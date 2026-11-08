<?php

namespace App\Modules\Properties\Services;

use App\Modules\Properties\Models\City;
use App\Modules\Properties\Models\CityTranslation;
use App\Modules\Properties\Models\Region;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** قواعد عمل إدارة المدن في لوحة الإدارة. */
class AdminCityAdminService
{
    /**
     * قائمة المدن مع عدد العقارات والمنطقة التابعة وفلاتر البحث.
     * @param array $input
     * @param mixed $actor
     * @return array
     */
    public function index(array $input, mixed $actor = null): array
    {
        $locale = app()->getLocale();

        $cities = City::with(['translations', 'region.translations'])
            ->withCount('homes')
            ->when($input['search'] ?? null, function ($q, $search) {
                $q->where(fn ($w) => $w->where('code', 'like', "%{$search}%")
                    ->orWhereHas('translations', fn ($t) => $t->where('name', 'like', "%{$search}%")));
            })
            ->when(!empty($input['region_id']), fn ($q) => $q->where('region_id', $input['region_id']))
            ->when(($input['status'] ?? null) === 'active', fn ($q) => $q->where('is_active', true))
            ->when(($input['status'] ?? null) === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('id')
            ->paginate(20)->withQueryString();

        $regions = Region::with('translations')->active()->orderBy('id')->get();

        return [
            'cities' => $cities,
            'regions' => $regions,
            'locale' => $locale,
            'filters' => $input,
        ];
    }

    /**
     * بيانات نموذج الإنشاء.
     * @return array
     */
    public function form(): array
    {
        $regions = Region::with('translations')->active()->orderBy('id')->get();

        return [
            'city' => null,
            'regions' => $regions,
        ];
    }

    /**
     * بيانات نموذج التعديل.
     * @param int $cityId
     * @return array
     */
    public function edit(int $cityId): array
    {
        $city = City::with(['translations', 'region'])->findOrFail($cityId);
        $regions = Region::with('translations')->active()->orderBy('id')->get();

        return [
            'city' => $city,
            'regions' => $regions,
            'names' => $city->translations->pluck('name', 'locale'),
        ];
    }

    /**
     * إنشاء مدينة جديدة مع ترجماتها.
     * @param array $data
     * @param mixed $actor
     * @return string
     * @throws \Throwable
     */
    public function store(array $data, mixed $actor = null): string
    {
        DB::transaction(function () use ($data) {
            $city = City::create([
                'code' => $data['code'],
                'region_id' => $data['region_id'] ?? null,
                'country_code' => $data['country_code'] ?? 'SA',
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $translations = [
                'ar' => $data['name_ar'],
                'en' => $data['name_en'] ?? null,
                'ur' => $data['name_ur'] ?? null,
            ];

            foreach ($translations as $locale => $name) {
                if ($name !== null && trim($name) !== '') {
                    CityTranslation::create([
                        'city_id' => $city->id,
                        'locale' => $locale,
                        'name' => trim($name),
                    ]);
                }
            }
        });

        return __('City created successfully.');
    }

    /**
     * تحديث بيانات المدينة وترجماتها.
     * @param array $data
     * @param mixed $actor
     * @param int $cityId
     * @return string
     * @throws \Throwable
     */
    public function update(array $data, mixed $actor, int $cityId): string
    {
        DB::transaction(function () use ($data, $cityId) {
            $city = City::findOrFail($cityId);

            $city->update([
                'code' => $data['code'],
                'region_id' => $data['region_id'] ?? null,
                'country_code' => $data['country_code'] ?? $city->country_code ?? 'SA',
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $translations = [
                'ar' => $data['name_ar'],
                'en' => $data['name_en'] ?? null,
                'ur' => $data['name_ur'] ?? null,
            ];

            foreach ($translations as $locale => $name) {
                if ($name !== null && trim($name) !== '') {
                    CityTranslation::updateOrCreate(
                        ['city_id' => $city->id, 'locale' => $locale],
                        ['name' => trim($name)]
                    );
                } else {
                    CityTranslation::where('city_id', $city->id)->where('locale', $locale)->delete();
                }
            }
        });

        return __('City updated successfully.');
    }

    /**
     * حذف المدينة مع التحقق من عدم وجود عقارات مرتبطة.
     * @param int $cityId
     * @return string
     */
    public function destroy(int $cityId): string
    {
        $city = City::findOrFail($cityId);

        if ($city->homes()->exists()) {
            throw ValidationException::withMessages([
                'city' => __('Cannot delete city with associated homes.'),
            ]);
        }

        $city->delete();

        return __('City deleted successfully.');
    }
}

