<?php
namespace Tests\Feature;
use App\Modules\Identity\Models\{Role, User};
use App\Modules\Properties\Models\City;
use App\Modules\Properties\Services\SaudiCityImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
/** بيانات مرجعية حقيقية وعلاقات محلية ثابتة عبر إعادة الاستيراد. */
class SaudiCitiesTest extends TestCase
{
    use RefreshDatabase;
    /** @return void */
    public function test_saudi_cities_are_id_based_and_legacy_foreign_keys_are_preserved(): void
    {
        $legacy = City::create(['code'=>'cairo']);
        $this->seed();
        $this->assertSame(4581, City::available()->count());
        $this->assertFalse($legacy->fresh()->is_active);
        $riyadh = City::where('source_city_id',3)->firstOrFail();
        app(SaudiCityImportService::class)->import();
        $this->assertSame($riyadh->id,City::where('source_city_id',3)->value('id'));
        $this->assertSame(4582,City::count());
        $admin = \App\Modules\Identity\Models\Admin::create(['is_super_admin'=>true,'is_active'=>true,'name'=>'Admin','email'=>'cities-admin@example.test','password'=>'StrongPassword123!',
            'locale'=>'ar','role_id'=>Role::where('code','admin')->value('id')]);
        $input = ['city_id'=>$riyadh->id,'name'=>'Riyadh Home','address'=>'Riyadh address',
            'property_code'=>'SA-HOME','health_percent'=>100,'association_balance'=>0,'floors_count'=>2,'units'=>[['unit_number'=>'1','floor_number'=>1,'area_sqm'=>100,'rooms_count'=>3]]];
        $this->actingAs($admin)->post('/admin/realestate',$input)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('homes',['city_id'=>$riyadh->id,'property_code'=>'SA-HOME']);
        $input['city_id']=$legacy->id;
        $input['property_code']='EG-HOME';
        $this->post('/admin/realestate',$input)->assertSessionHasErrors('city_id');
        $resident = User::create(['name'=>'Resident','email'=>'cities-resident@example.test','password'=>'StrongPassword123!',
            'locale'=>'ar','role_id'=>Role::where('code','resident')->value('id')]);
        Sanctum::actingAs($resident);
        $response = $this->getJson('/api/v1/resident/cities?search='.urlencode('الرياض'))->assertOk();
        $this->assertContains($riyadh->id, array_column($response->json('data'),'id'));
        $this->getJson('/api/v1/resident/cities?per_page=101')->assertUnprocessable();
    }
}
