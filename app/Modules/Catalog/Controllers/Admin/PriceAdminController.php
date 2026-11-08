<?php

namespace App\Modules\Catalog\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Requests\AdminPriceRequest;
use App\Modules\Catalog\Services\AdminPriceAdminService;
use App\Modules\Properties\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * متحكم HTTP لإدارة أسعار الخدمات الفرعية للوحة الإدارة.
 */
class PriceAdminController extends Controller
{
    public function __construct(private readonly AdminPriceAdminService $service)
    {
    }

    public function index(AdminPriceRequest $request): View
    {
        return view('admin.prices.index', $this->service->index($request->validated(), $request->user()));
    }

    public function create(AdminPriceRequest $request): View
    {
        return view('admin.prices.create', $this->service->createData());
    }

    public function store(AdminPriceRequest $request): RedirectResponse
    {
        $this->service->store($request->validated(), $request->user());

        return redirect()->route('admin.prices.index')
            ->with('status', __('Pricing created successfully.'));
    }

    public function edit(AdminPriceRequest $request, int $priceId): View
    {
        $price = $this->service->edit($priceId);
        $regions = Region::query()->with('translations')->where('is_active', true)->get();

        return view('admin.prices.edit', compact('price', 'regions'));
    }

    public function update(AdminPriceRequest $request, int $priceId): RedirectResponse
    {
        $this->service->update($request->user(), $priceId, $request->validated());

        return redirect()->route('admin.prices.index')
            ->with('status', __('Pricing updated successfully.'));
    }

    public function review(Request $request, int $priceId): RedirectResponse
    {
        $input = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'rejection_reason' => ['nullable', 'string', 'max:2000'],
        ]);
        $this->service->review($priceId, $input['decision'], $input['rejection_reason'] ?? null, $request->user());

        return redirect()->route('admin.prices.index')->with('status', __('Price review saved successfully.'));
    }

    public function destroy(int $priceId): RedirectResponse
    {
        $this->service->destroy(auth()->user(), $priceId);

        return back()->with('status', __('Pricing deleted successfully.'));
    }

    public function getProviderServices(int $providerId): JsonResponse
    {
        return response()->json($this->service->getProviderServices($providerId));
    }

    public function getSubServices(int $serviceId): JsonResponse
    {
        return response()->json($this->service->getSubServices($serviceId));
    }

    public function getRegionCities(int $regionId): JsonResponse
    {
        return response()->json($this->service->getRegionCities($regionId));
    }

    public function getPriceCities(int $priceId): JsonResponse
    {
        return response()->json($this->service->getPriceCities($priceId));
    }
}
