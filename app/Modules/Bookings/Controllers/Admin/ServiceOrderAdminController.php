<?php

namespace App\Modules\Bookings\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Bookings\Services\AdminServiceOrderAdminService;
use App\Modules\Identity\Models\User;
use App\Modules\Bookings\Requests\AdminServiceOrderAdminIndexRequest;
use App\Modules\Bookings\Requests\AdminServiceOrderAdminUpdateRequest;

/** متحكم HTTP لمجال Bookings؛ يفوض جميع العمليات إلى الخدمة. */
class ServiceOrderAdminController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param AdminServiceOrderAdminService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly AdminServiceOrderAdminService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminServiceOrderAdminIndexRequest $request
     * @return \Illuminate\Contracts\View\View
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(AdminServiceOrderAdminIndexRequest $request): \Illuminate\Contracts\View\View
    {
        return view('admin.service-orders.index', $this->service->index($request->validated(), $request->user()));
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminServiceOrderAdminUpdateRequest $request
     * @param int $bookingId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function update(AdminServiceOrderAdminUpdateRequest $request, int $bookingId): \Illuminate\Http\RedirectResponse
    {
        return back()->with('status', $this->service->update($request->validated(), $request->user(), $bookingId));
    }
}
