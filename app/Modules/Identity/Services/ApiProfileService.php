<?php

namespace App\Modules\Identity\Services;

use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
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
