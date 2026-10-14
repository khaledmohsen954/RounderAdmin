<?php

namespace App\Modules\Emergencies\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Emergencies\Services\AdminEmergencyCreationService;
use App\Modules\Identity\Models\User;
use App\Modules\Emergencies\Requests\AdminEmergencyCreationEmergencyFormRequest;
use App\Modules\Emergencies\Requests\AdminEmergencyCreationStoreEmergencyRequest;

/** متحكم HTTP لمجال Emergencies؛ يفوض جميع العمليات إلى الخدمة. */
class EmergencyCreationController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param AdminEmergencyCreationService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly AdminEmergencyCreationService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminEmergencyCreationEmergencyFormRequest $request
     * @return \Illuminate\Contracts\View\View
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function emergencyForm(AdminEmergencyCreationEmergencyFormRequest $request): \Illuminate\Contracts\View\View
    {
        return view('admin.orders.create', $this->service->emergencyForm($request->validated(), $request->user()));
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminEmergencyCreationStoreEmergencyRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function storeEmergency(AdminEmergencyCreationStoreEmergencyRequest $request): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('admin.emergencies.index')->with('status', $this->service->storeEmergency($request->validated(), $request->user()));
    }
}
