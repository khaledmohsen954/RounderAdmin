<?php

namespace App\Modules\Catalog\Services;
use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;
use App\Core\Enums\SupportedLocale;

use App\Modules\Catalog\Models\PackageBillingCycle;
use App\Modules\Catalog\Models\ResidentSubscription;
use App\Modules\Catalog\Models\Service;
use App\Modules\Catalog\Models\ServiceCategory;
use App\Modules\Catalog\Models\ServicePackage;
use App\Modules\Catalog\Models\ServicePackageService;
use App\Modules\Catalog\Models\ServicePackageTranslation;
use App\Modules\Catalog\Models\ServiceTranslation;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use App\Modules\Catalog\Events\CatalogChanged;
use App\Modules\Catalog\Enums\Operation;

/** قواعد عمل Catalog؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class AdminPackageAdminService
{
    /**
     * @param CatalogPricingService $pricing
     * @param \App\Services\CatalogImageStorage $images
     */
    public function __construct(
        private readonly CatalogPricingService $pricing,
        private readonly PackageFeatureService $features,
        private readonly \App\Services\CatalogImageStorage $images,
    ) {}

    /**
     * الاحتفاظ بالصور السابقة يمنع كسرها إذا تراجعت المعاملة أو كان لها قارئ متزامن.
     * تنظيف الملفات غير المشار إليها عملية صيانة مستقلة عن المعاملة المالية.
     * @param array<string, mixed> $input
     * @param string $priceField
     * @return array<string, mixed>
     * @throws \Throwable
     */
    private function catalogAttributes(array $input, string $priceField): array
    {
        $attributes = $this->pricing->attributes($input, $priceField);
        if (!empty($input['image'])) {
            $attributes['image_path'] = $this->images->store($input['image']);
        } elseif (!empty($input['remove_image'])) {
            $attributes['image_path'] = null;
        }
        return $attributes;
    }


    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param User|\App\Modules\Identity\Models\Admin|null $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(array $input, User|\App\Modules\Identity\Models\Admin|null $actor): mixed
    {
        $services = Service::query()
            ->join('service_categories', 'services.service_category_id', '=', 'service_categories.id')
            ->leftJoin('service_category_translations', function ($join) {
                $join->on('service_categories.id', '=', 'service_category_translations.service_category_id')->where('service_category_translations.locale', '=', app()->getLocale());
            })
            ->leftJoin('service_translations', function ($join) {
                $join->on('services.id', '=', 'service_translations.service_id')
                    ->where('service_translations.locale', '=', app()->getLocale());
            })->select('services.*', 'service_category_translations.name as category_name', 'service_translations.name')
            ->orderBy('service_categories.code')->orderBy('services.code')->get();

        $categories = ServiceCategory::query()->join('service_category_translations', function ($join) {
            $join->on('service_categories.id', '=', 'service_category_translations.service_category_id')
                ->where('service_category_translations.locale', '=', app()->getLocale());
        })->where('service_categories.is_active', true)->select('service_categories.id', 'service_category_translations.name')->orderBy('name')->get();

        $cycles = PackageBillingCycle::query()->orderBy('months')->get();

        // COUNT مرتبط بالباقة يتيح ترقيم الصفحات دون تحميل جميع عناصر الباقات.
        $packages = ServicePackage::query()
            ->join('package_billing_cycles', 'service_packages.billing_cycle_id', '=', 'package_billing_cycles.id')
            ->leftJoin('service_package_translations', function ($join) {
                $join->on('service_packages.id', '=', 'service_package_translations.service_package_id')
                    ->where('service_package_translations.locale', '=', app()->getLocale());
            })->select('service_packages.*', 'package_billing_cycles.code as cycle_code', 'service_package_translations.name')
            ->selectSub(ServicePackageService::query()->selectRaw('COUNT(*)')->whereColumn('service_package_services.service_package_id', 'service_packages.id'), 'services_count')
            ->orderByDesc('service_packages.id')->paginate(20);

        $subscriptions = ResidentSubscription::query()
            ->join('resident_homes', 'resident_subscriptions.resident_home_id', '=', 'resident_homes.id')
            ->join('users', 'resident_homes.user_id', '=', 'users.id')
            ->join('homes', 'resident_homes.home_id', '=', 'homes.id')
            ->join('service_packages', 'resident_subscriptions.service_package_id', '=', 'service_packages.id')
            ->leftJoin('service_package_translations', function ($join) {
                $join->on('service_packages.id', '=', 'service_package_translations.service_package_id')
                    ->where('service_package_translations.locale', '=', app()->getLocale());
            })
            ->join('subscription_statuses', 'resident_subscriptions.status_id', '=', 'subscription_statuses.id')
            ->join('subscription_status_translations', function ($join) {
                $join->on('subscription_statuses.id', '=', 'subscription_status_translations.subscription_status_id')
                    ->where('subscription_status_translations.locale', '=', app()->getLocale());
            })
            ->select('resident_subscriptions.*', 'users.name as resident_name', 'homes.name as home_name',
                'service_package_translations.name as package_name', 'subscription_status_translations.name as status_name')
            ->orderByDesc('resident_subscriptions.created_at')->paginate(20, ['*'], 'subscriptions');

        $packageServices = ServicePackageService::query()
            ->join('service_packages', 'service_package_services.service_package_id', '=', 'service_packages.id')
            ->join('services', 'service_package_services.service_id', '=', 'services.id')
            ->leftJoin('service_translations', function ($join) {
                $join->on('services.id', '=', 'service_translations.service_id')->where('service_translations.locale', '=', app()->getLocale());
            })->select('service_package_services.*', 'services.code as service_code', 'service_translations.name as service_name')
            ->get()->groupBy('service_package_id');

        $featureDefinitions = \App\Modules\Catalog\Models\PackageFeature::orderBy('sort_order')->orderBy('id')->get();
        return compact('services', 'categories', 'cycles', 'packages', 'packageServices', 'subscriptions', 'featureDefinitions');
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param User|\App\Modules\Identity\Models\Admin|null $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function storeService(array $input, User|\App\Modules\Identity\Models\Admin|null $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performStoreService($input, $actor);
            DB::afterCommit(fn () => event(new CatalogChanged(Operation::AdminPackageAdminStoreService, $actor?->id)));
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
    private function performStoreService(array $input, User|\App\Modules\Identity\Models\Admin|null $actor): mixed
    {
        $data = $input;

        
        

        $attributes = $this->catalogAttributes($data, 'base_price');
        DB::transaction(function () use ($data, $attributes) {
            $serviceId = Service::query()->create([
                ...$attributes,
                'service_category_id' => $data['service_category_id'],
                'code' => Str::lower($data['code']),
                'is_active' => $data['is_active'] ?? true,
                'created_at' => now(),
                'updated_at' => now(),
            ])->getKey();
            foreach (array_column(SupportedLocale::cases(), 'value') as $locale) {
                ServiceTranslation::query()->create([
                    'service_id' => $serviceId,
                    'locale' => $locale,
                    'name' => $data['name_' . $locale],
                    'description' => $data['description_' . $locale] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return __('Service created successfully.');
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param User|\App\Modules\Identity\Models\Admin|null $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function storePackage(array $input, User|\App\Modules\Identity\Models\Admin|null $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performStorePackage($input, $actor);
            DB::afterCommit(fn () => event(new CatalogChanged(Operation::AdminPackageAdminStorePackage, $actor?->id)));
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
    private function performStorePackage(array $input, User|\App\Modules\Identity\Models\Admin|null $actor): mixed
    {
        $data = $input;

        
        

        foreach ($data['services'] as $index => $service) {
            if (!$service['is_unlimited'] && empty($service['included_quantity'])) {
                throw ValidationException::withMessages([
                    "services.$index.included_quantity" => __('Set an included quantity or mark this service unlimited.'),
                ]);
            }
        }

        $attributes = $this->catalogAttributes($data, 'price');
        DB::transaction(function () use ($data, $attributes) {
            $packageId = ServicePackage::query()->create([
                ...$attributes,
                'billing_cycle_id' => $data['billing_cycle_id'],
                'code' => Str::lower($data['code']),
                'currency_code' => $data['currency_code'],
                'duration_months' => $data['duration_months'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ])->getKey();
            $this->features->sync(ServicePackage::findOrFail($packageId), $data['features'] ?? null);
            foreach (array_column(SupportedLocale::cases(), 'value') as $locale) {
                ServicePackageTranslation::query()->create([
                    'service_package_id' => $packageId,
                    'locale' => $locale,
                    'name' => $data['name_' . $locale],
                    'description' => $data['description_' . $locale] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            foreach ($data['services'] as $included) {
                ServicePackageService::query()->create([
                    'service_package_id' => $packageId,
                    'service_id' => $included['service_id'],
                    'included_quantity' => $included['is_unlimited'] ? null : $included['included_quantity'],
                    'is_unlimited' => $included['is_unlimited'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return __('Package created successfully.');
    }

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param User|\App\Modules\Identity\Models\Admin|null $actor
     * @param int $serviceId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function editService(array $input, User|\App\Modules\Identity\Models\Admin|null $actor, int $serviceId): mixed
    {
        $service = Service::query()->where('id', $serviceId)->first();
        DomainViolation::unless($service, FailureReason::NotFound);
        $translations = ServiceTranslation::query()->where('service_id', $serviceId)->get()->keyBy('locale');
        $categories = ServiceCategory::query()->leftJoin('service_category_translations', function ($join) {
            $join->on('service_categories.id', '=', 'service_category_translations.service_category_id')
                ->where('service_category_translations.locale', '=', app()->getLocale());
        })->select('service_categories.id', 'service_categories.code', 'service_category_translations.name')->get();
        return compact('service', 'translations', 'categories');
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param User|\App\Modules\Identity\Models\Admin|null $actor
     * @param int $serviceId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function updateService(array $input, User|\App\Modules\Identity\Models\Admin|null $actor, int $serviceId): mixed
    {
        return DB::transaction(function () use ($input, $actor, $serviceId) {
            $result = $this->performUpdateService($input, $actor, $serviceId);
            DB::afterCommit(fn () => event(new CatalogChanged(Operation::AdminPackageAdminUpdateService, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param User|\App\Modules\Identity\Models\Admin|null $actor
     * @param int $serviceId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performUpdateService(array $input, User|\App\Modules\Identity\Models\Admin|null $actor, int $serviceId): mixed
    {
        $data = $input;

        DomainViolation::unless(Service::query()->where('id', $serviceId)->exists(), FailureReason::NotFound);
        // FormRequest سبق أن وحّد الكود قبل التحقق من التفرد.
        
        
        $attributes = $this->catalogAttributes($data, 'base_price');
        DB::transaction(function () use ($serviceId, $data, $attributes) {
            DomainViolation::unless(Service::query()->where('id', $serviceId)->lockForUpdate()->first(), FailureReason::NotFound);
            // Catalogue edits never rewrite prices already recorded on orders.
            Service::query()->where('id', $serviceId)->update([
                ...$attributes,
                'service_category_id' => $data['service_category_id'], 'code' => $data['code'],
                'is_active' => $data['is_active'], 'updated_at' => now(),
            ]);
            foreach (array_column(SupportedLocale::cases(), 'value') as $locale) {
                ServiceTranslation::query()->updateOrCreate(
                    ['service_id' => $serviceId, 'locale' => $locale],
                    ['name' => $data['name_'.$locale], 'description' => $data['description_'.$locale] ?? null,
                     'updated_at' => now()]
                );
            }
        });
        return __('Service updated successfully.');
    }

    /**
     * @param array $input
     * @param User|null $actor
     * @param int $packageId
     * @return array
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function editPackage(array $input, User|\App\Modules\Identity\Models\Admin|null $actor, int $packageId): array
    {
        $package = ServicePackage::with(['translations', 'includedServices'])->findOrFail($packageId);
        $translations = $package->translations->keyBy('locale');
        $cycles = PackageBillingCycle::orderBy('months')->get();
        $services = Service::with('translations')->orderBy('code')->get();
        $featureDefinitions = \App\Modules\Catalog\Models\PackageFeature::orderBy('sort_order')->orderBy('id')->get();
        $featureValues = $package->comparisonFeatures->keyBy('package_feature_id');
        return compact('package', 'translations', 'cycles', 'services', 'featureDefinitions', 'featureValues');
    }

    /**
     * تحديث التعريف فقط؛ حصص المشتركين وأسعارهم المحفوظة لا يعاد كتابتها.
     * @param array $input
     * @param User|null $actor
     * @param int $packageId
     * @return string
     * @throws \Throwable
     */
    public function updatePackage(array $input, User|\App\Modules\Identity\Models\Admin|null $actor, int $packageId): string
    {
        return DB::transaction(function () use ($input, $actor, $packageId) {
            $package = ServicePackage::whereKey($packageId)->lockForUpdate()->firstOrFail();
            foreach ($input['services'] as $index => $included) {
                if (!$included['is_unlimited'] && empty($included['included_quantity'])) {
                    throw ValidationException::withMessages([
                        "services.$index.included_quantity" => __('Set an included quantity or mark this service unlimited.'),
                    ]);
                }
            }
            $package->update([
                ...$this->catalogAttributes($input, 'price'),
                'code' => $input['code'], 'billing_cycle_id' => $input['billing_cycle_id'],
                'currency_code' => $input['currency_code'], 'duration_months' => $input['duration_months'],
                'is_active' => $input['is_active'],
            ]);
            foreach (SupportedLocale::cases() as $locale) {
                $package->translations()->updateOrCreate(['locale' => $locale->value], [
                    'name' => $input['name_'.$locale->value],
                    'description' => $input['description_'.$locale->value] ?? null,
                ]);
            }
            $ids = array_column($input['services'], 'service_id');
            $package->includedServices()->whereNotIn('service_id', $ids)->delete();
            foreach ($input['services'] as $included) {
                $package->includedServices()->updateOrCreate(['service_id' => $included['service_id']], [
                    'included_quantity' => $included['is_unlimited'] ? null : $included['included_quantity'],
                    'is_unlimited' => $included['is_unlimited'],
                ]);
            }
            $this->features->sync($package, $input['features'] ?? null);
            DB::afterCommit(fn () => event(new CatalogChanged(Operation::AdminPackageAdminUpdatePackage, $actor?->id)));
            return __('Package updated successfully.');
        });
    }
    /**
     * اختيارات الفورم من قاعدة البيانات لضمان استخدام معرّفات الفئات.
     * @return array<string, mixed>
     */
    public function serviceForm(): array
    {
        $categories = ServiceCategory::with('translations')->where('is_active', true)->get();
        return compact('categories');
    }
}
