<?php

namespace App\Modules\Bookings\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Bookings\Services\ApiBookingRatingService;
use App\Modules\Identity\Models\User;
use App\Modules\Bookings\Requests\ApiBookingRatingRateBookingRequest;

/** متحكم HTTP لمجال Bookings؛ يفوض جميع العمليات إلى الخدمة. */
class BookingRatingController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param ApiBookingRatingService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly ApiBookingRatingService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiBookingRatingRateBookingRequest $request
     * @param int $bookingId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rateBooking(ApiBookingRatingRateBookingRequest $request, int $bookingId): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->rateBooking($request->validated(), $request->user(), $bookingId), 201);
    }
}
