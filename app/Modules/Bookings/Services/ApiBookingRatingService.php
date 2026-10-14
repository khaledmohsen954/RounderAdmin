<?php

namespace App\Modules\Bookings\Services;
use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;

use App\Modules\Bookings\Models\Booking;
use App\Modules\Bookings\Models\BookingRating;

use Illuminate\Database\QueryException;

use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use App\Modules\Bookings\Events\BookingsChanged;
use App\Modules\Bookings\Enums\Operation;

/** قواعد عمل Bookings؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class ApiBookingRatingService
{

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @param int $bookingId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rateBooking(array $input, ?User $actor, int $bookingId): mixed
    {
        return DB::transaction(function () use ($input, $actor, $bookingId) {
            $result = $this->performRateBooking($input, $actor, $bookingId);
            DB::afterCommit(fn () => event(new BookingsChanged(Operation::ApiBookingRatingRateBooking, $actor?->id)));
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
    private function performRateBooking(array $input, ?User $actor, int $bookingId): mixed
    {
        $data = $input;

        $booking = Booking::query()->where('id', $bookingId)
            ->where('resident_id', $actor->id)->first();
        DomainViolation::unless($booking, FailureReason::NotFound);

        

        try {
            $id = BookingRating::query()->create([
                'booking_id' => $bookingId,
                'resident_id' => $actor->id,
                ...$data,
                'created_at' => now(),
                'updated_at' => now(),
            ])->getKey();
        } catch (QueryException) {
            throw new DomainViolation(FailureReason::Conflict, 'This booking has already been rated.');
        }

        return ['data' => BookingRating::query()->find($id)];
    }

}
