<?php

namespace App\Modules\Properties\Services;

use App\Modules\Properties\Models\Region;
use App\Modules\Properties\Models\RegionTranslation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** قواعد عمل إدارة المناطق في لوحة الإدارة. */
class AdminRegionAdminService
{
    /**
     * قائمة المناطق مع عدد المدن التابعة وفلاتر البحث.
     * @param array $input
     * @param mixed $actor
     * @return array
     */
    public function index(array $input, mixed $actor = null): array
    {
        $locale = app()->getLocale();

        $regions = Region::with(['translations'])
            ->withCount('cities')
            ->when($input['search'] ?? null, function ($q, $search) {
                $q->where(fn ($w) => $w->where('code', 'like', "%{$search}%")
                    ->orWhereHas('translations', fn ($t) => $t->where('name', 'like', "%{$search}%")));
            })
            ->when(($input['status'] ?? null) === 'active', fn ($q) => $q->where('is_active', true))
            ->when(($input['status'] ?? null) === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('id')
            ->paginate(15)->withQueryString();

        return [
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
        return ['region' => null];
    }

    /**
     * بيانات نموذج التعديل.
     * @param int $regionId
     * @return array
     */
    public function edit(int $regionId): array
    {
        $region = Region::with('translations')->findOrFail($regionId);

        return [
            'region' => $region,
            'names' => $region->translations->pluck('name', 'locale'),
        ];
    }

    /**
     * إنشاء منطقة مع ترجماتها.
     * @param array $data
     * @param mixed $actor
     * @return string
     * @throws \Throwable
     */
    public function store(array $data, mixed $actor = null): string
    {
        DB::transaction(function () use ($data) {
            $code = !empty($data['code'])
                ? $data['code']
                : (\Illuminate\Support\Str::slug($data['name_en'] ?? '') ?: ('region_' . uniqid()));

            if (Region::where('code', $code)->exists()) {
                $code .= '_' . time();
            }

            $region = Region::create([
                'code' => $code,
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
                    RegionTranslation::create([
                        'region_id' => $region->id,
                        'locale' => $locale,
                        'name' => trim($name),
                    ]);
                }
            }
        });

        return __('Region created successfully.');
    }

    /**
     * تحديث بيانات المنطقة وترجماتها.
     * @param array $data
     * @param mixed $actor
     * @param int $regionId
     * @return string
     * @throws \Throwable
     */
    public function update(array $data, mixed $actor, int $regionId): string
    {
        DB::transaction(function () use ($data, $regionId) {
            $region = Region::findOrFail($regionId);

            $updateData = [
                'country_code' => $data['country_code'] ?? $region->country_code ?? 'SA',
                'is_active' => (bool) ($data['is_active'] ?? true),
            ];

            if (!empty($data['code'])) {
                $updateData['code'] = $data['code'];
            }

            $region->update($updateData);

            $translations = [
                'ar' => $data['name_ar'],
                'en' => $data['name_en'] ?? null,
                'ur' => $data['name_ur'] ?? null,
            ];

            foreach ($translations as $locale => $name) {
                if ($name !== null && trim($name) !== '') {
                    RegionTranslation::updateOrCreate(
                        ['region_id' => $region->id, 'locale' => $locale],
                        ['name' => trim($name)]
                    );
                } else {
                    RegionTranslation::where('region_id', $region->id)->where('locale', $locale)->delete();
                }
            }
        });

        return __('Region updated successfully.');
    }

    /**
     * حذف المنطقة مع فحص الارتباطات.
     * @param int $regionId
     * @return string
     */
    public function destroy(int $regionId): string
    {
        $region = Region::findOrFail($regionId);

        if ($region->cities()->exists()) {
            throw ValidationException::withMessages([
                'region' => __('Cannot delete region with associated cities.'),
            ]);
        }

        $region->delete();

        return __('Region deleted successfully.');
    }
}
