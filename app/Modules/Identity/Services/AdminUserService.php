<?php

namespace App\Modules\Identity\Services;
use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;

use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;

use Illuminate\Support\Facades\DB;
use App\Modules\Identity\Events\IdentityChanged;
use App\Modules\Identity\Enums\Operation;

/** قواعد عمل Identity؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class AdminUserService
{

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(array $input, ?User $actor): mixed
    {
        $users=User::with('role.translations')->when((isset($input['q']) && $input['q'] !== ''),fn($q)=>$q->where(fn($w)=>$w->where('name','like','%'.($input['q'] ?? '').'%')->orWhere('email','like','%'.($input['q'] ?? '').'%')->orWhere('phone','like','%'.($input['q'] ?? '').'%')))
   ->when((isset($input['role']) && $input['role'] !== ''),fn($q)=>$q->whereHas('role',fn($r)=>$r->where('code',($input['role'] ?? ''))))->latest()->paginate(15)->withQueryString();
  $roles=Role::with('translations')->orderBy('code')->get();
  return compact('users','roles');
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function store(array $input, ?User $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performStore($input, $actor);
            DB::afterCommit(fn () => event(new IdentityChanged(Operation::AdminUserStore, $actor?->id)));
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
    private function performStore(array $input, ?User $actor): mixed
    {
        $data = $input;

  
  User::create($data);
  return __('User created successfully.');
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @param User $user
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function update(array $input, ?User $actor, User $user): mixed
    {
        return DB::transaction(function () use ($input, $actor, $user) {
            $result = $this->performUpdate($input, $actor, $user);
            DB::afterCommit(fn () => event(new IdentityChanged(Operation::AdminUserUpdate, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @param User $user
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performUpdate(array $input, ?User $actor, User $user): mixed
    {
        $data = $input;

  
  DomainViolation::rejectIf($actor->is($user), FailureReason::InvalidInput, __('You cannot change your own account here.'));
  $user->update($data); return __('User updated successfully.');
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @param User $user
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function resetPassword(array $input, ?User $actor, User $user): mixed
    {
        return DB::transaction(function () use ($input, $actor, $user) {
            $result = $this->performResetPassword($input, $actor, $user);
            DB::afterCommit(fn () => event(new IdentityChanged(Operation::AdminUserResetPassword, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @param User $user
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performResetPassword(array $input, ?User $actor, User $user): mixed
    {
        $data = $input;

  
  $user->update(['password'=>$data['password']]); $user->tokens()->delete();
  return __('Password updated and active app sessions revoked.');
    }
}
