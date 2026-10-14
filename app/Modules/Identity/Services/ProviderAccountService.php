<?php
namespace App\Modules\Identity\Services;
use App\Modules\Identity\Models\{User, Role};
use App\Modules\Identity\Enums\{RoleCode, Operation};
use App\Modules\Identity\Events\IdentityChanged;
use Illuminate\Support\Facades\DB;
/** نقطة إنشاء حساب مزود من المجالات الأخرى دون تكرار سياسة الدور وكلمة المرور. */
final class ProviderAccountService
{
    /**
     * hashed cast في User يحفظ كلمة المرور مشفرة مرة واحدة.
     * @param array<string, mixed> $input
     * @param int|null $actorId
     * @return User
     * @throws \Throwable
     */
    public function create(array $input, ?int $actorId): User
    {
        return DB::transaction(function () use ($input, $actorId) {
            $role = Role::where('code', RoleCode::Provider->value)->firstOrFail();
            $user = User::create([
                'role_id' => $role->id, 'name' => $input['name'], 'email' => $input['email'],
                'phone' => $input['phone'], 'locale' => $input['locale'], 'password' => $input['password'],
            ]);
            DB::afterCommit(fn () => event(new IdentityChanged(Operation::CreateProviderAccount, $actorId)));
            return $user;
        });
    }
}
