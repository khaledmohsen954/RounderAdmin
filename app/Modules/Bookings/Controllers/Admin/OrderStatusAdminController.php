<?php

namespace App\Modules\Bookings\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Bookings\Requests\AdminUpdateOrderStatusVisibilityRequest;
use App\Modules\Bookings\Services\OrderStatusAdminService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * عرض حالات الطلبات والطوارئ والتحكم في ظهورها داخل لوحة مزود الخدمة فقط.
 */
class OrderStatusAdminController extends Controller
{
    public function __construct(
        private readonly OrderStatusAdminService $service
    ) {
    }

    public function index(): View
    {
        return view('admin.order-statuses.index', $this->service->getStatuses());
    }

    public function update(AdminUpdateOrderStatusVisibilityRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        if (array_key_exists('type', $validated) && array_key_exists('status_id', $validated)) {
            $result = $this->service->updateSingle(
                $validated['type'],
                (int) $validated['status_id'],
                (bool) $validated['show_in_provider_portal']
            );

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json($result);
            }

            return redirect()->route('admin.order-statuses.index')
                ->with('status', $result['message']);
        }

        $rawBookingIds = $validated['booking_statuses'] ?? $validated['booking_status_ids'] ?? [];
        $rawEmergencyIds = $validated['emergency_statuses'] ?? $validated['emergency_status_ids'] ?? [];

        $visibleBookingIds = array_map('intval', $rawBookingIds);
        $visibleEmergencyIds = array_map('intval', $rawEmergencyIds);

        $this->service->updateBatch($visibleBookingIds, $visibleEmergencyIds);

        return redirect()->route('admin.order-statuses.index')
            ->with('status', 'تم حفظ إعدادات ظهور الحالات بنجاح.');
    }
}
