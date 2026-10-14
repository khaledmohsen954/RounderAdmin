<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
class AdminServiceEditingTest extends TestCase
{
    use RefreshDatabase;
    private function admin(): User
    {
        $this->seed();
        return User::create(['name'=>'Catalogue admin','email'=>'catalogue@example.test',
            'password'=>'TestPassword123!', 'locale'=>'ar',
            'role_id'=>DB::table('roles')->where('code','admin')->value('id')]);
    }
    private function payload(object $service): array
    {
        return ['code'=>$service->code,'service_category_id'=>$service->service_category_id,
            'base_price'=>250,'is_active'=>0,'name_ar'=>'خدمة معدلة','name_en'=>'Updated service','name_ur'=>'نئی خدمت'];
    }
    public function test_admin_can_edit_translations_price_and_availability(): void
    {
        $admin=$this->admin();
        $service=DB::table('services')->first();
        $this->actingAs($admin)->get("/admin/packages/services/{$service->id}/edit")->assertOk();
        $this->put("/admin/packages/services/{$service->id}",$this->payload($service))
            ->assertRedirect(route('admin.packages.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('services',['id'=>$service->id,'base_price'=>250,'is_active'=>false]);
        $this->assertDatabaseHas('service_translations',['service_id'=>$service->id,'locale'=>'en','name'=>'Updated service']);
    }
    public function test_invalid_values_and_normalized_duplicate_codes_are_rejected(): void
    {
        $admin=$this->admin();
        $service=DB::table('services')->first();
        $other=DB::table('services')->where('id','!=',$service->id)->first();
        $data=$this->payload($service);
        $data['code']=strtoupper($other->code);
        $data['base_price']=-1;
        $data['is_active']='invalid';
        $this->actingAs($admin)->put("/admin/packages/services/{$service->id}",$data)
            ->assertSessionHasErrors(['code','base_price','is_active']);
        $this->assertDatabaseHas('services',['id'=>$service->id,'code'=>$service->code,'base_price'=>$service->base_price]);
    }
    public function test_missing_service_and_non_admin_are_rejected(): void
    {
        $admin=$this->admin();
        $this->actingAs($admin)->get('/admin/packages/services/999999/edit')->assertNotFound();
        $resident=User::create(['name'=>'Resident','email'=>'resident-edit@example.test','password'=>'TestPassword123!',
            'locale'=>'ar','role_id'=>DB::table('roles')->where('code','resident')->value('id')]);
        $id=DB::table('services')->value('id');
        $this->actingAs($resident)->put("/admin/packages/services/$id",[])->assertForbidden();
    }
}
