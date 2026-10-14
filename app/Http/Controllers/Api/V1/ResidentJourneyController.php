<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Announcement;
use App\Models\AssociationAccount;
use App\Models\AssociationDue;
use App\Models\AssociationLedgerEntry;
use App\Models\Booking;
use App\Models\BookingRating;
use App\Models\BookingStatus;
use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\CommunityReaction;
use App\Models\FacilityReport;
use App\Models\FacilityReportUpdate;
use App\Models\Initiative;
use App\Models\InitiativeParticipation;
use App\Models\ResidentHome;
use App\Models\ResidentNotification;
use App\Models\ResidentVisit;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** رحلات المجتمع والزيارات والمرافق مع فحص صلاحية الوصول للوحدة. */
class ResidentJourneyController extends Controller
{
    /**
     * Return a resident profile without password or token fields.
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->json(['data' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'locale' => $user->locale,
        ]]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:120'],
            'email' => ['sometimes', 'required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'phone' => ['sometimes', 'nullable', 'regex:/^\+[1-9]\d{7,14}$/', Rule::unique('users', 'phone')->ignore($request->user()->id)],
            'locale' => ['sometimes', 'required', Rule::in(['ar', 'en', 'ur'])],
        ]);
        $request->user()->fill($data)->save();

        return $this->profile($request);
    }

    public function association(Request $request)
    {
        $membership = $this->membership($request);
        abort_unless($membership, 404, 'Resident home not found.');

        $account = AssociationAccount::query()->where('home_id', $membership->home_id)->first();
        if (!$account) {
            return response()->json(['data' => ['account' => null, 'dues' => [], 'ledger' => []]]);
        }

        return response()->json(['data' => [
            'account' => $account,
            'dues' => AssociationDue::query()->where('resident_home_id', $membership->id)->orderByDesc('due_at')->paginate(20),
            'ledger' => AssociationLedgerEntry::query()->where('association_account_id', $account->id)
                ->where(function ($query) use ($membership, $request) {
                    $query->where('resident_home_id', $membership->id)->orWhere(function ($q) use ($request) {
                        $q->whereNull('resident_home_id')->where('user_id', $request->user()->id);
                    });
                })->orderByDesc('posted_at')->paginate(20),
        ]]);
    }

    public function facilityReports(Request $request)
    {
        $membership = $this->membership($request);
        abort_unless($membership, 404, 'Resident home not found.');

        $query = FacilityReport::query()->where('home_id', $membership->home_id);
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->integer('status_id'));
        }

        return response()->json(['data' => $query->orderByDesc('reported_at')->paginate(20)]);
    }

    public function createFacilityReport(Request $request)
    {
        $membership = $this->membership($request);
        abort_unless($membership, 404, 'Resident home not found.');

        $data = $request->validate([
            'facility_category_id' => ['required', 'integer', Rule::exists('facility_categories', 'id')->where('is_active', true)],
            'title' => ['required', 'string', 'min:4', 'max:160'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'location' => ['nullable', 'string', 'max:160'],
        ]);
        $statusId = BookingStatus::query()->where('code', 'pending')->value('id');
        abort_unless($statusId, 500, 'Default pending status is not configured.');

        $id = FacilityReport::query()->create([
            'home_id' => $membership->home_id,
            'resident_home_id' => $membership->id,
            'facility_category_id' => $data['facility_category_id'],
            'status_id' => $statusId,
            'reference' => 'FR-' . Str::upper(Str::random(12)),
            'title' => $data['title'],
            'description' => $data['description'],
            'location' => $data['location'] ?? null,
            'reported_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ])->getKey();
        FacilityReportUpdate::query()->create([
            'facility_report_id' => $id,
            'status_id' => $statusId,
            'updated_by' => $request->user()->id,
            'note' => null,
            'created_at' => now(),
        ]);

        return response()->json(['data' => FacilityReport::query()->find($id)], 201);
    }

    public function visits(Request $request)
    {
        $membership = $this->membership($request);
        abort_unless($membership, 404, 'Resident home not found.');

        return response()->json(['data' => ResidentVisit::query()
            ->where('resident_home_id', $membership->id)
            ->where('created_by', $request->user()->id)
            ->orderByDesc('starts_at')->paginate(20)]);
    }

    public function createVisit(Request $request)
    {
        $membership = $this->membership($request);
        abort_unless($membership, 404, 'Resident home not found.');

        $data = $request->validate([
            'visitor_name' => ['required', 'string', 'min:2', 'max:120'],
            'visitor_phone' => ['nullable', 'regex:/^\+[1-9]\d{7,14}$/'],
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);
        $id = ResidentVisit::query()->create([
            ...$data,
            'resident_home_id' => $membership->id,
            'created_by' => $request->user()->id,
            'access_code' => Str::upper(Str::random(8)),
            'created_at' => now(),
            'updated_at' => now(),
        ])->getKey();

        return response()->json(['data' => ResidentVisit::query()->find($id)], 201);
    }

    public function cancelVisit(Request $request, int $visitId)
    {
        $deleted = ResidentVisit::query()
            ->where('id', $visitId)
            ->where('created_by', $request->user()->id)
            ->whereNull('checked_in_at')
            ->where('starts_at', '>', now())
            ->delete();
        abort_unless($deleted, 404, 'Visit cannot be found or cancelled.');

        return response()->json(['message' => 'Visit cancelled.']);
    }

    public function announcements(Request $request)
    {
        $homeIds = $this->homeIds($request);
        abort_if($homeIds->isEmpty(), 403, 'Link a home first.');

        $locale = app()->getLocale();
        $rows = Announcement::query()
            ->leftJoin('announcement_translations', function ($join) use ($locale) {
                $join->on('announcements.id', '=', 'announcement_translations.announcement_id')
                    ->where('announcement_translations.locale', '=', $locale);
            })
            ->where('announcements.is_published', true)
            ->where(function ($query) use ($homeIds) {
                $query->whereNull('announcements.home_id')->orWhereIn('announcements.home_id', $homeIds);
            })
            ->where(function ($query) {
                $query->whereNull('announcements.expires_at')->orWhere('announcements.expires_at', '>', now());
            })
            ->select('announcements.id', 'announcements.reference', 'announcements.published_at',
                'announcement_translations.title', 'announcement_translations.body')
            ->orderByDesc('announcements.published_at')->paginate(20);

        return response()->json(['data' => $rows]);
    }

    public function communityPosts(Request $request)
    {
        $homeIds = $this->homeIds($request);
        abort_if($homeIds->isEmpty(), 403, 'Link a home first.');

        return response()->json(['data' => CommunityPost::query()
            ->whereIn('home_id', $homeIds)->where('is_hidden', false)
            ->orderByDesc('created_at')->paginate(20)]);
    }

    public function createCommunityPost(Request $request)
    {
        $membership = $this->membership($request);
        abort_unless($membership, 404, 'Resident home not found.');
        $data = $request->validate(['body' => ['required', 'string', 'min:2', 'max:5000']]);

        $id = CommunityPost::query()->create([
            'home_id' => $membership->home_id,
            'resident_home_id' => $membership->id,
            'created_by' => $request->user()->id,
            'reference' => 'CP-' . Str::upper(Str::random(12)),
            'body' => $data['body'],
            'is_hidden' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ])->getKey();

        return response()->json(['data' => CommunityPost::query()->find($id)], 201);
    }

    public function comment(Request $request, int $postId)
    {
        $homeIds = $this->homeIds($request);
        abort_if($homeIds->isEmpty(), 403, 'Link a home first.');
        abort_unless(CommunityPost::query()->where('id', $postId)->whereIn('home_id', $homeIds)->where('is_hidden', false)->exists(), 404);

        $data = $request->validate([
            'body' => ['required', 'string', 'min:1', 'max:3000'],
            'parent_id' => ['nullable', 'integer', Rule::exists('community_comments', 'id')->where('community_post_id', $postId)],
        ]);
        $id = CommunityComment::query()->create([
            'community_post_id' => $postId,
            'created_by' => $request->user()->id,
            'parent_id' => $data['parent_id'] ?? null,
            'body' => $data['body'],
            'created_at' => now(),
            'updated_at' => now(),
        ])->getKey();

        return response()->json(['data' => CommunityComment::query()->find($id)], 201);
    }

    public function react(Request $request, int $postId)
    {
        $homeIds = $this->homeIds($request);
        abort_if($homeIds->isEmpty(), 403, 'Link a home first.');
        abort_unless(CommunityPost::query()->where('id', $postId)->whereIn('home_id', $homeIds)->where('is_hidden', false)->exists(), 404);

        $data = $request->validate(['reaction_code' => ['required', Rule::in(['like', 'love', 'support', 'thanks'])]]);
        CommunityReaction::query()->updateOrCreate(
            ['community_post_id' => $postId, 'user_id' => $request->user()->id],
            ['reaction_code' => $data['reaction_code'], 'updated_at' => now(), 'created_at' => now()]
        );

        return response()->json(['message' => 'Reaction saved.']);
    }

    public function initiatives(Request $request)
    {
        $homeIds = $this->homeIds($request);
        abort_if($homeIds->isEmpty(), 403, 'Link a home first.');

        return response()->json(['data' => Initiative::query()
            ->whereIn('home_id', $homeIds)->where('is_published', true)
            ->where('ends_at', '>=', now())->orderBy('starts_at')->paginate(20)]);
    }

    public function joinInitiative(Request $request, int $initiativeId)
    {
        $membership = $this->membership($request);
        abort_unless($membership, 404, 'Resident home not found.');
        abort_unless(Initiative::query()->where('id', $initiativeId)
            ->where('home_id', $membership->home_id)->where('is_published', true)
            ->where('ends_at', '>=', now())->exists(), 404);

        $data = $request->validate(['pledged_amount' => ['sometimes', 'numeric', 'min:0', 'max:10000000']]);
        InitiativeParticipation::query()->updateOrCreate(
            ['initiative_id' => $initiativeId, 'resident_home_id' => $membership->id],
            ['user_id' => $request->user()->id, 'pledged_amount' => $data['pledged_amount'] ?? 0,
                'joined_at' => now(), 'updated_at' => now(), 'created_at' => now()]
        );

        return response()->json(['message' => 'Participation saved.']);
    }

    public function notifications(Request $request)
    {
        return response()->json(['data' => ResidentNotification::query()
            ->where('user_id', $request->user()->id)->orderByDesc('created_at')->paginate(30)]);
    }

    public function markNotificationRead(Request $request, int $notificationId)
    {
        $updated = ResidentNotification::query()->where('id', $notificationId)
            ->where('user_id', $request->user()->id)->update(['read_at' => now(), 'updated_at' => now()]);
        abort_unless($updated, 404);

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function rateBooking(Request $request, int $bookingId)
    {
        $booking = Booking::query()->where('id', $bookingId)
            ->where('resident_id', $request->user()->id)->first();
        abort_unless($booking, 404);

        $data = $request->validate([
            'score' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $id = BookingRating::query()->create([
                'booking_id' => $bookingId,
                'resident_id' => $request->user()->id,
                ...$data,
                'created_at' => now(),
                'updated_at' => now(),
            ])->getKey();
        } catch (QueryException) {
            abort(409, 'This booking has already been rated.');
        }

        return response()->json(['data' => BookingRating::query()->find($id)], 201);
    }

    private function membership(Request $request): ?object
    {
        $query = ResidentHome::query()->where('user_id', $request->user()->id);
        if ($request->filled('resident_home_id')) {
            $query->where('id', $request->integer('resident_home_id'));
        } else {
            $query->orderByDesc('is_primary')->orderBy('id');
        }

        return $query->first();
    }

    private function homeIds(Request $request)
    {
        return ResidentHome::query()->where('user_id', $request->user()->id)
            ->pluck('home_id')->unique()->values();
    }
}
