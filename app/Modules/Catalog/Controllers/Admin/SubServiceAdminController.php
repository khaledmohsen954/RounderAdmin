<?php

namespace App\Modules\Catalog\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Requests\AdminSubServiceIndexRequest;
use App\Modules\Catalog\Requests\AdminSubServiceStoreRequest;
use App\Modules\Catalog\Requests\AdminSubServiceUpdateRequest;
use App\Modules\Catalog\Services\AdminSubServiceAdminService;

/** متحكم HTTP للخدمات الفرعية؛ يفوض جميع العمليات إلى الخدمة. */
class SubServiceAdminController extends Controller
{
    public function __construct(private readonly AdminSubServiceAdminService $service)
    {
    }

    public function index(AdminSubServiceIndexRequest $request): \Illuminate\Contracts\View\View
    {
        return view('admin.sub-services.index', $this->service->index($request->validated(), $request->user()));
    }

    public function create(AdminSubServiceIndexRequest $request): \Illuminate\Contracts\View\View
    {
        return view('admin.sub-services.create', $this->service->form());
    }

    public function store(AdminSubServiceStoreRequest $request): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('admin.sub-services.index')
            ->with('status', $this->service->store($request->validated(), $request->user()));
    }

    public function edit(AdminSubServiceIndexRequest $request, int $subServiceId): \Illuminate\Contracts\View\View
    {
        return view('admin.sub-services.edit', $this->service->edit($subServiceId));
    }

    public function update(AdminSubServiceUpdateRequest $request, int $subServiceId): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('admin.sub-services.index')
            ->with('status', $this->service->update($request->validated(), $request->user(), $subServiceId));
    }

    public function destroy(int $subServiceId): \Illuminate\Http\RedirectResponse
    {
        return back()->with('status', $this->service->destroy($subServiceId));
    }
}
