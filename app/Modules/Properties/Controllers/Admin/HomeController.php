<?php

namespace App\Modules\Properties\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Properties\Services\AdminHomeService;
use App\Modules\Identity\Models\User;
use App\Modules\Properties\Requests\AdminHomeIndexRequest;
use App\Modules\Properties\Requests\AdminHomeStoreRequest;

/** متحكم HTTP لمجال Properties؛ يفوض جميع العمليات إلى الخدمة. */
class HomeController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param AdminHomeService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly AdminHomeService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminHomeIndexRequest $request
     * @return \Illuminate\Contracts\View\View
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(AdminHomeIndexRequest $request): \Illuminate\Contracts\View\View
    {
        return view('admin.homes.index', $this->service->index($request->validated(), $request->user()));
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminHomeStoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function store(AdminHomeStoreRequest $request): \Illuminate\Http\RedirectResponse
    {
        return back()->with('status', $this->service->store($request->validated(), $request->user()));
    }
}
