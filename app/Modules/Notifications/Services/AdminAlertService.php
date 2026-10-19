<?php
namespace App\Modules\Notifications\Services;
use App\Modules\Notifications\Enums\AlertType;
use App\Modules\Notifications\Models\AdminAlert;
use App\Modules\Access\Services\AdminPermissionService;
use App\Modules\Identity\Models\Admin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
/** Durable activity feed; permission revocation immediately removes access to old events. */
class AdminAlertService {
    /** @param AdminPermissionService $permissions */
    public function __construct(private AdminPermissionService $permissions) {}
    /** Save in the originating transaction, so rolled-back operations never leave phantom alerts.
     * @param AlertType $type @param int $subjectId @return void @throws \Throwable */
    public function record(AlertType $type, int $subjectId): void {
        DB::transaction(fn()=>AdminAlert::create(['type'=>$type,'subject_id'=>$subjectId]));
    }
    /** @param Admin $admin @return Builder */
    private function visible(Admin $admin): Builder {
        $types=array_map(fn($t)=>$t->value,array_filter(AlertType::cases(),
            fn($t)=>$this->permissions->allows($admin,$t->permission())));
        return AdminAlert::whereIn('type',$types);
    }
    /** @param Admin $admin @return array<string,mixed> */
    public function feed(Admin $admin): array {
        $query=$this->visible($admin);
        return [
            'unread'=>$this->visible($admin)->whereDoesntHave('reads',fn($q)=>$q->where('admin_id',$admin->id))->count(),
            'items'=>$query->withExists(['reads as is_read'=>fn($q)=>$q->where('admin_id',$admin->id)])
                ->latest('id')->limit(20)->get()->map(fn($alert)=>[
                    'id'=>$alert->id,'title'=>__($alert->type->label()),'subject_id'=>$alert->subject_id,
                    'urgent'=>$alert->type===AlertType::Emergency,'read'=>(bool)$alert->is_read,
                    'time'=>$alert->created_at->toIso8601String(),
                    'url'=>route($alert->type->route()),
                ]),
        ];
    }
    /** Reads are scoped to both the administrator and currently visible event.
     * @param Admin $admin @param int $id @return void @throws \Throwable */
    public function markRead(Admin $admin,int $id): void {
        DB::transaction(function()use($admin,$id){
            $alert=$this->visible($admin)->findOrFail($id);
            $alert->reads()->firstOrCreate(['admin_id'=>$admin->id],['read_at'=>now()]);
        });
    }
    /** @param Admin $admin @return void @throws \Throwable */
    public function markAllRead(Admin $admin): void {
        DB::transaction(function()use($admin){
            $this->visible($admin)->whereDoesntHave('reads',fn($q)=>$q->where('admin_id',$admin->id))
                ->chunkById(200,function($alerts)use($admin){
                    foreach($alerts as $alert) $alert->reads()->firstOrCreate(['admin_id'=>$admin->id],['read_at'=>now()]);
                });
        });
    }
}
