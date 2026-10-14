<?php

namespace App\Modules\Facilities\Services;
use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;
use App\Modules\Bookings\Enums\BookingStatusCode;

use App\Modules\Bookings\Models\BookingStatus;

use App\Modules\Facilities\Models\FacilityReport;
use App\Modules\Facilities\Models\FacilityReportUpdate;

use App\Modules\Properties\Models\ResidentHome;

use Illuminate\Support\Str;

use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use App\Modules\Facilities\Events\FacilitiesChanged;
use App\Modules\Facilities\Enums\Operation;

/** قواعد عمل Facilities؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class ApiFacilityService
{

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function facilityReports(array $input, ?User $actor): mixed
    {
        $membership = $this->membership($input, $actor);
        DomainViolation::unless($membership, FailureReason::NotFound, 'Resident home not found.');

        $query = FacilityReport::query()->where('home_id', $membership->home_id);
        if ((isset($input['status_id']) && $input['status_id'] !== '')) {
            $query->where('status_id', (int) ($input['status_id'] ?? 0));
        }

        return ['data' => $query->orderByDesc('reported_at')->paginate(20)];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function createFacilityReport(array $input, ?User $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performCreateFacilityReport($input, $actor);
            DB::afterCommit(fn () => event(new FacilitiesChanged(Operation::ApiFacilityCreateFacilityReport, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performCreateFacilityReport(array $input, ?User $actor): mixed
    {
        $data = $input;

        $membership = $this->membership($input, $actor);
        DomainViolation::unless($membership, FailureReason::NotFound, 'Resident home not found.');

        
        $statusId = BookingStatus::query()->where('code', BookingStatusCode::Pending->value)->value('id');
        DomainViolation::unless($statusId, FailureReason::Configuration, 'Default pending status is not configured.');

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
            'updated_by' => $actor->id,
            'note' => null,
            'created_at' => now(),
        ]);

        return ['data' => FacilityReport::query()->find($id)];
    }

    /**
     * مساعد داخلي يحافظ على قواعد الوصول والعلاقات.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function membership(array $input, ?User $actor): mixed
    {
        $query = ResidentHome::query()->where('user_id', $actor->id);
        if ((isset($input['resident_home_id']) && $input['resident_home_id'] !== '')) {
            $query->where('id', (int) ($input['resident_home_id'] ?? 0));
        } else {
            $query->orderByDesc('is_primary')->orderBy('id');
        }

        return $query->first();
    }

}
