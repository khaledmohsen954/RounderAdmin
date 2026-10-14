<?php

namespace App\Modules\Community\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Community\Services\AdminResidentContentService;
use App\Modules\Identity\Models\User;
use App\Modules\Community\Requests\AdminResidentContentIndexRequest;
use App\Modules\Community\Requests\AdminResidentContentStoreAnnouncementRequest;
use App\Modules\Community\Requests\AdminResidentContentStoreInitiativeRequest;

/** متحكم HTTP لمجال Community؛ يفوض جميع العمليات إلى الخدمة. */
class ResidentContentController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param AdminResidentContentService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly AdminResidentContentService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminResidentContentIndexRequest $request
     * @return \Illuminate\Contracts\View\View
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(AdminResidentContentIndexRequest $request): \Illuminate\Contracts\View\View
    {
        return view('admin.content.index', $this->service->index($request->validated(), $request->user()));
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminResidentContentStoreAnnouncementRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function storeAnnouncement(AdminResidentContentStoreAnnouncementRequest $request): \Illuminate\Http\RedirectResponse
    {
        return back()->with('success', $this->service->storeAnnouncement($request->validated(), $request->user()));
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminResidentContentStoreInitiativeRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function storeInitiative(AdminResidentContentStoreInitiativeRequest $request): \Illuminate\Http\RedirectResponse
    {
        return back()->with('success', $this->service->storeInitiative($request->validated(), $request->user()));
    }
}
