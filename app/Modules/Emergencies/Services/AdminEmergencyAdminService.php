<?php

namespace App\Modules\Emergencies\Services;
use App\Modules\Providers\Services\CommissionService;
use App\Modules\Providers\Enums\CommissionSource;
use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;
use App\Modules\Providers\Enums\CommissionStatusCode;
use App\Modules\Providers\Enums\ContractStatusCode;
use App\Modules\Emergencies\Enums\EmergencyStatusCode;

use App\Modules\Emergencies\Models\EmergencyProviderAssignment;
use App\Modules\Emergencies\Models\EmergencyRequest;
use App\Modules\Emergencies\Models\EmergencyRequestStatus;
use App\Modules\Emergencies\Models\EmergencyRequestUpdate;
use App\Modules\Providers\Models\ProviderCommissionEntry;
use App\Modules\Providers\Models\ProviderCommissionStatus;
use App\Modules\Providers\Models\ProviderContract;
use App\Modules\Providers\Models\ProviderProfile;

use Illuminate\Validation\ValidationException;
use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use App\Modules\Emergencies\Events\EmergenciesChanged;
use App\Modules\Emergencies\Enums\Operation;

/** قواعد عمل Emergencies؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class AdminEmergencyAdminService
{
    /**
     * @param CommissionService $commissions
     * @return void
     */
    public function __construct(private readonly CommissionService $commissions) {}


    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @param int $requestId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(array $input, ?User $actor): mixed
    {
        $locale = app()->getLocale();
        $requests = EmergencyRequest::query()
            ->join('users', 'emergency_requests.resident_id', '=', 'users.id')
            ->join('homes', 'emergency_requests.home_id', '=', 'homes.id')
            ->join('emergency_types', 'emergency_requests.emergency_type_id', '=', 'emergency_types.id')
            ->join('emergency_type_translations', function ($join) use ($locale) {
                $join->on('emergency_types.id', '=', 'emergency_type_translations.emergency_type_id')
                    ->where('emergency_type_translations.locale', '=', $locale);
            })
            ->join('emergency_request_statuses', 'emergency_requests.status_id', '=', 'emergency_request_statuses.id')
            ->join('emergency_request_status_translations', function ($join) use ($locale) {
                $join->on('emergency_request_statuses.id', '=', 'emergency_request_status_translations.emergency_request_status_id')
                    ->where('emergency_request_status_translations.locale', '=', $locale);
            })
            ->leftJoin('emergency_provider_assignments', 'emergency_requests.id', '=', 'emergency_provider_assignments.emergency_request_id')
            ->leftJoin('provider_profiles', 'emergency_provider_assignments.provider_profile_id', '=', 'provider_profiles.id')
            ->select(
                'emergency_requests.*', 'users.name as resident_name', 'users.phone as resident_phone',
                'homes.name as home_name', 'emergency_type_translations.name as type_name',
                'emergency_request_status_translations.name as status_name', 'emergency_request_statuses.code as status_code',
                'provider_profiles.company_name as provider_name',
                'emergency_provider_assignments.provider_contract_id'
            )->orderByDesc('emergency_requests.requested_at')->paginate(25);

        $providers = ProviderProfile::query()->join('users', 'provider_profiles.user_id', '=', 'users.id')
            ->where('provider_profiles.is_approved', true)->select('provider_profiles.id', 'provider_profiles.company_name', 'users.name')
            ->orderBy('provider_profiles.company_name')->get();
        $statuses = EmergencyRequestStatus::query()->join('emergency_request_status_translations', function ($join) use ($locale) {
            $join->on('emergency_request_statuses.id', '=', 'emergency_request_status_translations.emergency_request_status_id')
                ->where('emergency_request_status_translations.locale', '=', $locale);
        })->select('emergency_request_statuses.id', 'emergency_request_statuses.code', 'emergency_request_status_translations.name')->get();

        return compact('requests', 'providers', 'statuses');
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function update(array $input, ?User $actor, int $requestId): mixed
    {
        return DB::transaction(function () use ($input, $actor, $requestId) {
            $result = $this->performUpdate($input, $actor, $requestId);
            DB::afterCommit(fn () => event(new EmergenciesChanged(Operation::AdminEmergencyAdminUpdate, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @param int $requestId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performUpdate(array $input, ?User $actor, int $requestId): mixed
    {
        $data = $input;

        $emergency = EmergencyRequest::query()->where('id', $requestId)->first();
        DomainViolation::unless($emergency, FailureReason::NotFound);

        
        $status = EmergencyRequestStatus::query()->where('id', $data['status_id'])->first();
        $assignment = EmergencyProviderAssignment::query()->where('emergency_request_id', $emergency->id)->first();
        $providerId = $data['provider_profile_id'] ?? $assignment?->provider_profile_id;
        $contract = null;

        if ($providerId) {
            $provider = ProviderProfile::query()->where('id', $providerId)->where('is_approved', true)->first();
            DomainViolation::unless($provider, FailureReason::InvalidInput, __('Choose an approved provider.'));
            if ($assignment && (int) $assignment->provider_profile_id === (int) $providerId) {
                $contract = ProviderContract::query()->where('id', $assignment->provider_contract_id)->first();
            } else {
                $contract = ProviderContract::query()
                    ->join('provider_contract_statuses', 'provider_contracts.status_id', '=', 'provider_contract_statuses.id')
                    ->where('provider_contracts.provider_profile_id', $providerId)
                    ->where('provider_contract_statuses.code', ContractStatusCode::Active->value)
                    ->whereDate('provider_contracts.starts_on', '<=', today())
                    ->where(function ($query) {
                        $query->whereNull('provider_contracts.ends_on')->orWhereDate('provider_contracts.ends_on', '>=', today());
                    })->orderByDesc('provider_contracts.starts_on')->select('provider_contracts.*')->first();
            }
            DomainViolation::unless($contract, FailureReason::InvalidInput, __('Provider needs an active contract before assignment.'));
        }

        if ($status->code === EmergencyStatusCode::Assigned->value && !$providerId) {
            throw ValidationException::withMessages(['provider_profile_id' => __('Select a provider before assignment.')]);
        }
        if ($status->code === EmergencyStatusCode::Resolved->value && (!$providerId || ($data['gross_amount'] ?? null) === null)) {
            throw ValidationException::withMessages(['gross_amount' => __('Assign a provider and enter the final amount before resolving.')]);
        }

        DB::transaction(function () use ($input, $actor, $requestId, $emergency, $data, $status, $providerId, $contract, $assignment) {
            if ($providerId && $contract) {
                EmergencyProviderAssignment::query()->updateOrCreate(
                    ['emergency_request_id' => $requestId],
                    [
                        'provider_profile_id' => $providerId,
                        'provider_contract_id' => $contract->id,
                        'assigned_by' => $actor->id,
                        'assigned_at' => $assignment?->assigned_at ?? now(),
                        'completed_at' => $status->code === EmergencyStatusCode::Resolved->value ? now() : null,
                        'provider_note' => $data['note'] ?? null,
                        'updated_at' => now(),
                        'created_at' => $assignment?->created_at ?? now(),
                    ]
                );
            }
            EmergencyRequest::query()->where('id', $requestId)->update([
                'status_id' => $status->id,
                'handled_by' => $actor->id,
                'dispatched_at' => in_array($status->code, [EmergencyStatusCode::Assigned->value, EmergencyStatusCode::OnTheWay->value, EmergencyStatusCode::Resolved->value], true) ? ($emergency->dispatched_at ?? now()) : null,
                'resolved_at' => $status->code === EmergencyStatusCode::Resolved->value ? now() : null,
                'updated_at' => now(),
            ]);
            EmergencyRequestUpdate::query()->create([
                'emergency_request_id' => $requestId,
                'status_id' => $status->id,
                'updated_by' => $actor->id,
                'note' => $data['note'] ?? null,
                'created_at' => now(),
            ]);

            if ($status->code === EmergencyStatusCode::Resolved->value && $providerId && $contract) {
                $this->commissions->record(CommissionSource::Emergency, $requestId, $contract,
                    (int) $providerId, (float) $data['gross_amount'], $actor?->id);
            }
        });

        return __('Emergency request updated.');
    }
}
