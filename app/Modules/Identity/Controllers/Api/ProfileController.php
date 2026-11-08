<?php

namespace App\Modules\Identity\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Services\ApiProfileService;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Requests\ApiProfileProfileRequest;
use App\Modules\Identity\Requests\ApiProfileUpdateProfileRequest;
use App\Modules\Identity\Requests\ApiProfileUpdateMobileRequest;

/** متحكم HTTP لمجال Identity؛ يفوض جميع العمليات إلى الخدمة. */
class ProfileController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param ApiProfileService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly ApiProfileService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiProfileProfileRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function profile(ApiProfileProfileRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->profile($request->validated(), $request->user()), 200);
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiProfileUpdateProfileRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function updateProfile(ApiProfileUpdateProfileRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->updateProfile($request->validated(), $request->user()), 200);
    }

    public function updateMobile(ApiProfileUpdateMobileRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->updateMobile($request->validated(), $request->user()), 200);
    }
}
