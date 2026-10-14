<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingStatus;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** طلبات الساكن؛ تُقيّد القراءة والتعديل دائماً بصاحب الحساب الحالي. */
class BookingController extends Controller
{
    /** تحميل الخدمة والحالة مرة واحدة لكل صفحة لتجنب استعلام لكل طلب. */
    public function index(Request $request): JsonResponse
    {
        $page = Booking::with(['home', 'service.translations', 'status.translations'])
            ->where('resident_id', $request->user()->id)
            ->latest()->paginate(max(1, min($request->integer('per_page', 15), 50)));
        $locale = app()->getLocale();
        $items = $page->getCollection()->map(fn (Booking $booking) => [
            'id' => $booking->id,
            'service_name' => $booking->service->translations->firstWhere('locale', $locale)?->name ?? $booking->service->code,
            'status_name' => $booking->status->translations->firstWhere('locale', $locale)?->name ?? $booking->status->code,
            'status_code' => $booking->status->code,
            'scheduled_at' => $booking->scheduled_at,
            'total_amount' => $booking->total_amount,
        ])->values();

        return response()->json(['data' => $items, 'meta' => [
            'current_page' => $page->currentPage(), 'last_page' => $page->lastPage(),
            'per_page' => $page->perPage(), 'total' => $page->total(),
        ]]);
    }

    /** السعر يؤخذ من الكتالوج؛ إنشاء الطلب وتاريخه عملية واحدة لا تتجزأ. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'home_id' => ['required', 'integer', 'exists:homes,id',
                Rule::exists('resident_homes', 'home_id')->where('user_id', $request->user()->id)],
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where('is_active', true)],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $booking = DB::transaction(function () use ($request, $data) {
            $pending = BookingStatus::where('code', 'pending')->firstOrFail();
            $service = Service::where('is_active', true)->lockForUpdate()->findOrFail($data['service_id']);
            $booking = $request->user()->bookings()->create($data + [
                'booking_status_id' => $pending->id, 'total_amount' => $service->base_price,
            ]);
            $booking->statusHistory()->create([
                'booking_status_id' => $pending->id,
                'changed_by' => $request->user()->id, 'note' => 'Created by resident',
            ]);

            return $booking;
        });

        return response()->json(['data' => $booking->load(['service.translations', 'status.translations'])], 201);
    }

    /** نعيد التحقق من الحالة تحت القفل حتى لا يُلغى طلب بدأ تنفيذه بالتزامن. */
    public function cancel(Request $request, int $bookingId): JsonResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:3', 'max:500']]);
        $booking = DB::transaction(function () use ($request, $bookingId, $data) {
            $booking = $request->user()->bookings()->lockForUpdate()->findOrFail($bookingId);
            abort_unless(in_array($booking->status()->value('code'), ['pending', 'confirmed'], true),
                409, __('This booking can no longer be cancelled.'));
            $cancelled = BookingStatus::where('code', 'cancelled')->firstOrFail();
            $booking->update([
                'booking_status_id' => $cancelled->id,
                'notes' => trim(($booking->notes ? $booking->notes."\n" : '').'Cancellation: '.$data['reason']),
            ]);
            $booking->statusHistory()->create([
                'booking_status_id' => $cancelled->id,
                'changed_by' => $request->user()->id, 'note' => $data['reason'],
            ]);

            return $booking;
        });

        return response()->json(['data' => $booking->fresh()->load('status.translations')]);
    }
}
