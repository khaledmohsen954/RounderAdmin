<?php

namespace App\Modules\Settings\Services;

use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;
use App\Modules\Settings\Models\Page;

/**
 * خدمة قراءة الصفحات العامة (الشروط والخصوصية) للواجهات والتطبيق.
 */
class ApiPageService
{
    /**
     * استرجاع صفحة بالـ slug بحسب لغة التطبيق.
     *
     * @param string $slug
     * @return array<string, mixed>
     */
    public function getPage(string $slug): array
    {
        $page = Page::with('translations')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        DomainViolation::unless($page, FailureReason::NotFound, __('Page not found.'));

        $locale = app()->getLocale();
        $translation = $page->translations->firstWhere('locale', $locale)
            ?? $page->translations->firstWhere('locale', 'ar')
            ?? $page->translations->first();

        return [
            'data' => [
                'id' => $page->id,
                'slug' => $page->slug,
                'title' => $translation?->title ?? '',
                'content' => $translation?->content ?? '',
                'updated_at' => $page->updated_at?->toIso8601String(),
            ],
        ];
    }
}

