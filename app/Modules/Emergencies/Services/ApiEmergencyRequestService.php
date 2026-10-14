<?php

namespace App\Modules\Emergencies\Services;
use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;
use App\Modules\Emergencies\Enums\EmergencyStatusCode;

use App\Modules\Emergencies\Models\EmergencyRequest;
use App\Modules\Emergencies\Models\EmergencyRequestStatus;
use App\Modules\Emergencies\Models\EmergencyRequestUpdate;
use App\Modules\Properties\Models\ResidentHome;
use Illuminate\Support\Str;

use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use App\Modules\Emergencies\Events\EmergenciesChanged;
use App\Modules\Emergencies\Enums\Operation;

/** قواعد عمل Emergencies؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class ApiEmergencyRequestService
{

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(array $input, ?User $actor): mixed
    {
        $locale = app()->getLocale();
        $page = EmergencyRequest::query()
            ->join('emergency_request_statuses', 'emergency_requests.status_id', '=', 'emergency_request_statuses.id')
            ->join('emergency_request_status_translations', function ($join) use ($locale) {
                $join->on('emergency_request_statuses.id', '=', 'emergency_request_status_translations.emergency_request_status_id')
                    ->where('emergency_request_status_translations.locale', '=', $locale);
            })
            ->join('emergency_types', 'emergency_requests.emergency_type_id', '=', 'emergency_types.id')
            ->join('emergency_type_translations', function ($join) use ($locale) {
                $join->on('emergency_types.id', '=', 'emergency_type_translations.emergency_type_id')
                    ->where('emergency_type_translations.locale', '=', $locale);
            })
            ->where('emergency_requests.resident_id', $actor->id)
            ->select(
                'emergency_requests.id',
                'emergency_requests.reference',
                'emergency_requests.location_details',
                'emergency_requests.description',
                'emergency_requests.contact_phone',
                'emergency_requests.requested_at',
                'emergency_requests.dispatched_at',
                'emergency_requests.resolved_at',
                'emergency_request_statuses.code as status_code',
                'emergency_request_status_translations.name as status_name',
                'emergency_type_translations.name as type_name'
            )->orderByDesc('emergency_requests.requested_at')
            ->paginate(min(max((int) ($input['per_page'] ?? 15), 1), 50));

        return ['data' => $page];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function store(array $input, ?User $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performStore($input, $actor);
            DB::afterCommit(fn () => event(new EmergenciesChanged(Operation::ApiEmergencyRequestStore, $actor?->id)));
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
    private function performStore(array $input, ?User $actor): mixed
    {
        $data = $input;

        

        $membership = ResidentHome::query()
            ->where('id', $data['resident_home_id'])
            ->where('user_id', $actor->id)->first();
        DomainViolation::unless($membership, FailureReason::NotFound, 'Resident home not found.');

        $status = EmergencyRequestStatus::query()->where('code', EmergencyStatusCode::Received->value)->first();
        DomainViolation::unless($status, FailureReason::Configuration, 'Emergency request statuses have not been seeded.');

        $id = DB::transaction(function () use ($data, $membership, $input, $actor, $status) {
            $id = EmergencyRequest::query()->create([
                'resident_id' => $actor->id,
                'resident_home_id' => $membership->id,
                'home_id' => $membership->home_id,
                'emergency_type_id' => $data['emergency_type_id'],
                'status_id' => $status->id,
                'reference' => 'EM-' . Str::upper(Str::random(12)),
                'contact_phone' => $data['contact_phone'],
                'location_details' => $data['location_details'],
                'description' => $data['description'],
                'requested_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ])->getKey();
            EmergencyRequestUpdate::query()->create([
                'emergency_request_id' => $id,
                'status_id' => $status->id,
                'updated_by' => $actor->id,
                'note' => null,
                'created_at' => now(),
            ]);

            return $id;
        });

        return [
            'data' => EmergencyRequest::query()->where('id', $id)->first(),
            'message' => __('Emergency request received'),
        ];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function cancel(array $input, ?User $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performCancel($input, $actor);
            DB::afterCommit(fn () => event(new EmergenciesChanged(Operation::ApiEmergencyRequestCancel, $actor?->id)));
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
    private function performCancel(array $input, ?User $actor): mixed
    {
        $emergency = EmergencyRequest::query()
            ->where('id', $requestId)
            ->where('resident_id', $actor->id)->first();
        DomainViolation::unless($emergency, FailureReason::NotFound);

        $cancelled = EmergencyRequestStatus::query()->where('code', EmergencyStatusCode::Cancelled->value)->first();
        DomainViolation::unless($cancelled, FailureReason::Configuration, 'Emergency request statuses have not been seeded.');
        $received = EmergencyRequestStatus::query()->where('id', $emergency->status_id)->value('code');
        DomainViolation::unless($received === EmergencyStatusCode::Received->value, FailureReason::Conflict, 'A dispatched emergency request cannot be cancelled by the resident.');

        DB::transaction(function () use ($emergency, $cancelled, $input, $actor) {
            EmergencyRequest::query()->where('id', $emergency->id)->update([
                'status_id' => $cancelled->id,
                'updated_at' => now(),
            ]);
            EmergencyRequestUpdate::query()->create([
                'emergency_request_id' => $emergency->id,
                'status_id' => $cancelled->id,
                'updated_by' => $actor->id,
                'note' => 'Cancelled by resident',
                'created_at' => now(),
            ]);
        });

        return ['message' => __('Emergency request cancelled')];
    }
}
