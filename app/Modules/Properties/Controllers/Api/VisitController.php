<?php

namespace App\Modules\Properties\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Properties\Services\ApiVisitService;
use App\Modules\Identity\Models\User;
use App\Modules\Properties\Requests\ApiVisitVisitsRequest;
use App\Modules\Properties\Requests\ApiVisitCreateVisitRequest;
use App\Modules\Properties\Requests\ApiVisitCancelVisitRequest;

/** متحكم HTTP لمجال Properties؛ يفوض جميع العمليات إلى الخدمة. */
class VisitController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param ApiVisitService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly ApiVisitService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiVisitVisitsRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function visits(ApiVisitVisitsRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->visits($request->validated(), $request->user()), 200);
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiVisitCreateVisitRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function createVisit(ApiVisitCreateVisitRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->createVisit($request->validated(), $request->user()), 201);
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiVisitCancelVisitRequest $request
     * @param int $visitId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function cancelVisit(ApiVisitCancelVisitRequest $request, int $visitId): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->cancelVisit($request->validated(), $request->user(), $visitId), 200);
    }
}
