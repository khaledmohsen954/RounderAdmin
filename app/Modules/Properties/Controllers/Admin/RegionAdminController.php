<?php

namespace App\Modules\Properties\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Properties\Requests\AdminRegionRequest;
use App\Modules\Properties\Services\AdminRegionAdminService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/** متحكم إدارة المناطق في لوحة الإدارة. */
class RegionAdminController extends Controller
{
    public function __construct(private readonly AdminRegionAdminService $service)
    {
    }

    public function index(AdminRegionRequest $request): View
    {
        return view('admin.regions.index', $this->service->index($request->validated(), $request->user()));
    }

    public function create(AdminRegionRequest $request): View
    {
        return view('admin.regions.create', $this->service->form());
    }

    public function store(AdminRegionRequest $request): RedirectResponse
    {
        return redirect()->route('admin.regions.index')
            ->with('status', $this->service->store($request->validated(), $request->user()));
    }

    public function edit(AdminRegionRequest $request, int $regionId): View
    {
        return view('admin.regions.edit', $this->service->edit($regionId));
    }

    public function update(AdminRegionRequest $request, int $regionId): RedirectResponse
    {
        return redirect()->route('admin.regions.index')
            ->with('status', $this->service->update($request->validated(), $request->user(), $regionId));
    }

    public function destroy(int $regionId): RedirectResponse
    {
        return back()->with('status', $this->service->destroy($regionId));
    }
}
