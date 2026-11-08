<?php

namespace App\Modules\Identity\Services;

use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use App\Modules\Identity\Events\IdentityChanged;
use App\Modules\Identity\Enums\Operation;

/** قواعد عمل Identity؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class ApiProfileService
{

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function profile(array $input, ?User $actor): mixed
    {
        $user = $actor;

        return ['data' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'locale' => $user->locale,
        ]];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function updateProfile(array $input, ?User $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performUpdateProfile($input, $actor);
            DB::afterCommit(fn () => event(new IdentityChanged(Operation::ApiProfileUpdateProfile, $actor?->id)));
            return $result;
        });
    }

    public function updateMobile(array $input, ?User $actor): array
    {
        return DB::transaction(function () use ($input, $actor): array {
            $mobile = $input['mobile'];
            $international = '+966' . substr($mobile, 1);
            $otp = (string) $input['otp'];
            $cachedOtp = Cache::pull('otp_' . $mobile);
            $internationalOtp = Cache::pull('otp_' . $international);
            $cachedOtp ??= $internationalOtp;

            if (!is_string($cachedOtp) || !hash_equals($cachedOtp, $otp)) {
                throw ValidationException::withMessages([
                    'otp' => 'رمز التحقق غير صحيح أو منتهي الصلاحية.',
                ]);
            }

            $actor->phone = $mobile;
            $actor->save();
            DB::afterCommit(fn () => event(new IdentityChanged(Operation::ApiProfileUpdateMobile, $actor->id)));

            return $this->profile([], $actor);
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performUpdateProfile(array $input, ?User $actor): mixed
    {
        $data = $input;

        
        $actor->fill($data)->save();

        return $this->profile($input, $actor);
    }

}
