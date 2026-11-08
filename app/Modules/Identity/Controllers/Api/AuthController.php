<?php

namespace App\Modules\Identity\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Services\ApiAuthService;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Requests\ApiAuthRegisterRequest;
use App\Modules\Identity\Requests\ApiAuthLoginRequest;
use App\Modules\Identity\Requests\ApiSocialLoginRequest;
use App\Modules\Identity\Requests\ApiAuthLogoutRequest;
use App\Modules\Identity\Requests\ApiAuthMeRequest;
use App\Modules\Identity\Requests\ApiValidateMobileRequest;
use App\Modules\Identity\Requests\ApiSendOtpRequest;
use App\Modules\Identity\Requests\ApiVerifyOtpRequest;
use App\Modules\Identity\Services\ResidentSocialLoginService;

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

    public function socialLogin(
        ApiSocialLoginRequest $request,
        ResidentSocialLoginService $socialLogin
    ): \Illuminate\Http\JsonResponse {
        $data = $request->validated();

        return response()->json($socialLogin->login($data['provider'], $data['id_token']), 200);
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

    /**
     * التحقق من رقم الجوال.
     * @param ApiValidateMobileRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateMobile(ApiValidateMobileRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->validateMobile($request->validated(), null), 200);
    }

    /**
     * إرسال رمز التحقق OTP.
     * @param ApiSendOtpRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendOtp(ApiSendOtpRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->sendOtp($request->validated(), null), 200);
    }

    /**
     * التحقق من رمز OTP وتأكيد الدخول.
     * @param ApiVerifyOtpRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyOtp(ApiVerifyOtpRequest $request): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->service->verifyOtp($request->validated(), null), 200);
    }
}
