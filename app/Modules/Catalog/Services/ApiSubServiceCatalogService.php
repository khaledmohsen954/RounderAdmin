<?php

namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Models\SubService;
use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\Storage;

/** قراءة الخدمات الفرعية الفعالة للعملاء (التطبيق) مع ترقيم الصفحات (Pagination) والفلترة بالخدمة الأم والبحث. */
class ApiSubServiceCatalogService
{
    /**
     * @param array $input يقبل service_id اختياريًا و per_page و page و search.
     * @param ?User $actor
     * @return array<string, mixed>
     */
    public function __invoke(array $input, ?User $actor): array
    {
        $locale = app()->getLocale();
        $perPage = min(max((int) ($input['per_page'] ?? 15), 1), 100);

        $query = SubService::query()
            ->with('translations')
            ->active();

        if (!empty($input['service_id'])) {
            $query->where('service_id', (int) $input['service_id']);
        }

        if (!empty($input['search'])) {
            $search = $input['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhereHas('translations', fn ($t) => $t->where('name', 'like', "%{$search}%"));
            });
        }

        $pageNumber = !empty($input['page']) ? (int) $input['page'] : null;

        $page = $query->orderBy('service_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $pageNumber);

        $items = $page->getCollection()->map(function (SubService $sub) use ($locale) {
            $translation = $sub->translations->firstWhere('locale', $locale);

            return [
                'id' => $sub->id,
                'service_id' => $sub->service_id,
                'code' => $sub->code,
                'name' => $translation?->name ?? $sub->code,
                'description' => $translation?->description,
                'image_url' => $sub->image_path ? Storage::disk('public')->url($sub->image_path) : null,
            ];
        })->values();

        return [
            'data' => $items,
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
            'per_page' => $page->perPage(),
            'total' => $page->total(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
            'links' => [
                'first' => $page->url(1),
                'last' => $page->url($page->lastPage()),
                'prev' => $page->previousPageUrl(),
                'next' => $page->nextPageUrl(),
            ],
        ];
    }
}
