<?php

namespace App\Modules\Bookings\Services;
use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;
use App\Modules\Bookings\Enums\BookingStatusCode;

use App\Modules\Bookings\Models\Booking;
use App\Modules\Bookings\Models\BookingStatus;
use App\Modules\Catalog\Models\Service;

use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use App\Modules\Bookings\Events\BookingsChanged;
use App\Modules\Bookings\Enums\Operation;

/** قواعد عمل Bookings؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class ApiBookingService
{

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(array $input, ?User $actor): mixed
    {
        $page = Booking::with(['home', 'service.translations', 'status.translations'])
            ->where('resident_id', $actor->id)
            ->latest()->paginate(max(1, min((int) ($input['per_page'] ?? 15), 50)));
        $locale = app()->getLocale();
        $items = $page->getCollection()->map(fn (Booking $booking) => [
            'id' => $booking->id,
            'service_name' => $booking->service->translations->firstWhere('locale', $locale)?->name ?? $booking->service->code,
            'status_name' => $booking->status->translations->firstWhere('locale', $locale)?->name ?? $booking->status->code,
            'status_code' => $booking->status->code,
            'scheduled_at' => $booking->scheduled_at,
            'total_amount' => $booking->total_amount,
        ])->values();

        return ['data' => $items, 'meta' => [
            'current_page' => $page->currentPage(), 'last_page' => $page->lastPage(),
            'per_page' => $page->perPage(), 'total' => $page->total(),
        ]];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function store(array $input, ?User $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performStore($input, $actor);
            DB::afterCommit(fn () => event(new BookingsChanged(Operation::ApiBookingStore, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performStore(array $input, ?User $actor): mixed
    {
        $data = $input;

        

        $booking = DB::transaction(function () use ($input, $actor, $data) {
            $pending = BookingStatus::where('code', BookingStatusCode::Pending->value)->firstOrFail();
            $service = Service::where('is_active', true)->lockForUpdate()->findOrFail($data['service_id']);
            $booking = $actor->bookings()->create($data + [
                'booking_status_id' => $pending->id, 'total_amount' => $service->base_price,
            ]);
            $booking->statusHistory()->create([
                'booking_status_id' => $pending->id,
                'changed_by' => $actor->id, 'note' => 'Created by resident',
            ]);

            return $booking;
        });

        return ['data' => $booking->load(['service.translations', 'status.translations'])];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @param int $bookingId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function cancel(array $input, ?User $actor, int $bookingId): mixed
    {
        return DB::transaction(function () use ($input, $actor, $bookingId) {
            $result = $this->performCancel($input, $actor, $bookingId);
            DB::afterCommit(fn () => event(new BookingsChanged(Operation::ApiBookingCancel, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @param int $bookingId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performCancel(array $input, ?User $actor, int $bookingId): mixed
    {
        $data = $input;

        
        $booking = DB::transaction(function () use ($input, $actor, $bookingId, $data) {
            $booking = $actor->bookings()->lockForUpdate()->findOrFail($bookingId);
            DomainViolation::unless(in_array($booking->status()->value('code'), [BookingStatusCode::Pending->value, BookingStatusCode::Confirmed->value], true), FailureReason::Conflict, __('This booking can no longer be cancelled.'));
            $cancelled = BookingStatus::where('code', BookingStatusCode::Cancelled->value)->firstOrFail();
            $booking->update([
                'booking_status_id' => $cancelled->id,
                'notes' => trim(($booking->notes ? $booking->notes."\n" : '').'Cancellation: '.$data['reason']),
            ]);
            $booking->statusHistory()->create([
                'booking_status_id' => $cancelled->id,
                'changed_by' => $actor->id, 'note' => $data['reason'],
            ]);

            return $booking;
        });

        return ['data' => $booking->fresh()->load('status.translations')];
    }
}
