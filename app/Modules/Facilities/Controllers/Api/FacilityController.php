<?php

namespace App\Modules\Facilities\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Facilities\Services\ApiFacilityService;
use App\Modules\Identity\Models\User;
use App\Modules\Facilities\Requests\ApiFacilityFacilityReportsRequest;
use App\Modules\Facilities\Requests\ApiFacilityCreateFacilityReportRequest;

/** متحكم HTTP لمجال Facilities؛ يفوض جميع العمليات إلى الخدمة. */
class FacilityController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param ApiFacilityService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly ApiFacilityService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiFacilityFacilityReportsRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function facilityReports(ApiFacilityFacilityReportsRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->facilityReports($request->validated(), $request->user()), 200);
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiFacilityCreateFacilityReportRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function createFacilityReport(ApiFacilityCreateFacilityReportRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->createFacilityReport($request->validated(), $request->user()), 201);
    }
}
