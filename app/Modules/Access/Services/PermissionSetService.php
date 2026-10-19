<?php
namespace App\Modules\Access\Services;
use App\Modules\Access\Models\PermissionSet;
use App\Modules\Access\Enums\{DashboardSection, PermissionAction};
use App\Modules\Access\Events\PermissionsChanged;
use App\Modules\Identity\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
/** إدارة مجموعات الصلاحيات؛ منع رفع الصلاحيات الذاتية أو تعديل مجموعة مدير أعلى. */
final class PermissionSetService
{
    /** @return array<string,mixed> */
    public function index(): array
    {
        return ['sets'=>PermissionSet::withCount('admins')->latest()->paginate(20)];
    }
    /** @param int|null $id @return array<string,mixed> @throws \Throwable */
    public function form(?int $id = null): array
    {
        return ['set'=>$id ? PermissionSet::findOrFail($id) : new PermissionSet,
            'sections'=>DashboardSection::cases(), 'actions'=>PermissionAction::cases()];
    }
    /**
     * @param array<string,mixed> $input @param Admin $actor @param int|null $id
     * @return int @throws \Throwable
     */
    public function save(array $input, Admin $actor, ?int $id = null): int
    {
        return DB::transaction(function () use ($input, $actor, $id) {
            $set = $id ? PermissionSet::lockForUpdate()->findOrFail($id) : new PermissionSet;
            $this->assertManageable($actor, $set);
            $permissions = $input['permissions'] ?? [];
            if (!$actor->is_super_admin && array_diff($permissions, $actor->permissionSet?->permissions ?? [])) {
                throw ValidationException::withMessages(['permissions'=>__('Cannot grant permissions you do not have.')]);
            }
            $set->fill(['name_ar'=>$input['name_ar'],'name_en'=>$input['name_en'],'permissions'=>$permissions])->save();
            DB::afterCommit(fn () => event(new PermissionsChanged($set->id,$actor->id)));
            return $set->id;
        });
    }
    /** @param int $id @param Admin $actor @return void @throws \Throwable */
    public function delete(int $id, Admin $actor): void
    {
        DB::transaction(function () use ($id, $actor) {
            $set = PermissionSet::lockForUpdate()->findOrFail($id);
            $this->assertManageable($actor,$set);
            if ($set->admins()->exists()) throw ValidationException::withMessages(['delete'=>__('This record is in use and cannot be deleted.')]);
            $set->delete();
            DB::afterCommit(fn () => event(new PermissionsChanged($id,$actor->id)));
        });
    }
    /** @param Admin $actor @param PermissionSet $set @return void @throws ValidationException */
    private function assertManageable(Admin $actor, PermissionSet $set): void
    {
        if (!$actor->is_super_admin && ($actor->permission_set_id === $set->id ||
            array_diff($set->permissions ?? [], $actor->permissionSet?->permissions ?? []))) {
            throw ValidationException::withMessages(['permissions'=>__('Cannot change your own or a higher permission set.')]);
        }
    }
}
