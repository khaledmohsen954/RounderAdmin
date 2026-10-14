<?php

namespace App\Modules\Providers\Services;
use App\Modules\Identity\Services\ProviderAccountService;
use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;
use App\Modules\Providers\Enums\ContractStatusCode;
use App\Modules\Identity\Enums\RoleCode;

use App\Modules\Providers\Models\ProviderCommissionEntry;
use App\Modules\Providers\Models\ProviderContract;
use App\Modules\Providers\Models\ProviderContractStatus;
use App\Modules\Providers\Models\ProviderProfile;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\DB;
use App\Modules\Providers\Events\ProvidersChanged;
use App\Modules\Providers\Enums\Operation;

/** قواعد عمل Providers؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class AdminProviderService
{
    /**
     * @param ProviderAccountService $accounts
     * @return void
     */
    public function __construct(private readonly ProviderAccountService $accounts) {}


    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(array $input, ?User $actor): mixed
    {
        // التجميع المرتبط يحسب العمولة في MySQL دون تحميل كل القيود إلى الذاكرة، وCOALESCE يعيد صفراً للمزود الجديد.
        $providers = ProviderProfile::query()
            ->join('users', 'provider_profiles.user_id', '=', 'users.id')
            ->leftJoin('provider_contracts', function ($join) {
                $join->on('provider_profiles.id', '=', 'provider_contracts.provider_profile_id')
                    ->whereNull('provider_contracts.ends_on');
            })
            ->leftJoin('provider_contract_statuses', 'provider_contracts.status_id', '=', 'provider_contract_statuses.id')
            ->select(
                'provider_profiles.id as provider_id', 'provider_profiles.company_name',
                'provider_profiles.is_approved', 'users.id as user_id', 'users.name',
                'users.email', 'users.phone', 'provider_contracts.reference as contract_reference',
                'provider_contracts.service_commission_percent', 'provider_contracts.emergency_commission_percent',
                'provider_contracts.starts_on', 'provider_contract_statuses.code as contract_status'
            )->selectSub(ProviderCommissionEntry::query()->selectRaw('COALESCE(SUM(commission_amount), 0)')
                ->whereColumn('provider_commission_entries.provider_profile_id', 'provider_profiles.id'), 'commission_total')
            ->orderByDesc('provider_profiles.id')->paginate(20);

        return compact('providers');
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
            DB::afterCommit(fn () => event(new ProvidersChanged(Operation::AdminProviderStore, $actor?->id)));
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

        

        DB::transaction(function () use ($input, $actor, $data) {
            $userId = $this->accounts->create($data, $actor?->id)->getKey();
            $providerId = ProviderProfile::query()->create([
                'user_id' => $userId,
                'company_name' => $data['company_name'],
                'is_approved' => $data['is_approved'],
                'created_at' => now(),
                'updated_at' => now(),
            ])->getKey();
            $contractStatus = ProviderContractStatus::query()
                ->where('code', $data['is_approved'] ? ContractStatusCode::Active->value : ContractStatusCode::Draft->value)->value('id');
            DomainViolation::unless($contractStatus, FailureReason::Configuration, 'Provider contract statuses are not configured.');

            ProviderContract::query()->create([
                'provider_profile_id' => $providerId,
                'status_id' => $contractStatus,
                'reference' => 'PC-' . Str::upper(Str::random(12)),
                'service_commission_percent' => $data['service_commission_percent'],
                'emergency_commission_percent' => $data['emergency_commission_percent'],
                'starts_on' => $data['starts_on'],
                'ends_on' => $data['ends_on'] ?? null,
                'terms' => $data['terms'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return __('Provider and contract created successfully.');
    }
}
