<?php

namespace App\Http\Controllers\Admin;

use App\Models\Announcement;
use App\Models\AnnouncementTranslation;
use App\Models\BookingStatus;
use App\Models\FacilityReport;
use App\Models\FacilityReportUpdate;
use App\Models\Home;
use App\Models\Initiative;
use App\Models\InitiativeTranslation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** إدارة الإعلانات والمبادرات وبلاغات المرافق. */
class ResidentContentController extends Controller
{
    public function index()
    {
        return view('admin.content.index', [
            'homes' => Home::query()->orderBy('name')->get(),
            'announcements' => Announcement::query()->leftJoin('announcement_translations', function ($join) {
                $join->on('announcements.id', '=', 'announcement_translations.announcement_id')
                    ->where('announcement_translations.locale', '=', app()->getLocale());
            })->select('announcements.*', 'announcement_translations.title')->latest('announcements.created_at')->paginate(15, ['*'], 'announcements'),
            'initiatives' => Initiative::query()->leftJoin('initiative_translations', function ($join) {
                $join->on('initiatives.id', '=', 'initiative_translations.initiative_id')
                    ->where('initiative_translations.locale', '=', app()->getLocale());
            })->select('initiatives.*', 'initiative_translations.title')->latest('initiatives.created_at')->paginate(15, ['*'], 'initiatives'),
            'reports' => FacilityReport::query()->leftJoin('booking_statuses', 'facility_reports.status_id', '=', 'booking_statuses.id')
                ->leftJoin('booking_status_translations', function ($join) {
                    $join->on('booking_statuses.id', '=', 'booking_status_translations.booking_status_id')
                        ->where('booking_status_translations.locale', '=', app()->getLocale());
                })->select('facility_reports.*', 'booking_status_translations.name as status_name')
                ->latest('facility_reports.reported_at')->paginate(15, ['*'], 'reports'),
            'statuses' => BookingStatus::query()->join('booking_status_translations', function ($join) {
                $join->on('booking_statuses.id', '=', 'booking_status_translations.booking_status_id')
                    ->where('booking_status_translations.locale', '=', app()->getLocale());
            })->select('booking_statuses.id', 'booking_status_translations.name')->orderBy('booking_statuses.id')->get(),
        ]);
    }

    public function storeAnnouncement(Request $request)
    {
        $data = $request->validate([
            'home_id' => ['nullable', 'integer', 'exists:homes,id'],
            'title_ar' => ['required', 'string', 'min:3', 'max:180'],
            'body_ar' => ['required', 'string', 'min:5', 'max:20000'],
            'title_en' => ['required', 'string', 'min:3', 'max:180'],
            'body_en' => ['required', 'string', 'min:5', 'max:20000'],
            'published' => ['required', 'boolean'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $id = DB::transaction(function () use ($request, $data) {
            $id = Announcement::query()->create([
                'home_id' => $data['home_id'] ?? null,
                'created_by' => $request->user()->id,
                'reference' => 'AN-' . Str::upper(Str::random(12)),
                'published_at' => $data['published'] ? now() : null,
                'expires_at' => $data['expires_at'] ?? null,
                'is_published' => $data['published'],
                'created_at' => now(),
                'updated_at' => now(),
            ])->getKey();
            foreach (['ar', 'en'] as $locale) {
                AnnouncementTranslation::query()->create([
                    'announcement_id' => $id,
                    'locale' => $locale,
                    'title' => $data['title_' . $locale],
                    'body' => $data['body_' . $locale],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $id;
        });

        return back()->with('success', __('Announcement saved') . ' #' . $id);
    }

    public function storeInitiative(Request $request)
    {
        $data = $request->validate([
            'home_id' => ['required', 'integer', 'exists:homes,id'],
            'title_ar' => ['required', 'string', 'min:3', 'max:180'],
            'description_ar' => ['required', 'string', 'min:5', 'max:5000'],
            'title_en' => ['required', 'string', 'min:3', 'max:180'],
            'description_en' => ['required', 'string', 'min:5', 'max:5000'],
            'target_amount' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'is_published' => ['required', 'boolean'],
        ]);

        $id = DB::transaction(function () use ($request, $data) {
            $id = Initiative::query()->create([
                'home_id' => $data['home_id'],
                'created_by' => $request->user()->id,
                'reference' => 'IN-' . Str::upper(Str::random(12)),
                'title' => $data['title_ar'],
                'description' => $data['description_ar'],
                'target_amount' => $data['target_amount'] ?? null,
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'],
                'is_published' => $data['is_published'],
                'created_at' => now(),
                'updated_at' => now(),
            ])->getKey();
            foreach (['ar', 'en'] as $locale) {
                InitiativeTranslation::query()->create([
                    'initiative_id' => $id,
                    'locale' => $locale,
                    'title' => $data['title_' . $locale],
                    'description' => $data['description_' . $locale],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $id;
        });

        return back()->with('success', __('Initiative saved') . ' #' . $id);
    }

    public function updateFacilityReport(Request $request, int $reportId)
    {
        $report = FacilityReport::query()->where('id', $reportId)->first();
        abort_unless($report, 404);

        $data = $request->validate([
            'status_id' => ['required', 'integer', 'exists:booking_statuses,id'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $report, $data) {
            FacilityReport::query()->where('id', $report->id)->update([
                'status_id' => $data['status_id'],
                'resolved_at' => BookingStatus::query()->where('id', $data['status_id'])->where('code', 'completed')->exists() ? now() : null,
                'updated_at' => now(),
            ]);
            FacilityReportUpdate::query()->create([
                'facility_report_id' => $report->id,
                'status_id' => $data['status_id'],
                'updated_by' => $request->user()->id,
                'note' => $data['note'] ?? null,
                'created_at' => now(),
            ]);
        });

        return back()->with('success', __('Facility report updated'));
    }
}
