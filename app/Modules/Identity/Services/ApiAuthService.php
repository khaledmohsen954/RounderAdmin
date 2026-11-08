<?php

namespace App\Modules\Identity\Services;
use App\Modules\Identity\Enums\RoleCode;
use App\Core\Enums\SupportedLocale;

use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Modules\Identity\Events\IdentityChanged;
use App\Modules\Identity\Enums\Operation;
use App\Modules\Identity\Services\SMS;
use Illuminate\Support\Facades\Log;

/** قواعد عمل Identity؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class ApiAuthService
{

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function register(array $input, ?User $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performRegister($input, $actor);
            DB::afterCommit(fn () => event(new IdentityChanged(Operation::ApiAuthRegister, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performRegister(array $input, ?User $actor): mixed
    {
        $data = $input;
        $mobile = $data['mobile'] ?? $data['phone'] ?? null;
        if ($mobile !== null) {
            $data['phone'] = $mobile;
            unset($data['mobile']);
        }

        $role = Role::where('code', RoleCode::Resident->value)->firstOrFail();
        $user = User::create($data + ['role_id' => $role->id, 'locale' => $data['locale'] ?? SupportedLocale::Arabic->value]);
        return ['data' => ['user' => $user->only(['id', 'name', 'email', 'phone', 'locale']) + ['mobile' => $user->phone], 'token' => $user->createToken('rounder-mobile')->plainTextToken]];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function login(array $input, ?User $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performLogin($input, $actor);
            DB::afterCommit(fn () => event(new IdentityChanged(Operation::ApiAuthLogin, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performLogin(array $input, ?User $actor): mixed
    {
        $data = $input;
        $mobile = $data['mobile'] ?? $data['phone'] ?? null;

        $user = User::with('role')
            ->where(function ($query) use ($mobile, $data) {
                if ($mobile) {
                    $international = '+966' . substr($mobile, 1);
                    $query->where('phone', $mobile)
                          ->orWhere('phone', $international)
                          ->orWhere('phone', ltrim($mobile, '0'));
                } elseif (!empty($data['email'])) {
                    $query->where('email', $data['email']);
                }
            })
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password) || $user->role->code !== RoleCode::Resident->value) {
            throw ValidationException::withMessages(['mobile' => __('auth.failed')]);
        }
        return ['data' => ['user' => $user->only(['id', 'name', 'email', 'phone', 'locale']) + ['mobile' => $user->phone], 'token' => $user->createToken('rounder-mobile')->plainTextToken]];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function logout(array $input, ?User $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performLogout($input, $actor);
            DB::afterCommit(fn () => event(new IdentityChanged(Operation::ApiAuthLogout, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performLogout(array $input, ?User $actor): mixed
    {
        $actor->currentAccessToken()?->delete();return ['message'=>'Signed out'];
    }

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function me(array $input, ?User $actor): mixed
    {
        return ['data' => $actor->only(['id', 'name', 'email', 'phone', 'locale']) + ['mobile' => $actor->phone]];
    }

    /**
     * التحقق من صحة رقم الجوال وهل هو مسجل مسبقاً.
     * @param array $input
     * @param ?User $actor
     * @return array
     */
    public function validateMobile(array $input, ?User $actor): array
    {
        $mobile = $input['mobile'] ?? $input['phone'] ?? '';
        $international = '+966' . substr($mobile, 1);

        $user = User::where(function ($q) use ($mobile, $international) {
            $q->where('phone', $mobile)
              ->orWhere('phone', $international)
              ->orWhere('phone', ltrim($mobile, '0'));
        })->first();

        return [
            'status' => true,
            'message' => 'رقم الجوال صحيح ومقبول.',
            'data' => [
                'mobile' => $mobile,
                'formatted_mobile' => $international,
                'is_registered' => (bool) $user,
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                ] : null,
            ],
        ];
    }

    /**
     * إرسال رمز تحقق OTP مكون من 6 أرقام.
     * @param array $input
     * @param ?User $actor
     * @return array
     */
    public function sendOtp(array $input, ?User $actor): array
    {
        $mobile = $input['mobile'] ?? $input['phone'] ?? '';
        $international = '+966' . substr($mobile, 1);

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        try {
            SMS::send($mobile, 'Your OTP code is ' . $otp);
        } catch (\Throwable $e) {
            Log::error('Failed to send OTP', [
                'mobile' => $mobile,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        // حفظ الكود في Cache لمدة 3 دقائق
        Cache::put('otp_' . $mobile, $otp, now()->addMinutes(3));
        Cache::put('otp_' . $international, $otp, now()->addMinutes(3));

        $response = [
            'status' => true,
            'message' => 'تم إرسال رمز التحقق المكون من 6 أرقام بنجاح.',
            'data' => [
                'mobile' => $mobile,
                'formatted_mobile' => $international,
                'expires_in_seconds' => 180,
            ],
        ];

        // إرجاع الكود في بيئة التطوير لتسهيل التجربة في Postman والتطبيق
        if (app()->environment('local', 'testing') || config('app.debug')) {
            $response['data']['otp'] = $otp;
        }

        return $response;
    }

    /**
     * التحقق من صحة رمز OTP وتأكيد الدخول.
     * @param array $input
     * @param ?User $actor
     * @return array
     * @throws ValidationException
     */
    public function verifyOtp(array $input, ?User $actor): array
    {
        $mobile = $input['mobile'] ?? $input['phone'] ?? '';
        $international = '+966' . substr($mobile, 1);
        $otp = (string) ($input['otp'] ?? '');

        $cachedOtp = Cache::get('otp_' . $mobile) ?? Cache::get('otp_' . $international);

        $isMatch = is_string($cachedOtp) && hash_equals($cachedOtp, $otp);

        if (!$isMatch) {
            throw ValidationException::withMessages([
                'otp' => 'رمز التحقق غير صحيح أو منتهي الصلاحية.',
            ]);
        }

        // مسح الكود بعد الاستخدام
        Cache::forget('otp_' . $mobile);
        Cache::forget('otp_' . $international);

        // فحص وجود المستخدم
        $user = User::with('role')->where(function ($q) use ($mobile, $international) {
            $q->where('phone', $mobile)
              ->orWhere('phone', $international)
              ->orWhere('phone', ltrim($mobile, '0'));
        })->first();

        if ($user) {
            $token = $user->createToken('rounder-mobile')->plainTextToken;
            return [
                'status' => true,
                'message' => 'تم التحقق بنجاح وتسجيل الدخول.',
                'data' => [
                    'is_registered' => true,
                    'user' => $user->only(['id', 'name', 'email', 'phone', 'locale']) + ['mobile' => $user->phone],
                    'token' => $token,
                ],
            ];
        }

        return [
            'status' => true,
            'message' => 'تم التحقق من رقم الجوال بنجاح، يُرجى إكمال إنشاء الحساب.',
            'data' => [
                'is_registered' => false,
                'mobile' => $mobile,
                'formatted_mobile' => $international,
            ],
        ];
    }
}
