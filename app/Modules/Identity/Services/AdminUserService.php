<?php
namespace App\Modules\Identity\Services;
use App\Modules\Identity\Models\Admin;
use App\Modules\Access\Models\PermissionSet;
use App\Modules\Identity\Events\IdentityChanged;
use App\Modules\Identity\Enums\Operation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
/** إدارة الحسابات الإدارية حصراً؛ الحسابات التشغيلية تبقى في users. */
class AdminUserService
{
    /** @param array<string,mixed> $input @param Admin|null $actor @return array<string,mixed> */
    public function index(array $input, ?Admin $actor): array
    {
        $users = Admin::with('permissionSet')->when(!empty($input['q']), fn ($q) =>
            $q->where(fn ($w) => $w->where('name','like','%'.$input['q'].'%')->orWhere('email','like','%'.$input['q'].'%')->orWhere('phone','like','%'.$input['q'].'%')))
            ->latest()->paginate(15)->withQueryString();
        return compact('users');
    }
    /** @param Admin $actor @param Admin|null $user @return array<string,mixed> */
    public function form(Admin $actor, ?Admin $user = null): array
    {
        $sets = PermissionSet::all()->filter(fn ($set) => $actor->is_super_admin ||
            !array_diff($set->permissions ?? [],$actor->permissionSet?->permissions ?? []));
        return ['user'=>$user ?? new Admin,'sets'=>$sets];
    }
    /** @param array<string,mixed> $input @param Admin $actor @return string @throws \Throwable */
    public function store(array $input, Admin $actor): string
    {
        return DB::transaction(function () use ($input,$actor) {
            $this->assertAssignable($actor,(int)$input['permission_set_id']);
            Admin::create($input + ['is_super_admin'=>false]);
            DB::afterCommit(fn () => event(new IdentityChanged(Operation::AdminUserStore,$actor->id)));
            return __('User created successfully.');
        });
    }
    /** @param array<string,mixed> $input @param Admin $actor @param Admin $user @return string @throws \Throwable */
    public function update(array $input, Admin $actor, Admin $user): string
    {
        return DB::transaction(function () use ($input,$actor,$user) {
            $locked = Admin::lockForUpdate()->findOrFail($user->id);
            $this->assertManageable($actor,$locked);
            $this->assertAssignable($actor,(int)$input['permission_set_id']);
            $locked->update($input);
            DB::afterCommit(fn () => event(new IdentityChanged(Operation::AdminUserUpdate,$actor->id)));
            return __('User updated successfully.');
        });
    }
    /** @param array<string,mixed> $input @param Admin $actor @param Admin $user @return string @throws \Throwable */
    public function resetPassword(array $input, Admin $actor, Admin $user): string
    {
        return DB::transaction(function () use ($input,$actor,$user) {
            $locked = Admin::lockForUpdate()->findOrFail($user->id);
            $this->assertManageable($actor,$locked);
            $locked->forceFill(['password'=>$input['password'],'remember_token'=>Str::random(60)])->save();
            DB::afterCommit(fn () => event(new IdentityChanged(Operation::AdminUserResetPassword,$actor->id)));
            return __('Password updated successfully.');
        });
    }
    /** @param Admin $actor @param Admin $user @return void @throws \Throwable */
    public function delete(Admin $actor, Admin $user): void
    {
        DB::transaction(function () use ($actor,$user) {
            $locked = Admin::lockForUpdate()->findOrFail($user->id);
            $this->assertManageable($actor,$locked);
            // المراجع المالية والتاريخية محمية بمفاتيح RESTRICT؛ لا تُحذف سجلات الأعمال بالتبعية.
            try { $locked->delete(); }
            catch (\Illuminate\Database\QueryException $error) {
                if (($error->errorInfo[1] ?? null) !== 1451) throw $error;
                throw ValidationException::withMessages(['delete'=>__('This record is in use and cannot be deleted.')]);
            }
        });
    }
    /** @param Admin $actor @param Admin $target @return void @throws ValidationException */
    private function assertManageable(Admin $actor, Admin $target): void
    {
        if ($actor->is($target) || $target->is_super_admin ||
            (!$actor->is_super_admin && array_diff($target->permissionSet?->permissions ?? [],$actor->permissionSet?->permissions ?? []))) {
            throw ValidationException::withMessages(['user'=>__('Cannot change your own or a higher privileged account.')]);
        }
    }
    /** @param Admin $actor @param int $id @return void @throws ValidationException */
    private function assertAssignable(Admin $actor, int $id): void
    {
        $set = PermissionSet::lockForUpdate()->findOrFail($id);
        if (!$actor->is_super_admin && array_diff($set->permissions ?? [],$actor->permissionSet?->permissions ?? [])) {
            throw ValidationException::withMessages(['permission_set_id'=>__('Cannot grant permissions you do not have.')]);
        }
    }
}
