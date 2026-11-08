<?php

namespace App\Modules\Bookings\Services;

use App\Modules\Bookings\Models\BookingStatus;
use App\Modules\Emergencies\Models\EmergencyRequestStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OrderStatusAdminService
{
    /**
     * @return array{bookingStatuses: Collection, emergencyStatuses: Collection}
     */
    public function getStatuses(): array
    {
        $bookingStatuses = BookingStatus::with('translations')
            ->withCount('bookings')
            ->orderBy('id')
            ->get();

        $emergencyStatuses = EmergencyRequestStatus::with('translations')
            ->withCount('emergencyRequests')
            ->orderBy('id')
            ->get();

        return compact('bookingStatuses', 'emergencyStatuses');
    }

    public function updateSingle(string $type, int $statusId, bool $showInPortal): array
    {
        if ($type === 'booking') {
            $status = BookingStatus::findOrFail($statusId);
        } else {
            $status = EmergencyRequestStatus::findOrFail($statusId);
        }

        $status->update([
            'show_in_provider_portal' => $showInPortal,
        ]);

        return [
            'ok' => true,
            'status_id' => $status->id,
            'type' => $type,
            'show_in_provider_portal' => (bool) $status->show_in_provider_portal,
            'message' => 'تم تحديث حالة الظهور لمزود الخدمة بنجاح.',
        ];
    }

    public function updateBatch(array $visibleBookingIds, array $visibleEmergencyIds): void
    {
        DB::transaction(function () use ($visibleBookingIds, $visibleEmergencyIds) {
            foreach (BookingStatus::all() as $status) {
                $status->update([
                    'show_in_provider_portal' => in_array((int) $status->id, $visibleBookingIds, true),
                ]);
            }

            foreach (EmergencyRequestStatus::all() as $status) {
                $status->update([
                    'show_in_provider_portal' => in_array((int) $status->id, $visibleEmergencyIds, true),
                ]);
            }
        });
    }
}

