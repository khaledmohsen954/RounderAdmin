<?php
namespace Tests\Feature;
use App\Modules\Identity\Models\{Admin,User,Role};
use App\Modules\Access\Models\PermissionSet;
use App\Modules\Access\Services\AdminPermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
/** اختبار الفصل الفعلي للمصادقة والصلاحيات، وليس ظهور الأزرار فقط. */
class AdminAccessTest extends TestCase
{
    use RefreshDatabase;
    /** @return Admin */
    private function superAdmin(): Admin
    {
        $this->seed();
        return Admin::create(['name'=>'Super','email'=>'super@example.com','password'=>'StrongPassword123!',
            'locale'=>'ar','is_super_admin'=>true,'is_active'=>true]);
    }
    /** @return void */
    public function test_admin_creation_is_separate_from_application_users(): void
    {
        $root=$this->superAdmin();
        $set=PermissionSet::create(['name_ar'=>'مشاهدة','name_en'=>'Read only','permissions'=>['providers.view']]);
        $this->actingAs($root)->get('/admin/users/create')->assertOk()->assertDontSee('id="createUser"',false);
        $payload=['name'=>'Operator','email'=>'operator@example.com','phone'=>'+966500000099','locale'=>'ar',
            'permission_set_id'=>$set->id,'is_active'=>1,'password'=>'StrongPassword123!','password_confirmation'=>'StrongPassword123!',
            'is_super_admin'=>true];
        $this->post('/admin/users',$payload)->assertSessionHasNoErrors()->assertRedirect(route('admin.users.index'));
        $operator=Admin::where('email',$payload['email'])->firstOrFail();
        $this->assertFalse($operator->is_super_admin);
        $this->assertDatabaseMissing('users',['email'=>$payload['email']]);
        $this->assertTrue(Hash::check($payload['password'],$operator->password));
        $this->post('/admin/logout');
        $this->post('/admin/login',['email'=>$operator->email,'password'=>$payload['password']])->assertRedirect();
        $this->assertAuthenticatedAs($operator);
        $this->get('/admin/providers')->assertOk()->assertDontSee(route('admin.providers.create'));
        $this->get('/admin/providers/create')->assertForbidden();
        $this->post('/admin/providers',[])->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }
    /** @return void */
    public function test_permission_matrix_validates_and_enforces_each_operation(): void
    {
        $root=$this->superAdmin();
        $this->actingAs($root)->get('/admin/permissions/create')->assertOk()->assertSee('permissions[]',false);
        $this->post('/admin/permissions',['name_ar'=>'مزودون','name_en'=>'Providers',
            'permissions'=>['providers.view','providers.create']])->assertSessionHasNoErrors();
        $set=PermissionSet::where('name_en','Providers')->firstOrFail();
        $operator=Admin::create(['name'=>'Operator','email'=>'permission@example.com','password'=>'StrongPassword123!',
            'permission_set_id'=>$set->id,'is_active'=>true]);
        $permissions=app(AdminPermissionService::class);
        foreach(['index'=>true,'create'=>true,'edit'=>false,'destroy'=>false] as $suffix=>$expected) {
            $this->assertSame($expected,$permissions->allowsRoute($operator,'admin.providers.'.$suffix,$suffix==='destroy'?'DELETE':'GET'));
        }
        $this->assertFalse($permissions->allowsRoute($operator,'admin.unregistered.index','GET'));
        $this->put('/admin/permissions/'.$set->id,['name_ar'=>'مزودون','name_en'=>'Providers','permissions'=>['unknown.delete']])
            ->assertSessionHasErrors('permissions.0');
        $this->delete('/admin/permissions/'.$set->id)->assertSessionHasErrors('delete');
        $this->actingAs($operator)->get('/admin/permissions')->assertForbidden();
        $operator->update(['is_active'=>false]);
        $this->get('/admin/providers')->assertForbidden();
    }
    /** @return void */
    public function test_application_account_cannot_enter_admin_even_with_an_admin_role(): void
    {
        $root=$this->superAdmin();
        $legacy=User::create(['name'=>'Legacy','email'=>'legacy@example.com','password'=>'StrongPassword123!',
            'role_id'=>Role::where('code','admin')->value('id'),'locale'=>'ar']);
        $this->actingAs($legacy)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($root)->delete('/admin/users/'.$root->id)->assertSessionHasErrors('user');
    }
}
