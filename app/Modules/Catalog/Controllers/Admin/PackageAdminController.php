<?php

namespace App\Modules\Catalog\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Catalog\Services\AdminPackageAdminService;
use App\Modules\Identity\Models\User;
use App\Modules\Catalog\Requests\AdminPackageAdminIndexRequest;
use App\Modules\Catalog\Requests\AdminPackageAdminStoreServiceRequest;
use App\Modules\Catalog\Requests\AdminPackageAdminStorePackageRequest;
use App\Modules\Catalog\Requests\AdminPackageAdminEditServiceRequest;
use App\Modules\Catalog\Requests\AdminPackageAdminUpdateServiceRequest;

/** متحكم HTTP لمجال Catalog؛ يفوض جميع العمليات إلى الخدمة. */
class PackageAdminController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param AdminPackageAdminService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly AdminPackageAdminService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminPackageAdminIndexRequest $request
     * @return \Illuminate\Contracts\View\View
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(AdminPackageAdminIndexRequest $request): \Illuminate\Contracts\View\View
    {
        return view('admin.packages.index', $this->service->index($request->validated(), $request->user()));
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminPackageAdminStoreServiceRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function storeService(AdminPackageAdminStoreServiceRequest $request): \Illuminate\Http\RedirectResponse
    {
        return back()->with('status', $this->service->storeService($request->validated(), $request->user()));
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminPackageAdminStorePackageRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function storePackage(AdminPackageAdminStorePackageRequest $request): \Illuminate\Http\RedirectResponse
    {
        return back()->with('status', $this->service->storePackage($request->validated(), $request->user()));
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminPackageAdminEditServiceRequest $request
     * @param int $serviceId
     * @return \Illuminate\Contracts\View\View
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function editService(AdminPackageAdminEditServiceRequest $request, int $serviceId): \Illuminate\Contracts\View\View
    {
        return view('admin.packages.edit-service', $this->service->editService($request->validated(), $request->user(), $serviceId));
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminPackageAdminUpdateServiceRequest $request
     * @param int $serviceId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function updateService(AdminPackageAdminUpdateServiceRequest $request, int $serviceId): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('admin.packages.index')->with('status', $this->service->updateService($request->validated(), $request->user(), $serviceId));
    }

    /**
     * @param \App\Modules\Catalog\Requests\AdminPackageAdminEditPackageRequest $request
     * @param int $packageId
     * @return \Illuminate\Contracts\View\View
     * @throws \Throwable
     */
    public function editPackage(\App\Modules\Catalog\Requests\AdminPackageAdminEditPackageRequest $request, int $packageId): \Illuminate\Contracts\View\View
    {
        return view('admin.packages.edit-package', $this->service->editPackage($request->validated(), $request->user(), $packageId));
    }

    /**
     * @param \App\Modules\Catalog\Requests\AdminPackageAdminUpdatePackageRequest $request
     * @param int $packageId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable
     */
    public function updatePackage(\App\Modules\Catalog\Requests\AdminPackageAdminUpdatePackageRequest $request, int $packageId): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('admin.packages.index')->with('status', $this->service->updatePackage($request->validated(), $request->user(), $packageId));
    }
}
