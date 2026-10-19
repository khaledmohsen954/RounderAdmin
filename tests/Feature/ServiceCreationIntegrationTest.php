<?php

namespace Tests\Feature;

use App\Modules\Catalog\Models\{Service, ServiceCategory};
use App\Modules\Identity\Models\{Role, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** خدمة ينشئها المدير تظهر عبر API بالمعرّف نفسه، دون بيانات ثابتة في العميل. */
class ServiceCreationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @return void */
    public function test_created_service_is_persisted_and_available_for_integration(): void
    {
        $this->seed();
        Storage::fake('public');
        $admin = User::create(['name'=>'Admin','email'=>'service-form@example.test','password'=>'StrongPassword123!',
            'locale'=>'ar','role_id'=>Role::where('code','admin')->value('id')]);
        $this->actingAs($admin);
        foreach (['ar','en'] as $locale) {
            $this->withSession(['locale'=>$locale])->get('/admin/services/create')->assertOk()->assertSee('name="code"',false);
        }
        $input = ['service_category_id'=>ServiceCategory::where('is_active',true)->value('id'),
            'code'=>'admin-created','base_price'=>'200.00','discount_type'=>'percentage','discount_value'=>'10.00',
            'name_ar'=>'خدمة جديدة','name_en'=>'Created service','name_ur'=>'نئی خدمت',
            'description_ar'=>'وصف الخدمة','description_en'=>'Description','description_ur'=>'تفصیل',
            'is_active'=>1,'image'=>UploadedFile::fake()->image('service.png')];
        $this->from('/admin/services/create')->post('/admin/packages/services',$input)
            ->assertRedirect('/admin/services/create')->assertSessionHasNoErrors();
        $service = Service::where('code','admin-created')->firstOrFail();
        $this->assertSame('180.00',$service->base_price);
        $this->assertSame(3,$service->translations()->count());
        Storage::disk('public')->assertExists($service->image_path);
        $inactive = $input;
        $inactive['code'] = 'inactive-service';
        $inactive['is_active'] = 0;
        unset($inactive['image']);
        $this->post('/admin/packages/services',$inactive)->assertSessionHasNoErrors();
        $this->post('/admin/packages/services',$input)->assertSessionHasErrors('code');
        $resident = User::create(['name'=>'Resident','email'=>'service-api@example.test','password'=>'StrongPassword123!',
            'locale'=>'en','role_id'=>Role::where('code','resident')->value('id')]);
        Sanctum::actingAs($resident);
        $rows = collect($this->getJson('/api/v1/resident/services')->assertOk()->json('data'));
        $row = $rows->firstWhere('id',$service->id);
        $this->assertNotNull($row);
        $this->assertEquals(180,$row['price']);
        $this->assertSame($service->service_category_id,$row['category']['id']);
        $this->assertNotEmpty($row['image_url']);
        $this->assertNull($rows->firstWhere('code','inactive-service'));
        $this->actingAs($resident)->get('/admin/services/create')->assertForbidden();
        $this->post('/admin/packages/services',$input)->assertForbidden();
    }
}
