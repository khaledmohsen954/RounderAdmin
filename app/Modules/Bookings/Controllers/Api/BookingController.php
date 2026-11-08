<?php

namespace App\Modules\Bookings\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Bookings\Services\ApiBookingService;
use App\Modules\Identity\Models\User;
use App\Modules\Bookings\Requests\ApiBookingIndexRequest;
use App\Modules\Bookings\Requests\ApiBookingStoreRequest;
use App\Modules\Bookings\Requests\ApiBookingCancelRequest;
use App\Modules\Bookings\Requests\ApiResidentServiceProvidersRequest;

/** متحكم HTTP لمجال Bookings؛ يفوض جميع العمليات إلى الخدمة. */
class BookingController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param ApiBookingService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly ApiBookingService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiBookingIndexRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(ApiBookingIndexRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->index($request->validated(), $request->user()), 200);
    }

    /**
     * إرجاع تفاصيل طلب محدد للساكن مع مراحل الطلب.
     * @param int $bookingId
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $bookingId, \Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->show($bookingId, $request->user()), 200);
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiBookingStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function store(ApiBookingStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->store($request->validated(), $request->user()), 201);
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiBookingCancelRequest $request
     * @param int $bookingId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function cancel(ApiBookingCancelRequest $request, int $bookingId): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->cancel($request->validated(), $request->user(), $bookingId), 200);
    }

    /**
     * إرجاع قائمة مزودي الخدمة المعتمدين لتطبيق الجوال.
     * @return \Illuminate\Http\JsonResponse
     */
    public function providers(ApiResidentServiceProvidersRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->serviceProviders($request->validated(), $request->user()), 200);
    }

    /**
     * إرجاع قائمة حالات الحجوزات لتطبيق الجوال.
     * @return \Illuminate\Http\JsonResponse
     */
    public function statuses(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->statuses(), 200);
    }
}
