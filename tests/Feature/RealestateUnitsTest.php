<?php
namespace Tests\Feature;
use App\Modules\Identity\Models\{Admin,User,Role};
use App\Modules\Properties\Models\{Home,City,ResidentHome};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
/** حفظ الشقق وعلاقاتها وتأكيد منع حذف بيانات مأهولة. */
class RealestateUnitsTest extends TestCase
{
    use RefreshDatabase;
    /** @return void */
    public function test_building_and_apartments_save_update_and_delete_with_guards(): void
    {
        $this->seed();
        $admin=Admin::create(['name'=>'Admin','email'=>'units@example.test','password'=>'StrongPassword123!','is_super_admin'=>true,'is_active'=>true,'locale'=>'ar']);
        $this->actingAs($admin)->get('/admin/realestate/add')->assertOk()->assertSee('units[0][area_sqm]',false);
        $data=['name'=>'Building','city_id'=>City::available()->value('id'),'address'=>'Saudi Arabia','property_code'=>'TEST-UNITS',
            'health_percent'=>100,'association_balance'=>'0.00','floors_count'=>2,
            'units'=>[['unit_number'=>'101','floor_number'=>1,'area_sqm'=>'120.50','rooms_count'=>3],
                      ['unit_number'=>'201','floor_number'=>2,'area_sqm'=>'90.00','rooms_count'=>2]]];
        $this->post('/admin/realestate',$data)->assertSessionHasNoErrors()->assertRedirect(route('admin.realestate.index'));
        $home=Home::where('property_code','TEST-UNITS')->firstOrFail();
        $this->assertSame(2,$home->units()->count());
        $unit=$home->units()->firstOrFail();
        $this->assertSame('120.50',$unit->area_sqm);
        $this->get('/admin/realestate/'.$home->id.'/edit')->assertOk()->assertSee('120.50');
        $data['units']=$home->units->map->only(['id','unit_number','floor_number','area_sqm','rooms_count'])->all();
        $data['units'][0]['area_sqm']='130.00';
        $this->put('/admin/realestate/'.$home->id,$data)->assertSessionHasNoErrors();
        $this->assertSame('130.00',$unit->fresh()->area_sqm);
        $invalid=$data; $invalid['units'][0]['floor_number']=3;
        $this->put('/admin/realestate/'.$home->id,$invalid)->assertSessionHasErrors('units.0.floor_number');
        $resident=User::create(['name'=>'Resident','email'=>'units-resident@example.test','password'=>'StrongPassword123!',
            'role_id'=>Role::where('code','resident')->value('id'),'locale'=>'ar']);
        ResidentHome::create(['user_id'=>$resident->id,'home_id'=>$home->id,'home_unit_id'=>$unit->id,'unit_label'=>'101']);
        $this->delete('/admin/realestate/'.$home->id)->assertSessionHasErrors('delete');
        $data['units']=[$data['units'][1]];
        $this->put('/admin/realestate/'.$home->id,$data)->assertSessionHasErrors('units');
        $this->assertSame(2,$home->units()->count());
    }
}
