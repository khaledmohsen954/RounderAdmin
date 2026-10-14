<?php

namespace App\Modules\Emergencies\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Emergencies\Services\ApiEmergencyRequestService;
use App\Modules\Identity\Models\User;
use App\Modules\Emergencies\Requests\ApiEmergencyRequestIndexRequest;
use App\Modules\Emergencies\Requests\ApiEmergencyRequestStoreRequest;
use App\Modules\Emergencies\Requests\ApiEmergencyRequestCancelRequest;

/** متحكم HTTP لمجال Emergencies؛ يفوض جميع العمليات إلى الخدمة. */
class EmergencyRequestController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param ApiEmergencyRequestService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly ApiEmergencyRequestService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiEmergencyRequestIndexRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(ApiEmergencyRequestIndexRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->index($request->validated(), $request->user()), 200);
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiEmergencyRequestStoreRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function store(ApiEmergencyRequestStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->store($request->validated(), $request->user()), 201);
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiEmergencyRequestCancelRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function cancel(ApiEmergencyRequestCancelRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->cancel($request->validated(), $request->user()), 200);
    }
}
