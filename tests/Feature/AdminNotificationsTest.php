<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Modules\Identity\Models\Admin;
use App\Modules\Access\Models\PermissionSet;
use App\Modules\Notifications\Models\AdminAlert;
use App\Modules\Notifications\Services\AdminAlertService;
use App\Modules\Notifications\Enums\AlertType;
/** Verify visibility, isolated receipts and rollback semantics for dashboard alerts. */
class AdminNotificationsTest extends TestCase {
    use RefreshDatabase;
    /** @param string $email @param array<string> $permissions @return Admin */
    private function admin(string $email,array $permissions): Admin {
        $set=PermissionSet::create(['name_ar'=>$email,'name_en'=>$email,'permissions'=>$permissions]);
        return Admin::create(['name'=>'Operator','email'=>$email,'password'=>'StrongPassword123!',
            'permission_set_id'=>$set->id,'is_active'=>true]);
    }
    /** @return void */
    public function test_permissions_and_independent_read_receipts(): void {
        $first=$this->admin('first@example.test',['emergencies.view']);
        $second=$this->admin('second@example.test',['emergencies.view']);
        $service=app(AdminAlertService::class);
        $service->record(AlertType::Emergency,44); $service->record(AlertType::Property,55);
        $emergency=AdminAlert::where('type',AlertType::Emergency)->firstOrFail();
        $property=AdminAlert::where('type',AlertType::Property)->firstOrFail();
        $this->actingAs($first)->getJson('/admin/notifications')->assertOk()
            ->assertJsonPath('unread',1)->assertJsonCount(1,'items')->assertJsonPath('items.0.urgent',true);
        $this->postJson('/admin/notifications/'.$property->id.'/read')->assertNotFound();
        $this->postJson('/admin/notifications/'.$emergency->id.'/read')->assertOk();
        $this->postJson('/admin/notifications/'.$emergency->id.'/read')->assertOk();
        $this->getJson('/admin/notifications')->assertJsonPath('unread',0);
        $this->actingAs($second)->getJson('/admin/notifications')->assertJsonPath('unread',1);
        $this->postJson('/admin/notifications/read-all')->assertOk();
        $this->getJson('/admin/notifications')->assertJsonPath('unread',0);
        $second->permissionSet->update(['permissions'=>[]]);
        $second->unsetRelation('permissionSet');
        $this->getJson('/admin/notifications')->assertJsonCount(0,'items');
        $this->postJson('/admin/notifications/'.$emergency->id.'/read')->assertNotFound();
        $second->update(['is_active'=>false]);
        $this->getJson('/admin/notifications')->assertForbidden();
    }
    /** @return void */
    public function test_rolled_back_events_do_not_leave_notifications(): void {
        $before=AdminAlert::count();
        try {
            DB::transaction(function(){
                app(AdminAlertService::class)->record(AlertType::Emergency,999);
                throw new \RuntimeException('rollback');
            });
        } catch(\RuntimeException) {}
        $this->assertSame($before,AdminAlert::count());
    }
    /** @return void */
    public function test_property_creation_observer_records_once_and_rolls_back_with_property(): void {
        $this->seed();
        $home=\App\Modules\Properties\Models\Home::create([
            'name'=>'Notification fixture','city_id'=>\App\Modules\Properties\Models\City::available()->value('id'),
            'address'=>'Saudi Arabia','property_code'=>'NOTIFICATION-BASE','health_percent'=>100,'association_balance'=>'0.00']);
        $count=AdminAlert::where('type',AlertType::Property)->count();
        DB::beginTransaction();
        $copy=$home->replicate(); $copy->property_code='NOTIFICATION-TEST'; $copy->save();
        $this->assertSame($count+1,AdminAlert::where('type',AlertType::Property)->count());
        $copy->update(['name'=>'Updated']);
        $this->assertSame($count+1,AdminAlert::where('type',AlertType::Property)->count());
        DB::rollBack();
        $this->assertSame($count,AdminAlert::where('type',AlertType::Property)->count());
    }
}
