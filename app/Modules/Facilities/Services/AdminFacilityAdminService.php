<?php

namespace App\Modules\Facilities\Services;
use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;
use App\Modules\Bookings\Enums\BookingStatusCode;

use App\Modules\Bookings\Models\BookingStatus;
use App\Modules\Facilities\Models\FacilityReport;
use App\Modules\Facilities\Models\FacilityReportUpdate;

use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use App\Modules\Facilities\Events\FacilitiesChanged;
use App\Modules\Facilities\Enums\Operation;

/** قواعد عمل Facilities؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class AdminFacilityAdminService
{

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @param int $reportId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function updateFacilityReport(array $input, ?User $actor, int $reportId): mixed
    {
        return DB::transaction(function () use ($input, $actor, $reportId) {
            $result = $this->performUpdateFacilityReport($input, $actor, $reportId);
            DB::afterCommit(fn () => event(new FacilitiesChanged(Operation::AdminFacilityAdminUpdateFacilityReport, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @param int $reportId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performUpdateFacilityReport(array $input, ?User $actor, int $reportId): mixed
    {
        $data = $input;

        $report = FacilityReport::query()->where('id', $reportId)->first();
        DomainViolation::unless($report, FailureReason::NotFound);

        

        DB::transaction(function () use ($input, $actor, $report, $data) {
            FacilityReport::query()->where('id', $report->id)->update([
                'status_id' => $data['status_id'],
                'resolved_at' => BookingStatus::query()->where('id', $data['status_id'])->where('code', BookingStatusCode::Completed->value)->exists() ? now() : null,
                'updated_at' => now(),
            ]);
            FacilityReportUpdate::query()->create([
                'facility_report_id' => $report->id,
                'status_id' => $data['status_id'],
                'updated_by' => $actor->id,
                'note' => $data['note'] ?? null,
                'created_at' => now(),
            ]);
        });

        return __('Facility report updated');
    }
}
