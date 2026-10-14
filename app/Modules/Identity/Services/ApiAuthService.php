<?php

namespace App\Modules\Identity\Services;
use App\Modules\Identity\Enums\RoleCode;
use App\Core\Enums\SupportedLocale;

use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Modules\Identity\Events\IdentityChanged;
use App\Modules\Identity\Enums\Operation;

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

  
  $role=Role::where('code',RoleCode::Resident->value)->firstOrFail();
  $user=User::create($data+['role_id'=>$role->id,'locale'=>$data['locale']?? SupportedLocale::Arabic->value]);
  return ['data'=>['user'=>$user->only(['id','name','email','phone','locale']),'token'=>$user->createToken('rounder-mobile')->plainTextToken]];
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

  
  $user=User::with('role')->where('email',$data['email'])->first();
  if(!$user||!Hash::check($data['password'],$user->password)||$user->role->code!==RoleCode::Resident->value)
   throw ValidationException::withMessages(['email'=>__('auth.failed')]);
  return ['data'=>['user'=>$user->only(['id','name','email','phone','locale']),'token'=>$user->createToken('rounder-mobile')->plainTextToken]];
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
        return ['data'=>$actor->only(['id','name','email','phone','locale'])];
    }
}
