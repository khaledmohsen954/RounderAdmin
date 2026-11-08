<?php

namespace App\Modules\Settings\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Requests\AdminPageUpdateRequest;
use App\Modules\Settings\Services\AdminPageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * متحكم إدارة الصفحات الثابتة والسياسات من لوحة التحكم.
 */
class PageAdminController extends Controller
{
    public function __construct(private readonly AdminPageService $service)
    {
    }

    /**
     * عرض قائمة الصفحات الثابتة (الشروط والأحكام، سياسة الخصوصية).
     */
    public function index(): View
    {
        $pages = $this->service->list();

        return view('admin.pages.index', compact('pages'));
    }

    /**
     * عرض شاشة تعديل الصفحة ومحتواها.
     */
    public function edit(int $page): View
    {
        $pageModel = $this->service->find($page);
        $translationAr = $pageModel->translations->firstWhere('locale', 'ar');
        $translationEn = $pageModel->translations->firstWhere('locale', 'en');

        return view('admin.pages.edit', [
            'page' => $pageModel,
            'translationAr' => $translationAr,
            'translationEn' => $translationEn,
        ]);
    }

    /**
     * حفظ تعديلات الصفحة والترجمات.
     */
    public function update(AdminPageUpdateRequest $request, int $page): RedirectResponse
    {
        $this->service->update($page, $request->validated());

        return redirect()->route('admin.pages.index')->with('status', __('Page updated successfully.'));
    }
}

