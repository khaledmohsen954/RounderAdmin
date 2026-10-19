<?php

namespace App\Modules\Community\Services;

use App\Modules\Community\Models\Announcement;
use App\Modules\Community\Models\AnnouncementTranslation;
use App\Modules\Bookings\Models\BookingStatus;
use App\Modules\Facilities\Models\FacilityReport;

use App\Modules\Properties\Models\Home;
use App\Modules\Community\Models\Initiative;
use App\Modules\Community\Models\InitiativeTranslation;
use Illuminate\Support\Str;

use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use App\Modules\Community\Events\CommunityChanged;
use App\Modules\Community\Enums\Operation;

/** قواعد عمل Community؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class AdminResidentContentService
{

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param User|\App\Modules\Identity\Models\Admin|null $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(array $input, User|\App\Modules\Identity\Models\Admin|null $actor): mixed
    {
        return [
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
        ];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param User|\App\Modules\Identity\Models\Admin|null $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function storeAnnouncement(array $input, User|\App\Modules\Identity\Models\Admin|null $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performStoreAnnouncement($input, $actor);
            DB::afterCommit(fn () => event(new CommunityChanged(Operation::AdminResidentContentStoreAnnouncement, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param User|\App\Modules\Identity\Models\Admin|null $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performStoreAnnouncement(array $input, User|\App\Modules\Identity\Models\Admin|null $actor): mixed
    {
        $data = $input;

        

        $id = DB::transaction(function () use ($input, $actor, $data) {
            $id = Announcement::query()->create([
                'home_id' => $data['home_id'] ?? null,
                ...\App\Core\AdminActor::attributes($actor, 'created_by'),
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

        return __('Announcement saved') . ' #' . $id;
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param User|\App\Modules\Identity\Models\Admin|null $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function storeInitiative(array $input, User|\App\Modules\Identity\Models\Admin|null $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performStoreInitiative($input, $actor);
            DB::afterCommit(fn () => event(new CommunityChanged(Operation::AdminResidentContentStoreInitiative, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param User|\App\Modules\Identity\Models\Admin|null $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performStoreInitiative(array $input, User|\App\Modules\Identity\Models\Admin|null $actor): mixed
    {
        $data = $input;

        

        $id = DB::transaction(function () use ($input, $actor, $data) {
            $id = Initiative::query()->create([
                'home_id' => $data['home_id'],
                ...\App\Core\AdminActor::attributes($actor, 'created_by'),
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

        return __('Initiative saved') . ' #' . $id;
    }
}
