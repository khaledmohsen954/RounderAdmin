<?php

namespace App\Modules\Dashboard\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Services\AdminDashboardService;
use App\Modules\Identity\Models\User;
use App\Modules\Dashboard\Requests\AdminDashboard__invokeRequest;

/** متحكم HTTP لمجال Dashboard؛ يفوض جميع العمليات إلى الخدمة. */
class DashboardController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param AdminDashboardService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly AdminDashboardService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminDashboard__invokeRequest $request
     * @return \Illuminate\Contracts\View\View
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __invoke(AdminDashboard__invokeRequest $request): \Illuminate\Contracts\View\View
    {
        return view('admin.dashboard', $this->service->__invoke($request->validated(), $request->user()));
    }
}
