<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\ServiceCategory;
use App\Modules\Identity\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/** قواعد عمل أقسام الخدمات في لوحة الإدارة؛ لا تعتمد على HTTP. */
class AdminServiceCategoryAdminService
{
    /**
     * قائمة أقسام الخدمات مع عدد الخدمات التابعة للفلترة والعرض.
     * @param array $input
     * @param mixed $actor
     * @return array
     */
    public function index(array $input, mixed $actor = null): array
    {
        $locale = app()->getLocale();

        $categories = ServiceCategory::with(['translations'])
            ->withCount('services')
            ->when($input['search'] ?? null, function ($q, $search) {
                $q->where(fn ($w) => $w->where('code', 'like', "%{$search}%")
                    ->orWhereHas('translations', fn ($t) => $t->where('name', 'like', "%{$search}%")));
            })
            ->when(($input['status'] ?? null) === 'active', fn ($q) => $q->where('is_active', true))
            ->when(($input['status'] ?? null) === 'inactive', fn ($q) => $q->where('is_active', false))
            ->orderBy('id')
            ->paginate(15)->withQueryString();

        return [
            'categories' => $categories,
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
        return ['category' => null];
    }

    /**
     * بيانات نموذج التعديل.
     * @param int $categoryId
     * @return array
     */
    public function edit(int $categoryId): array
    {
        $category = ServiceCategory::with('translations')->findOrFail($categoryId);

        return [
            'category' => $category,
            'names' => $category->translations->pluck('name', 'locale'),
        ];
    }

    /**
     * إنشاء قسم خدمة مع ترجماته وصورته إن وجدت.
     * @param array $data
     * @param mixed $actor
     * @return string
     * @throws \Throwable
     */
    public function store(array $data, mixed $actor = null): string
    {
        DB::transaction(function () use ($data) {
            $category = ServiceCategory::create([
                'code' => $data['code'],
                'is_active' => (bool) ($data['is_active'] ?? true),
                'image_path' => $this->storeImage($data['image'] ?? null),
            ]);

            $this->syncTranslations($category, $data);
        });

        return __('Service category created successfully.');
    }

    /**
     * تعديل قسم خدمة وترجماته وصورته.
     * @param array $data
     * @param mixed $actor
     * @param int $categoryId
     * @return string
     * @throws \Throwable
     */
    public function update(array $data, mixed $actor = null, int $categoryId = 0): string
    {
        DB::transaction(function () use ($data, $categoryId) {
            $category = ServiceCategory::findOrFail($categoryId);

            $payload = [
                'code' => $data['code'],
                'is_active' => (bool) ($data['is_active'] ?? false),
            ];

            if (($data['image'] ?? null) instanceof UploadedFile) {
                $this->deleteImage($category->image_path);
                $payload['image_path'] = $this->storeImage($data['image']);
            }

            $category->update($payload);
            $this->syncTranslations($category, $data);
        });

        return __('Service category updated successfully.');
    }

    /**
     * حذف قسم خدمة بعد التحقق من عدم ارتباط خدمات به.
     * @param int $categoryId
     * @return string
     * @throws ValidationException
     */
    public function destroy(int $categoryId): string
    {
        $category = ServiceCategory::withCount('services')->findOrFail($categoryId);

        if ($category->services_count > 0) {
            throw ValidationException::withMessages([
                'delete' => __('Cannot delete category because it has linked services.'),
            ]);
        }

        $this->deleteImage($category->image_path);
        $category->delete();

        return __('Service category deleted successfully.');
    }

    private function syncTranslations(ServiceCategory $category, array $data): void
    {
        foreach (['ar', 'en', 'ur'] as $locale) {
            $name = $data["name_{$locale}"] ?? null;

            if (blank($name)) {
                $category->translations()->where('locale', $locale)->delete();
                continue;
            }

            $category->translations()->updateOrCreate(
                ['locale' => $locale],
                ['name' => $name]
            );
        }
    }

    private function storeImage(?UploadedFile $file): ?string
    {
        return $file ? $file->store('service-categories', 'public') : null;
    }

    private function deleteImage(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
