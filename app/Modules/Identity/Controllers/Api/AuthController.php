<?php

namespace App\Modules\Identity\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Services\ApiAuthService;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Requests\ApiAuthRegisterRequest;
use App\Modules\Identity\Requests\ApiAuthLoginRequest;
use App\Modules\Identity\Requests\ApiAuthLogoutRequest;
use App\Modules\Identity\Requests\ApiAuthMeRequest;

/** متحكم HTTP لمجال Identity؛ يفوض جميع العمليات إلى الخدمة. */
class AuthController extends Controller
{
    /**
     * حقن خدمة المجال؛ لا توجد استعلامات أو قواعد عمل داخل المتحكم.
     * @param ApiAuthService $service
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function __construct(private readonly ApiAuthService $service)
    {
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiAuthRegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function register(ApiAuthRegisterRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->register($request->validated(), null), 201);
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiAuthLoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function login(ApiAuthLoginRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->login($request->validated(), null), 200);
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiAuthLogoutRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function logout(ApiAuthLogoutRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->logout($request->validated(), $request->user()), 200);
    }

    /**
     * تمرير الطلب المتحقق منه وإرجاع استجابة HTTP فقط.
     * @param ApiAuthMeRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function me(ApiAuthMeRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->me($request->validated(), $request->user()), 200);
    }
}
