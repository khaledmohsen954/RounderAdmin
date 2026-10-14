<?php

namespace App\Modules\Emergencies\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Emergencies\Services\AdminEmergencyAdminService;
use App\Modules\Identity\Models\User;
use App\Modules\Emergencies\Requests\AdminEmergencyAdminIndexRequest;
use App\Modules\Emergencies\Requests\AdminEmergencyAdminUpdateRequest;

/** متحكم HTTP لمجال Emergencies؛ يفوض جميع العمليات إلى الخدمة. */
class EmergencyAdminController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param AdminEmergencyAdminService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly AdminEmergencyAdminService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminEmergencyAdminIndexRequest $request
     * @return \Illuminate\Contracts\View\View
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(AdminEmergencyAdminIndexRequest $request): \Illuminate\Contracts\View\View
    {
        return view('admin.emergencies.index', $this->service->index($request->validated(), $request->user()));
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminEmergencyAdminUpdateRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function update(AdminEmergencyAdminUpdateRequest $request): \Illuminate\Http\RedirectResponse
    {
        return back()->with('status', $this->service->update($request->validated(), $request->user()));
    }
}
