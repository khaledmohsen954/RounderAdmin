<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Service;
use App\Modules\Catalog\Models\SubService;
use App\Modules\Catalog\Models\SubServicePrice;
use App\Modules\Properties\Models\City;
use App\Modules\Properties\Models\Region;
use App\Modules\Providers\Models\ProviderProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Modules\Identity\Models\Admin;

/**
 * قواعد عمل إدارة أسعار الخدمات الفرعية للوحة الإدارة.
 */
class AdminPriceAdminService
{
    /**
     * استعراض سجلات التسعير مع التصفية والتقسيم على صفحات.
     *
     * @param array<string, mixed> $input
     * @param mixed $actor
     * @return array<string, mixed>
     */
    public function index(array $input, mixed $actor): array
    {
        $locale = app()->getLocale();

        $query = SubServicePrice::query()
            ->with([
                'provider.user',
                'subService.translations',
                'subService.service.translations',
                'region.translations',
            ])
            ->withCount('cities');

        if (!empty($input['provider_profile_id'])) {
            $query->where('provider_profile_id', (int) $input['provider_profile_id']);
        }

        if (!empty($input['sub_service_id'])) {
            $query->where('sub_service_id', (int) $input['sub_service_id']);
        } elseif (!empty($input['service_id'])) {
            $query->whereHas('subService', function ($q) use ($input) {
                $q->where('service_id', (int) $input['service_id']);
            });
        }

        if (!empty($input['region_id'])) {
            $query->where('region_id', (int) $input['region_id']);
        }

        if (!empty($input['city_id'])) {
            $query->whereHas('cities', function ($q) use ($input) {
                $q->where('cities.id', (int) $input['city_id']);
            });
        }

        if (isset($input['status']) && $input['status'] !== 'all') {
            $query->where('is_active', $input['status'] === 'active');
        }
        if (!empty($input['approval_status']) && $input['approval_status'] !== 'all') {
            $query->where('approval_status', $input['approval_status']);
        }

        if (!empty($input['search'])) {
            $search = $input['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('provider', function ($p) use ($search) {
                    $p->where('company_name', 'like', "%{$search}%");
                })->orWhereHas('subService.translations', function ($st) use ($search) {
                    $st->where('name', 'like', "%{$search}%");
                })->orWhereHas('region.translations', function ($rt) use ($search) {
                    $rt->where('name', 'like', "%{$search}%");
                })->orWhereHas('cities.translations', function ($ct) use ($search) {
                    $ct->where('name', 'like', "%{$search}%");
                });
            });
        }

        $prices = $query->orderByDesc('id')->paginate(25)->withQueryString();

        $providers = ProviderProfile::query()
            ->where('is_approved', true)
            ->orderBy('company_name')
            ->get();

        $services = Service::query()
            ->with('translations')
            ->where('is_active', true)
            ->get();

        $regions = Region::query()
            ->with('translations')
            ->where('is_active', true)
            ->get();

        return [
            'prices' => $prices,
            'providers' => $providers,
            'services' => $services,
            'regions' => $regions,
            'filters' => $input,
            'locale' => $locale,
        ];
    }

    /**
     * بيانات نموذج إنشاء تسعير جديد.
     *
     * @return array<string, mixed>
     */
    public function createData(): array
    {
        $locale = app()->getLocale();

        $providers = ProviderProfile::query()
            ->where('is_approved', true)
            ->with(['services.translations'])
            ->orderBy('company_name')
            ->get();

        $regions = Region::query()
            ->where('is_active', true)
            ->with(['translations'])
            ->orderBy('id')
            ->get();

        return [
            'providers' => $providers,
            'regions' => $regions,
            'locale' => $locale,
        ];
    }

    /**
     * حفظ سجل تسعير وربط المدن التابعة له.
     *
     * @param array<string, mixed> $input
     * @param mixed $actor
     * @return SubServicePrice
     */
    public function store(array $input, mixed $actor): SubServicePrice
    {
        return DB::transaction(function () use ($input) {
            ProviderProfile::query()->whereKey($input['provider_profile_id'])->lockForUpdate()->firstOrFail();
            $this->ensureCitiesBelongToRegion($input['region_id'], $input['city_ids'] ?? []);
            $cityIds = $input['city_ids'] ?? [];
            if (!empty($cityIds)) {
                $conflicting = SubServicePrice::query()
                    ->where('provider_profile_id', (int) $input['provider_profile_id'])
                    ->where('sub_service_id', (int) $input['sub_service_id'])
                    ->whereHas('cities', fn ($q) => $q->whereIn('cities.id', $cityIds))
                    ->with('region.translations')
                    ->first();

                if ($conflicting) {
                    $regionName = $conflicting->region?->translations->firstWhere('locale', app()->getLocale())?->name
                        ?? $conflicting->region?->translations->firstWhere('locale', 'ar')?->name
                        ?? $conflicting->region?->code
                        ?? '';

                    throw ValidationException::withMessages([
                        'city_ids' => [__('A pricing already exists for this provider and sub-service covering the selected cities (Price #:id:region). Please edit the existing price instead of creating a conflict.', [
                            'id' => $conflicting->id,
                            'region' => $regionName ? " - {$regionName}" : '',
                        ])],
                    ]);
                }
            }

            $price = SubServicePrice::create([
                'provider_profile_id' => (int) $input['provider_profile_id'],
                'sub_service_id' => (int) $input['sub_service_id'],
                'region_id' => (int) $input['region_id'],
                'price' => $input['price'],
                'is_active' => isset($input['is_active']) ? (bool) $input['is_active'] : true,
            ]);

            if (!empty($input['city_ids'])) {
                $price->cities()->sync($input['city_ids']);
            }

            return $price;
        });
    }

    /**
     * جلب سجل التسعير مع تفاصيل الخدمة والمزود للتعديل.
     *
     * @param int $priceId
     * @return SubServicePrice
     */
    public function edit(int $priceId): SubServicePrice
    {
        return SubServicePrice::query()
            ->with([
                'provider',
                'subService.translations',
                'subService.service.translations',
                'region.translations',
            ])
            ->withCount('cities')
            ->findOrFail($priceId);
    }

    /**
     * تحديث تسعير محدد ومدنه التابعة.
     *
     * @param mixed $actor
     * @param int $priceId
     * @param array<string, mixed> $input
     * @return SubServicePrice
     */
    public function update(mixed $actor, int $priceId, array $input): SubServicePrice
    {
        return DB::transaction(function () use ($priceId, $input) {
            $priceRecord = SubServicePrice::findOrFail($priceId);

            $cityIds = $input['city_ids'] ?? null;
            $this->ensureCitiesBelongToRegion($input['region_id'], $cityIds ?? []);
            if (!empty($cityIds)) {
                $conflicting = SubServicePrice::query()
                    ->where('id', '!=', $priceId)
                    ->where('provider_profile_id', $priceRecord->provider_profile_id)
                    ->where('sub_service_id', $priceRecord->sub_service_id)
                    ->whereHas('cities', fn ($q) => $q->whereIn('cities.id', $cityIds))
                    ->with('region.translations')
                    ->first();

                if ($conflicting) {
                    $regionName = $conflicting->region?->translations->firstWhere('locale', app()->getLocale())?->name
                        ?? $conflicting->region?->translations->firstWhere('locale', 'ar')?->name
                        ?? $conflicting->region?->code
                        ?? '';

                    throw ValidationException::withMessages([
                        'city_ids' => [__('A pricing already exists for this provider and sub-service covering the selected cities (Price #:id:region). Please edit the existing price instead of creating a conflict.', [
                            'id' => $conflicting->id,
                            'region' => $regionName ? " - {$regionName}" : '',
                        ])],
                    ]);
                }
            }

            $dataToUpdate = [
                'price' => $input['price'],
                'is_active' => isset($input['is_active']) ? (bool) $input['is_active'] : true,
            ];

            if (array_key_exists('region_id', $input) && !empty($input['region_id'])) {
                $dataToUpdate['region_id'] = (int) $input['region_id'];
            }

            $priceRecord->update($dataToUpdate);

            if (isset($input['city_ids'])) {
                $priceRecord->cities()->sync($input['city_ids']);
            }

            return $priceRecord;
        });
    }

    /**
     * حذف تسعير محدد.
     *
     * @param mixed $actor
     * @param int $priceId
     * @return void
     */
    public function destroy(mixed $actor, int $priceId): void
    {
        $priceRecord = SubServicePrice::findOrFail($priceId);
        $priceRecord->delete();
    }

    public function review(int $priceId, string $decision, ?string $reason, Admin $actor): void
    {
        DB::transaction(function () use ($priceId, $decision, $reason, $actor) {
            $price = SubServicePrice::query()->whereKey($priceId)->lockForUpdate()->firstOrFail();
            if ($price->approval_status !== SubServicePrice::APPROVAL_PENDING) {
                throw ValidationException::withMessages(['approval_status' => __('Only pending prices can be reviewed.')]);
            }
            if (!in_array($decision, [SubServicePrice::APPROVAL_APPROVED, SubServicePrice::APPROVAL_REJECTED], true)) {
                throw ValidationException::withMessages(['decision' => __('Select a valid review decision.')]);
            }

            $price->update([
                'approval_status' => $decision,
                'reviewed_by' => $actor->id,
                'reviewed_at' => now(),
                'rejection_reason' => $decision === SubServicePrice::APPROVAL_REJECTED ? $reason : null,
            ]);
        });
    }

    private function ensureCitiesBelongToRegion(int $regionId, array $cityIds): void
    {
        if (!$cityIds) {
            return;
        }

        $matchingCities = City::query()->where('region_id', $regionId)
            ->whereIn('id', $cityIds)->count();
        if ($matchingCities !== count(array_unique(array_map('intval', $cityIds)))) {
            throw ValidationException::withMessages([
                'city_ids' => [__('Each selected city must belong to the selected region.')],
            ]);
        }
    }

    /**
     * جلب الخدمات المرتبطة بمزود خدمة محدد.
     *
     * @param int $providerProfileId
     * @return Collection
     */
    public function getProviderServices(int $providerProfileId): Collection
    {
        $provider = ProviderProfile::query()
            ->with('services.translations')
            ->findOrFail($providerProfileId);

        $locale = app()->getLocale();

        return $provider->services->map(function ($service) use ($locale) {
            $name = $service->translations->firstWhere('locale', $locale)?->name
                ?? $service->translations->firstWhere('locale', 'ar')?->name
                ?? $service->code;

            return [
                'id' => $service->id,
                'name' => $name,
            ];
        });
    }

    /**
     * جلب الخدمات الفرعية لخدمة رئيسية.
     *
     * @param int $serviceId
     * @return Collection
     */
    public function getSubServices(int $serviceId): Collection
    {
        $subServices = SubService::query()
            ->where('service_id', $serviceId)
            ->where('is_active', true)
            ->with('translations')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $locale = app()->getLocale();

        return $subServices->map(function ($sub) use ($locale) {
            $name = $sub->translations->firstWhere('locale', $locale)?->name
                ?? $sub->translations->firstWhere('locale', 'ar')?->name
                ?? $sub->code;

            return [
                'id' => $sub->id,
                'name' => $name,
            ];
        });
    }

    /**
     * جلب مدن منطقة جغرافية محددة.
     *
     * @param int $regionId
     * @return Collection
     */
    public function getRegionCities(int $regionId): Collection
    {
        $cities = City::query()
            ->where('region_id', $regionId)
            ->where('is_active', true)
            ->with('translations')
            ->orderBy('id')
            ->get();

        $locale = app()->getLocale();

        return $cities->map(function ($city) use ($locale) {
            $name = $city->translations->firstWhere('locale', $locale)?->name
                ?? $city->translations->firstWhere('locale', 'ar')?->name
                ?? $city->code;

            return [
                'id' => $city->id,
                'name' => $name,
            ];
        });
    }

    /**
     * جلب تفاصيل المدن التابعة لتسعير محدد لعرضها في النافذة المنبثقة.
     *
     * @param int $priceId
     * @return array<string, mixed>
     */
    public function getPriceCities(int $priceId): array
    {
        $price = SubServicePrice::query()
            ->with([
                'provider',
                'subService.translations',
                'region.translations',
            ])
            ->findOrFail($priceId);

        $locale = app()->getLocale();

        $cities = DB::table('sub_service_price_cities')
            ->join('cities', 'cities.id', '=', 'sub_service_price_cities.city_id')
            ->leftJoin('city_translations', function ($join) use ($locale) {
                $join->on('city_translations.city_id', '=', 'cities.id')
                    ->where('city_translations.locale', '=', $locale);
            })
            ->leftJoin('city_translations as ct_ar', function ($join) {
                $join->on('ct_ar.city_id', '=', 'cities.id')
                    ->where('ct_ar.locale', '=', 'ar');
            })
            ->where('sub_service_price_cities.sub_service_price_id', $priceId)
            ->select('cities.id', DB::raw('COALESCE(city_translations.name, ct_ar.name, cities.code) as name'))
            ->orderBy('name')
            ->get();

        $providerName = $price->provider?->company_name ?? '—';
        $subServiceName = $price->subService?->translations->firstWhere('locale', $locale)?->name
            ?? $price->subService?->translations->firstWhere('locale', 'ar')?->name
            ?? $price->subService?->code;
        $regionName = $price->region?->translations->firstWhere('locale', $locale)?->name
            ?? $price->region?->translations->firstWhere('locale', 'ar')?->name
            ?? __('All regions');

        return [
            'price_id' => $price->id,
            'provider_name' => $providerName,
            'sub_service_name' => $subServiceName,
            'region_name' => $regionName,
            'cities_count' => $cities->count(),
            'cities' => $cities,
        ];
    }
}
