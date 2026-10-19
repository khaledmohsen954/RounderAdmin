<?php

namespace App\Modules\Providers\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Providers\Services\AdminProviderService;
use App\Modules\Identity\Models\User;
use App\Modules\Providers\Requests\AdminProviderIndexRequest;
use App\Modules\Providers\Requests\AdminProviderStoreRequest;

/** متحكم HTTP لمجال Providers؛ يفوض جميع العمليات إلى الخدمة. */
class ProviderController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param AdminProviderService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly AdminProviderService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminProviderIndexRequest $request
     * @return \Illuminate\Contracts\View\View
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(AdminProviderIndexRequest $request): \Illuminate\Contracts\View\View
    {
        return view('admin.providers.index', $this->service->index($request->validated(), $request->user()));
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminProviderStoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function store(AdminProviderStoreRequest $request): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('admin.providers.index')->with('status', $this->service->store($request->validated(), $request->user()));
    }
    /**
     * عرض صفحة الإضافة دون تحميل جدول الشركات.
     * @param AdminProviderIndexRequest $request
     * @return \Illuminate\Contracts\View\View
     */
    public function create(AdminProviderIndexRequest $request): \Illuminate\Contracts\View\View
    {
        return view('admin.providers.create');
    }

    /**
     * @param AdminProviderIndexRequest $request
     * @param \App\Modules\Providers\Models\ProviderProfile $provider
     * @return \Illuminate\Contracts\View\View
     * @throws \Throwable
     */
    public function edit(AdminProviderIndexRequest $request, \App\Modules\Providers\Models\ProviderProfile $provider): \Illuminate\Contracts\View\View
    {
        return view('admin.providers.edit', $this->service->edit($provider->getKey()));
    }

    /**
     * @param \App\Modules\Providers\Requests\AdminProviderUpdateRequest $request
     * @param \App\Modules\Providers\Models\ProviderProfile $provider
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable
     */
    public function update(\App\Modules\Providers\Requests\AdminProviderUpdateRequest $request, \App\Modules\Providers\Models\ProviderProfile $provider): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('admin.providers.index')->with('status',
            $this->service->update($provider->getKey(), $request->validated(), $request->user()));
    }
}

