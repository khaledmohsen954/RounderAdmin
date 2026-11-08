<?php

namespace App\Modules\Properties\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Properties\Requests\AdminCityRequest;
use App\Modules\Properties\Services\AdminCityAdminService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/** متحكم إدارة المدن في لوحة الإدارة. */
class CityAdminController extends Controller
{
    public function __construct(private readonly AdminCityAdminService $service)
    {
    }

    public function index(AdminCityRequest $request): View
    {
        return view('admin.cities.index', $this->service->index($request->validated(), $request->user()));
    }

    public function create(AdminCityRequest $request): View
    {
        return view('admin.cities.create', $this->service->form());
    }

    public function store(AdminCityRequest $request): RedirectResponse
    {
        return redirect()->route('admin.cities.index')
            ->with('status', $this->service->store($request->validated(), $request->user()));
    }

    public function edit(AdminCityRequest $request, int $cityId): View
    {
        return view('admin.cities.edit', $this->service->edit($cityId));
    }

    public function update(AdminCityRequest $request, int $cityId): RedirectResponse
    {
        return redirect()->route('admin.cities.index')
            ->with('status', $this->service->update($request->validated(), $request->user(), $cityId));
    }

    public function destroy(int $cityId): RedirectResponse
    {
        return back()->with('status', $this->service->destroy($cityId));
    }
}
