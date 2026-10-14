<?php

namespace App\Modules\Facilities\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Facilities\Services\AdminFacilityAdminService;
use App\Modules\Identity\Models\User;
use App\Modules\Facilities\Requests\AdminFacilityAdminUpdateFacilityReportRequest;

/** متحكم HTTP لمجال Facilities؛ يفوض جميع العمليات إلى الخدمة. */
class FacilityAdminController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param AdminFacilityAdminService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly AdminFacilityAdminService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminFacilityAdminUpdateFacilityReportRequest $request
     * @param int $reportId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function updateFacilityReport(AdminFacilityAdminUpdateFacilityReportRequest $request, int $reportId): \Illuminate\Http\RedirectResponse
    {
        return back()->with('success', $this->service->updateFacilityReport($request->validated(), $request->user(), $reportId));
    }
}
