<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ResidentContentController extends Controller
{
    public function index()
    {
        return view('admin.content.index', [
            'homes' => DB::table('homes')->orderBy('name')->get(),
            'announcements' => DB::table('announcements')->leftJoin('announcement_translations', function ($join) {
                $join->on('announcements.id', '=', 'announcement_translations.announcement_id')
                    ->where('announcement_translations.locale', '=', app()->getLocale());
            })->select('announcements.*', 'announcement_translations.title')->latest('announcements.created_at')->paginate(15, ['*'], 'announcements'),
            'initiatives' => DB::table('initiatives')->leftJoin('initiative_translations', function ($join) {
                $join->on('initiatives.id', '=', 'initiative_translations.initiative_id')
                    ->where('initiative_translations.locale', '=', app()->getLocale());
            })->select('initiatives.*', 'initiative_translations.title')->latest('initiatives.created_at')->paginate(15, ['*'], 'initiatives'),
            'reports' => DB::table('facility_reports')->leftJoin('booking_statuses', 'facility_reports.status_id', '=', 'booking_statuses.id')
                ->leftJoin('booking_status_translations', function ($join) {
                    $join->on('booking_statuses.id', '=', 'booking_status_translations.booking_status_id')
                        ->where('booking_status_translations.locale', '=', app()->getLocale());
                })->select('facility_reports.*', 'booking_status_translations.name as status_name')
                ->latest('facility_reports.reported_at')->paginate(15, ['*'], 'reports'),
            'statuses' => DB::table('booking_statuses')->join('booking_status_translations', function ($join) {
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
            $id = DB::table('announcements')->insertGetId([
                'home_id' => $data['home_id'] ?? null,
                'created_by' => $request->user()->id,
                'reference' => 'AN-' . Str::upper(Str::random(12)),
                'published_at' => $data['published'] ? now() : null,
                'expires_at' => $data['expires_at'] ?? null,
                'is_published' => $data['published'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            foreach (['ar', 'en'] as $locale) {
                DB::table('announcement_translations')->insert([
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
            $id = DB::table('initiatives')->insertGetId([
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
            ]);
            foreach (['ar', 'en'] as $locale) {
                DB::table('initiative_translations')->insert([
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
        $report = DB::table('facility_reports')->where('id', $reportId)->first();
        abort_unless($report, 404);

        $data = $request->validate([
            'status_id' => ['required', 'integer', 'exists:booking_statuses,id'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $report, $data) {
            DB::table('facility_reports')->where('id', $report->id)->update([
                'status_id' => $data['status_id'],
                'resolved_at' => DB::table('booking_statuses')->where('id', $data['status_id'])->where('code', 'completed')->exists() ? now() : null,
                'updated_at' => now(),
            ]);
            DB::table('facility_report_updates')->insert([
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
