<?php

namespace App\Modules\Settings\Services;

use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;
use App\Modules\Settings\Models\Page;
use App\Modules\Settings\Models\PageTranslation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * خدمة إدارة وتعديل الصفحات الثابتة من لوحة التحكم.
 */
class AdminPageService
{
    /**
     * قائمة جميع الصفحات مع الترجمات.
     *
     * @return Collection<int, Page>
     */
    public function list(): Collection
    {
        return Page::with('translations')->orderBy('id')->get();
    }

    /**
     * البحث عن صفحة بالمعرف أو الـ slug.
     *
     * @param int|string $id
     * @return Page
     */
    public function find(int|string $id): Page
    {
        $page = is_numeric($id)
            ? Page::with('translations')->find($id)
            : Page::with('translations')->where('slug', $id)->first();

        DomainViolation::unless($page, FailureReason::NotFound, __('Page not found.'));

        return $page;
    }

    /**
     * تحديث بيانات الصفحة وترجماتها.
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @return Page
     */
    public function update(int $id, array $data): Page
    {
        return DB::transaction(function () use ($id, $data) {
            $page = Page::lockForUpdate()->find($id);
            DomainViolation::unless($page, FailureReason::NotFound, __('Page not found.'));

            $page->update([
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            // تحديث أو إنشاء الترجمة العربية
            PageTranslation::updateOrCreate(
                ['page_id' => $page->id, 'locale' => 'ar'],
                [
                    'title' => $data['title_ar'],
                    'content' => $data['content_ar'],
                ]
            );

            // تحديث أو إنشاء الترجمة الإنجليزية إذا توفرت
            if (!empty($data['title_en']) || !empty($data['content_en'])) {
                PageTranslation::updateOrCreate(
                    ['page_id' => $page->id, 'locale' => 'en'],
                    [
                        'title' => $data['title_en'] ?? $data['title_ar'],
                        'content' => $data['content_en'] ?? '',
                    ]
                );
            }

            $page->touch();

            return $page->fresh(['translations']);
        });
    }
}

