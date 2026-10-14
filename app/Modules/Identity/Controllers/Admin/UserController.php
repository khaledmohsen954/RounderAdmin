<?php

namespace App\Modules\Identity\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Services\AdminUserService;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Requests\AdminUserIndexRequest;
use App\Modules\Identity\Requests\AdminUserStoreRequest;
use App\Modules\Identity\Requests\AdminUserUpdateRequest;
use App\Modules\Identity\Requests\AdminUserResetPasswordRequest;

/** متحكم HTTP لمجال Identity؛ يفوض جميع العمليات إلى الخدمة. */
class UserController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param AdminUserService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly AdminUserService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminUserIndexRequest $request
     * @return \Illuminate\Contracts\View\View
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(AdminUserIndexRequest $request): \Illuminate\Contracts\View\View
    {
        return view('admin.users.index', $this->service->index($request->validated(), $request->user()));
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminUserStoreRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function store(AdminUserStoreRequest $request): \Illuminate\Http\RedirectResponse
    {
        return back()->with('status', $this->service->store($request->validated(), $request->user()));
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminUserUpdateRequest $request
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function update(AdminUserUpdateRequest $request, User $user): \Illuminate\Http\RedirectResponse
    {
        return back()->with('status', $this->service->update($request->validated(), $request->user(), $user));
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param AdminUserResetPasswordRequest $request
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function resetPassword(AdminUserResetPasswordRequest $request, User $user): \Illuminate\Http\RedirectResponse
    {
        return back()->with('status', $this->service->resetPassword($request->validated(), $request->user(), $user));
    }
}
