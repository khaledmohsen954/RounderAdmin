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
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Facades\DB;
use App\Modules\Providers\Events\ProvidersChanged;
use App\Modules\Providers\Enums\Operation;
use App\Modules\Catalog\Models\Service;

/** قواعد عمل Providers؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class AdminProviderService
{
    /**
     * @param ProviderAccountService $accounts
     * @return void
     */
    public function __construct(
        private readonly ProviderAccountService $accounts,
        private readonly \App\Services\RichTextSanitizer $richText,
        private readonly ProviderDocumentService $documents,
    ) {}


    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param User|\App\Modules\Identity\Models\Admin|null $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(array $input, User|\App\Modules\Identity\Models\Admin|null $actor): mixed
    {
        // التجميع المرتبط يحسب العمولة في MySQL دون تحميل كل القيود إلى الذاكرة، وCOALESCE يعيد صفراً للمزود الجديد.
        $providers = ProviderProfile::query()
            ->join('users', 'provider_profiles.user_id', '=', 'users.id')
            ->leftJoin('provider_contracts', function ($join) {
                $join->on('provider_profiles.id', '=', 'provider_contracts.provider_profile_id')
                    ->whereRaw('provider_contracts.id = (SELECT MAX(pc2.id) FROM provider_contracts as pc2 WHERE pc2.provider_profile_id = provider_profiles.id)');
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
            ->when(!empty($input['q']), function ($q) use ($input) {
                $term = trim($input['q']);
                $q->where(function ($w) use ($term) {
                    $w->where('provider_profiles.company_name', 'like', "%{$term}%")
                        ->orWhere('users.name', 'like', "%{$term}%")
                        ->orWhere('users.email', 'like', "%{$term}%")
                        ->orWhere('users.phone', 'like', "%{$term}%")
                        ->orWhere('provider_contracts.reference', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('provider_profiles.id')
            ->paginate((int) ($input['per_page'] ?? 20))
            ->withQueryString();

        return compact('providers');
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param User|\App\Modules\Identity\Models\Admin|null $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function store(array $input, User|\App\Modules\Identity\Models\Admin|null $actor): mixed
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
     * @param User|\App\Modules\Identity\Models\Admin|null $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performStore(array $input, User|\App\Modules\Identity\Models\Admin|null $actor): mixed
    {
        $data = $input;
        $data['permissions'] = ProviderPortalService::PERMISSIONS;
        $cleanTerms = $this->richText->clean($data['terms'] ?? null);
        if ($data['is_approved'] && filled($data['provider_type'] ?? null) && blank($cleanTerms)) {
            throw ValidationException::withMessages(['terms' => __('Contract terms are required for an approved provider.')]);
        }

        $user = $this->accounts->create($data, $actor?->id);
        $provider = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'company_name' => $data['company_name'],
            'trade_name' => $data['trade_name'] ?? null,
            'provider_type' => $data['provider_type'] ?? null,
            'years_of_experience' => $data['years_of_experience'] ?? null,
            'commercial_registration' => $data['commercial_registration'] ?? null,
            'legal_representative' => $data['legal_representative'] ?? null,
            'legal_representative_id' => $data['legal_representative_id'] ?? null,
            'is_approved' => $data['is_approved'],
        ]);
        $user->update(['provider_profile_id' => $provider->id]);
        $provider->services()->sync($data['service_ids'] ?? []);
        $contractStatus = ProviderContractStatus::query()
            ->where('code', $data['is_approved'] ? ContractStatusCode::Active->value : ContractStatusCode::Draft->value)->value('id');
        DomainViolation::unless($contractStatus, FailureReason::Configuration, 'Provider contract statuses are not configured.');

        $reference = 'PC-' . Str::upper(Str::random(12));
        if (is_string($cleanTerms) && str_contains($cleanTerms, 'يُصدر تلقائياً عند الحفظ')) {
            $cleanTerms = str_replace('يُصدر تلقائياً عند الحفظ', $reference, $cleanTerms);
        }

        ProviderContract::query()->create([
            'provider_profile_id' => $provider->id,
            'status_id' => $contractStatus,
            'reference' => $reference,
            'service_commission_percent' => $data['service_commission_percent'],
            'emergency_commission_percent' => $data['emergency_commission_percent'],
            'starts_on' => $data['starts_on'],
            'ends_on' => $data['ends_on'] ?? null,
            'terms' => $cleanTerms,
        ]);
        $this->documents->save($provider, $data);

        return __('Provider and contract created successfully.');
    }
    /**
     * @param int $providerId
     * @return array<string, mixed>
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function edit(int $providerId): array
    {
        $provider = ProviderProfile::with(['user', 'services.translations'])->findOrFail($providerId);
        return [
            'provider' => $provider,
            'contract' => $provider->contracts()->latest('id')->first(),
            'services' => $this->availableServices(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function create(): array
    {
        return ['services' => $this->availableServices()];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Service>
     */
    private function availableServices(): \Illuminate\Database\Eloquent\Collection
    {
        return Service::query()->with('translations')->orderBy('code')->get();
    }

    /**
     * حفظ الشركة والحساب كوحدة واحدة؛ لا يعيد تسعير العقود أو العمولات المسجلة.
     * @param int $providerId
     * @param array<string, mixed> $input
     * @param User|null $actor
     * @return string
     * @throws \Throwable
     */
    public function update(int $providerId, array $input, User|\App\Modules\Identity\Models\Admin|null $actor): string
    {
        return DB::transaction(function () use ($providerId, $input, $actor) {
            $provider = ProviderProfile::lockForUpdate()->findOrFail($providerId);
            $this->accounts->updateContact($provider->user_id, $input);
            $contract = !empty($input['contract_id'])
                ? $provider->contracts()->lockForUpdate()->findOrFail($input['contract_id'])
                : null;
            $cleanTerms = array_key_exists('terms', $input) ? $this->richText->clean($input['terms']) : $contract?->terms;
            $registrationProfile = filled($provider->provider_type) || filled($input['provider_type'] ?? null);
            if ($input['is_approved'] && $registrationProfile && blank($cleanTerms)) {
                throw ValidationException::withMessages(['terms' => __('Contract terms are required for an approved provider.')]);
            }
            $contractChanged = $contract && (
                $cleanTerms !== $contract->terms
                || (string) $input['service_commission_percent'] !== (string) $contract->service_commission_percent
                || (string) $input['emergency_commission_percent'] !== (string) $contract->emergency_commission_percent
                || $input['starts_on'] !== $contract->starts_on?->format('Y-m-d')
                || ($input['ends_on'] ?? null) !== $contract->ends_on?->format('Y-m-d')
            );
            $provider->update([
                'company_name' => $input['company_name'],
                'trade_name' => $input['trade_name'] ?? null,
                'provider_type' => $input['provider_type'] ?? null,
                'years_of_experience' => $input['years_of_experience'] ?? null,
                'commercial_registration' => $input['commercial_registration'] ?? null,
                'legal_representative' => $input['legal_representative'] ?? null,
                'legal_representative_id' => $input['legal_representative_id'] ?? null,
                'is_approved' => $input['is_approved'],
                ...(!$input['is_approved'] || $contractChanged ? ['contract_accepted_at' => null] : []),
            ]);
            $this->documents->save($provider, $input);
            if (array_key_exists('service_ids', $input)) {
                $provider->services()->sync($input['service_ids'] ?? []);
            }
            if ($contract) {
                if ($input['is_approved'] && $contract->status?->code === ContractStatusCode::Draft->value) {
                    $contract->status_id = ProviderContractStatus::where('code',ContractStatusCode::Active->value)->firstOrFail()->id;
                }
                $contract->update([
                    'service_commission_percent'=>$input['service_commission_percent'],
                    'emergency_commission_percent'=>$input['emergency_commission_percent'],
                    'starts_on'=>$input['starts_on'],'ends_on'=>$input['ends_on'] ?? null,
                    'terms'=>$cleanTerms,
                ]);
            }
            DB::afterCommit(fn () => event(new ProvidersChanged(Operation::AdminProviderUpdate, $actor?->id)));
            return __('Provider updated successfully.');
        });
    }
}
