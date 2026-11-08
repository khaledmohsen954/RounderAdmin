<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\ServiceCategory;
use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\Storage;

/** قواعد عمل Catalog؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class ApiServiceCategoryService
{
    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __invoke(array $input, ?User $actor): mixed
    {
        $locale = app()->getLocale();
        $subServicesLimit = isset($input['sub_services_limit'])
            ? min(max((int) $input['sub_services_limit'], 1), 50)
            : 6;

        $query = ServiceCategory::with([
            'translations',
            'services' => fn ($query) => $query->active()->with([
                'translations',
                'subServices' => fn ($query) => $query->active()->with('translations')
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])->orderBy('id'),
        ])
            ->active();

        if (!empty($input['service_category_id'])) {
            $query->where('id', (int) $input['service_category_id']);
        }

        $categories = $query->orderBy('id')
            ->get()
            ->map(fn (ServiceCategory $category) => [
                'id' => $category->id,
                'name' => $category->translations->firstWhere('locale', $locale)?->name ?? $category->code,
                'image_url' => $category->image_path ? Storage::disk('public')->url($category->image_path) : null,
                'services' => $category->services->map(function ($service) use ($locale, $subServicesLimit) {
                    $translation = $service->translations->firstWhere('locale', $locale);
                    $totalSubServices = $service->subServices->count();
                    $previewSubServices = $service->subServices->take($subServicesLimit);

                    return [
                        'id' => $service->id,
                        'code' => $service->code,
                        'name' => $translation?->name ?? $service->code,
                        'description' => $translation?->description,
                        'price' => (float) $service->base_price,
                        'price_before_discount' => $service->price_before_discount ?? $service->base_price,
                        'discount_type' => $service->discount_type?->value ?? \App\Modules\Catalog\Enums\DiscountType::None->value,
                        'discount_value' => $service->discount_value ?? '0.00',
                        'image_url' => $service->image_path ? Storage::disk('public')->url($service->image_path) : null,
                        'sub_services_count' => $totalSubServices,
                        'has_more' => $totalSubServices > $subServicesLimit,
                        'sub_services' => $previewSubServices->map(function ($subService) use ($locale) {
                            $translation = $subService->translations->firstWhere('locale', $locale);

                            return [
                                'id' => $subService->id,
                                'service_id' => $subService->service_id,
                                'code' => $subService->code,
                                'name' => $translation?->name ?? $subService->code,
                                'description' => $translation?->description,
                                'image_url' => $subService->image_path ? Storage::disk('public')->url($subService->image_path) : null,
                            ];
                        })->values(),
                    ];
                })->values(),
            ]);

        return ['data' => $categories];
    }
}
