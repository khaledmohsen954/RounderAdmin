<?php

namespace App\Modules\Bookings\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Bookings\Services\AdminOrderCreationService;
use App\Modules\Identity\Models\User;
use App\Modules\Bookings\Requests\AdminOrderCreationServiceFormRequest;
use App\Modules\Bookings\Requests\AdminOrderCreationStoreServiceRequest;

/** متحكم HTTP لمجال Bookings؛ يفوض جميع العمليات إلى الخدمة. */
class OrderCreationController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param AdminOrderCreationService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly AdminOrderCreationService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminOrderCreationServiceFormRequest $request
     * @return \Illuminate\Contracts\View\View
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function serviceForm(AdminOrderCreationServiceFormRequest $request): \Illuminate\Contracts\View\View
    {
        return view('admin.orders.create', $this->service->serviceForm($request->validated(), $request->user()));
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminOrderCreationStoreServiceRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function storeService(AdminOrderCreationStoreServiceRequest $request): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('admin.service-orders.index')->with('status', $this->service->storeService($request->validated(), $request->user()));
    }
}
