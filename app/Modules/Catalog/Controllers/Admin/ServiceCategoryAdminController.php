<?php

namespace App\Modules\Catalog\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Requests\AdminServiceCategoryIndexRequest;
use App\Modules\Catalog\Requests\AdminServiceCategoryStoreRequest;
use App\Modules\Catalog\Requests\AdminServiceCategoryUpdateRequest;
use App\Modules\Catalog\Services\AdminServiceCategoryAdminService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/** متحكم HTTP لأقسام الخدمات؛ يفوض جميع العمليات إلى خدمة المجال. */
class ServiceCategoryAdminController extends Controller
{
    public function __construct(private readonly AdminServiceCategoryAdminService $service)
    {
    }

    public function index(AdminServiceCategoryIndexRequest $request): View
    {
        return view('admin.service-categories.index', $this->service->index($request->validated(), $request->user()));
    }

    public function create(AdminServiceCategoryIndexRequest $request): View
    {
        return view('admin.service-categories.create', $this->service->form());
    }

    public function store(AdminServiceCategoryStoreRequest $request): RedirectResponse
    {
        return redirect()->route('admin.service-categories.index')
            ->with('status', $this->service->store($request->validated(), $request->user()));
    }

    public function edit(AdminServiceCategoryIndexRequest $request, int $categoryId): View
    {
        return view('admin.service-categories.edit', $this->service->edit($categoryId));
    }

    public function update(AdminServiceCategoryUpdateRequest $request, int $categoryId): RedirectResponse
    {
        return redirect()->route('admin.service-categories.index')
            ->with('status', $this->service->update($request->validated(), $request->user(), $categoryId));
    }

    public function destroy(int $categoryId): RedirectResponse
    {
        return back()->with('status', $this->service->destroy($categoryId));
    }
}
