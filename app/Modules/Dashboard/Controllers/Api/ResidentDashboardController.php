<?php

namespace App\Modules\Dashboard\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Services\ApiResidentDashboardService;
use App\Modules\Identity\Models\User;
use App\Modules\Dashboard\Requests\ApiResidentDashboard__invokeRequest;

/** متحكم HTTP لمجال Dashboard؛ يفوض جميع العمليات إلى الخدمة. */
class ResidentDashboardController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param ApiResidentDashboardService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly ApiResidentDashboardService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiResidentDashboard__invokeRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __invoke(ApiResidentDashboard__invokeRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->__invoke($request->validated(), $request->user()), 200);
    }
}
