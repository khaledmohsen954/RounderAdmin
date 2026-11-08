<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\Service;
use App\Modules\Catalog\Models\SubService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/** قواعد عمل الخدمات الفرعية في لوحة الإدارة؛ لا تعتمد على HTTP. */
class AdminSubServiceAdminService
{
    /**
     * قائمة الخدمات الفرعية مع الخدمات الأم للفلترة.
     * @param array $input
     * @param mixed $actor
     * @return array
     */
    public function index(array $input, mixed $actor): array
    {
        $locale = app()->getLocale();

        $subServices = SubService::with(['translations', 'service.translations'])
            ->when($input['service_id'] ?? null, fn ($q, $id) => $q->where('service_id', $id))
            ->when($input['search'] ?? null, function ($q, $search) {
                $q->where(fn ($w) => $w->where('code', 'like', "%{$search}%")
                    ->orWhereHas('translations', fn ($t) => $t->where('name', 'like', "%{$search}%")));
            })
            ->orderBy('service_id')->orderBy('sort_order')->orderBy('id')
            ->paginate(20)->withQueryString();

        return [
            'subServices' => $subServices,
            'services' => $this->servicesList(),
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
        return ['services' => $this->servicesList(), 'subService' => null];
    }

    /**
     * بيانات نموذج التعديل.
     * @param int $subServiceId
     * @return array
     */
    public function edit(int $subServiceId): array
    {
        $subService = SubService::with('translations')->findOrFail($subServiceId);

        return [
            'services' => $this->servicesList(),
            'subService' => $subService,
            'names' => $subService->translations->pluck('name', 'locale'),
            'descriptions' => $subService->translations->pluck('description', 'locale'),
        ];
    }

    /**
     * إنشاء خدمة فرعية مع ترجماتها.
     * @param array $data
     * @param mixed $actor
     * @return string
     * @throws \Throwable
     */
    public function store(array $data, mixed $actor): string
    {
        DB::transaction(function () use ($data) {
            $subService = SubService::create([
                'service_id' => $data['service_id'],
                'code' => $data['code'],
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => (bool) ($data['is_active'] ?? true),
                'image_path' => $this->storeImage($data['image'] ?? null),
            ]);

            $this->syncTranslations($subService, $data);
        });

        return 'تم إضافة الخدمة الفرعية بنجاح';
    }

    /**
     * تعديل خدمة فرعية.
     * @param array $data
     * @param mixed $actor
     * @param int $subServiceId
     * @return string
     * @throws \Throwable
     */
    public function update(array $data, mixed $actor, int $subServiceId): string
    {
        DB::transaction(function () use ($data, $subServiceId) {
            $subService = SubService::findOrFail($subServiceId);

            $payload = [
                'service_id' => $data['service_id'],
                'code' => $data['code'],
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => (bool) ($data['is_active'] ?? false),
            ];

            if (($data['image'] ?? null) instanceof UploadedFile) {
                $this->deleteImage($subService->image_path);
                $payload['image_path'] = $this->storeImage($data['image']);
            }

            $subService->update($payload);
            $this->syncTranslations($subService, $data);
        });

        return 'تم تحديث الخدمة الفرعية بنجاح';
    }

    /**
     * حذف خدمة فرعية.
     * @param int $subServiceId
     * @return string
     */
    public function destroy(int $subServiceId): string
    {
        $subService = SubService::findOrFail($subServiceId);
        $this->deleteImage($subService->image_path);
        $subService->delete();

        return 'تم حذف الخدمة الفرعية';
    }

    /** @return \Illuminate\Support\Collection */
    private function servicesList()
    {
        return Service::with('translations')->orderBy('id')->get();
    }

    private function syncTranslations(SubService $subService, array $data): void
    {
        foreach (['ar', 'en', 'ur'] as $locale) {
            $name = $data["name_{$locale}"] ?? null;

            if (blank($name)) {
                $subService->translations()->where('locale', $locale)->delete();
                continue;
            }

            $subService->translations()->updateOrCreate(
                ['locale' => $locale],
                ['name' => $name, 'description' => $data["description_{$locale}"] ?? null]
            );
        }
    }

    private function storeImage(?UploadedFile $file): ?string
    {
        return $file ? $file->store('sub-services', 'public') : null;
    }

    private function deleteImage(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}