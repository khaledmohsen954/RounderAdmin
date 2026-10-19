<?php
namespace Tests\Feature;

use App\Modules\Catalog\Models\{Service, ServicePackage, PackageBillingCycle, ResidentSubscription};
use App\Modules\Catalog\Services\{CatalogPricingService, ApiResidentPackageService};
use App\Modules\Identity\Models\{User, Role};
use App\Modules\Properties\Models\{City, Home, ResidentHome};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** تغطية مالية وحفظ فعلي على MySQL، مع التأكد من بقاء حقوق المشترك القديمة. */
class CatalogPricingTest extends TestCase
{
    use RefreshDatabase;

    /** @return User */
    private function admin(): \App\Modules\Identity\Models\Admin
    {
        $this->seed();
        return \App\Modules\Identity\Models\Admin::create(['is_super_admin'=>true,'is_active'=>true,'name'=>'Catalog admin','email'=>'discount-admin@example.test',
            'password'=>'StrongPassword123!','locale'=>'en','role_id'=>Role::where('code','admin')->value('id')]);
    }

    /** @return array<string, mixed> */
    private function packageInput(): array
    {
        return ['code'=>'dynamic-plan','billing_cycle_id'=>PackageBillingCycle::value('id'),
            'price'=>'1000.00','original_price'=>'1200.00','discount_type'=>'percentage','discount_value'=>'12.50',
            'currency_code'=>'SAR','duration_months'=>12,'is_active'=>1,
            'name_ar'=>'باقة ديناميكية','name_en'=>'Dynamic plan','name_ur'=>'متحرک پیکیج',
            'services'=>[['service_id'=>Service::value('id'),'included_quantity'=>4,'is_unlimited'=>0]]];
    }

    /** @return void */
    public function test_rounding_and_discount_boundaries_use_minor_units(): void
    {
        $pricing = app(CatalogPricingService::class);
        $this->assertSame('0.02', $pricing->attributes(['price'=>'0.05','discount_type'=>'percentage','discount_value'=>'50'], 'price')['price']);
        $this->assertSame('80.00', $pricing->attributes(['price'=>'100','discount_type'=>'fixed','discount_value'=>'20'], 'price')['price']);
        $this->assertSame('0.00', $pricing->attributes(['price'=>'100','discount_type'=>'percentage','discount_value'=>'100'], 'price')['price']);
        $this->assertSame('9999999999.99', $pricing->attributes(['price'=>'9999999999.99'], 'price')['price']);
        foreach ([
            ['price'=>'100','discount_type'=>'fixed','discount_value'=>'100.01'],
            ['price'=>'100','discount_type'=>'percentage','discount_value'=>'100.01'],
            ['price'=>'100','discount_type'=>'none','discount_value'=>'1'],
            ['price'=>'1.001'], ['price'=>'-1'], ['price'=>'1e2'],
        ] as $input) {
            try {
                $pricing->attributes($input, 'price');
                $this->fail('Invalid financial input was accepted.');
            } catch (ValidationException $exception) {
                $this->assertNotEmpty($exception->errors());
            }
        }
    }

    /** @return void */
    public function test_package_edit_updates_catalog_but_preserves_subscription_snapshots(): void
    {
        $admin = $this->admin();
        $data = $this->packageInput();
        $this->actingAs($admin)->post('/admin/packages', $data)->assertRedirect()->assertSessionHasNoErrors();
        $package = ServicePackage::where('code',$data['code'])->firstOrFail();
        $this->assertSame('875.00', $package->price);
        $this->get('/admin/packages/'.$package->id.'/edit')->assertOk()->assertSee('discount_value');
        $resident = User::create(['name'=>'Subscriber','email'=>'subscriber@example.test','password'=>'StrongPassword123!',
            'role_id'=>Role::where('code','resident')->value('id'),'locale'=>'en']);
        $home = Home::create(['city_id'=>City::value('id'),'name'=>'Home','address'=>'Address','property_code'=>'CAT-001']);
        $membership = ResidentHome::create(['user_id'=>$resident->id,'home_id'=>$home->id,'unit_label'=>'1']);
        app(ApiResidentPackageService::class)->subscribe(['resident_home_id'=>$membership->id,'service_package_id'=>$package->id],$resident);
        $snapshot = ResidentSubscription::where('service_package_id',$package->id)->firstOrFail();
        $data['discount_type']='fixed';
        $data['discount_value']='100.00';
        $data['services'][0]['included_quantity']=8;
        $this->put('/admin/packages/'.$package->id,$data)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('900.00',$package->fresh()->price);
        $this->assertDatabaseHas('service_package_services',['service_package_id'=>$package->id,'included_quantity'=>8]);
        $this->assertSame('875.00',$snapshot->fresh()->price_snapshot);
        $this->assertDatabaseHas('resident_subscription_services',['resident_subscription_id'=>$snapshot->id,'included_quantity'=>4]);
        $invalid = $data;
        $invalid['discount_value']='1001';
        $this->put('/admin/packages/'.$package->id,$invalid)->assertSessionHasErrors('discount_value');
        $this->assertSame('900.00',$package->fresh()->price);
        $invalid = $data;
        $invalid['services'][]=$data['services'][0];
        $this->put('/admin/packages/'.$package->id,$invalid)->assertSessionHasErrors('services.0.service_id');
        $this->get('/admin/packages/999999/edit')->assertNotFound();
    }

    /** @return void */
    public function test_service_images_discounts_and_public_api_fields(): void
    {
        $admin=$this->admin();
        Storage::fake('public');
        $service=Service::firstOrFail();
        $data=['code'=>$service->code,'service_category_id'=>$service->service_category_id,
            'base_price'=>'200.00','original_price'=>'300.00','discount_type'=>'fixed','discount_value'=>'25.50',
            'is_active'=>1,'name_ar'=>'خدمة جديدة','name_en'=>'New service','name_ur'=>'نئی خدمت',
            'image'=>UploadedFile::fake()->image('service.png')];
        $this->actingAs($admin)->put('/admin/packages/services/'.$service->id,$data)->assertSessionHasNoErrors();
        $service->refresh();
        $this->assertSame('174.50',$service->base_price);
        Storage::disk('public')->assertExists($service->image_path);
        $resident=User::create(['name'=>'Resident','email'=>'catalog-resident@example.test','password'=>'StrongPassword123!',
            'role_id'=>Role::where('code','resident')->value('id'),'locale'=>'en']);
        Sanctum::actingAs($resident);
        $entry=collect($this->getJson('/api/v1/resident/services')->assertOk()->json('data'))->firstWhere('id',$service->id);
        $this->assertEquals(174.5,$entry['price']);
        $this->assertSame('200.00',$entry['price_before_discount']);
        $this->assertSame('fixed',$entry['discount_type']);
        $this->assertNotEmpty($entry['image_url']);
        $this->assertArrayNotHasKey('original_price',$entry);
        $this->assertArrayNotHasKey('original_price',$service->toArray());
        $this->actingAs($resident)->put('/admin/packages/services/'.$service->id,$data)->assertForbidden();
    }

    /** @return void */
    public function test_package_image_upload_and_invalid_file_validation(): void
    {
        $admin=$this->admin();
        Storage::fake('public');
        $data=$this->packageInput();
        $data['image']=UploadedFile::fake()->image('plan.jpg');
        $this->actingAs($admin)->post('/admin/packages',$data)->assertSessionHasNoErrors();
        $package=ServicePackage::where('code',$data['code'])->firstOrFail();
        Storage::disk('public')->assertExists($package->image_path);
        $data['image']=UploadedFile::fake()->create('unsafe.svg',1,'image/svg+xml');
        $this->put('/admin/packages/'.$package->id,$data)->assertSessionHasErrors('image');
        unset($data['image']);
        $data['remove_image']=1;
        $this->put('/admin/packages/'.$package->id,$data)->assertSessionHasNoErrors();
        $this->assertNull($package->fresh()->image_path);
        $resident=User::create(['name'=>'Resident','email'=>'unauthorized-plan@example.test','password'=>'StrongPassword123!',
            'role_id'=>Role::where('code','resident')->value('id'),'locale'=>'en']);
        $this->actingAs($resident)->get('/admin/packages/'.$package->id.'/edit')->assertForbidden();
        $this->put('/admin/packages/'.$package->id,$data)->assertForbidden();
    }

    /** @return void */
    public function test_comparison_definitions_are_editable_id_based_and_localized(): void
    {
        $admin = $this->admin();
        $feature = ['code'=>'report-frequency','name_ar'=>'التقارير','name_en'=>'Reports','name_ur'=>'رپورٹس','sort_order'=>1,'is_active'=>1];
        $this->actingAs($admin)->post('/admin/package-features',$feature)->assertSessionHasNoErrors();
        $definition = \App\Modules\Catalog\Models\PackageFeature::where('code',$feature['code'])->firstOrFail();
        $data = $this->packageInput();
        $data['features'] = [['package_feature_id'=>$definition->id,'availability'=>'included',
            'value_ar'=>'أسبوعياً','value_en'=>'Weekly','value_ur'=>'ہفتہ وار']];
        $this->post('/admin/packages',$data)->assertSessionHasNoErrors();
        $package = ServicePackage::where('code',$data['code'])->firstOrFail();
        $this->get('/admin/package-features')->assertOk()->assertSee('report-frequency');
        $this->get('/admin/packages/'.$package->id.'/edit')->assertOk()->assertSee('Weekly');
        app()->setLocale('en');
        $entry = app(ApiResidentPackageService::class)->index([], null)['data']->firstWhere('id',$package->id);
        $this->assertSame('Weekly',$entry['features'][0]['value']);
        $this->assertSame($definition->id,$entry['features'][0]['id']);
        $this->assertSame('included',$entry['features'][0]['availability']);
        $data['features'][0]['availability']='addon';
        $data['features'][0]['value_en']='Optional weekly report';
        $this->put('/admin/packages/'.$package->id,$data)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('package_feature_values',[
            'service_package_id'=>$package->id,'package_feature_id'=>$definition->id,
            'availability'=>'addon','value_en'=>'Optional weekly report',
        ]);
        $invalid = $data;
        $invalid['features'][0]['package_feature_id']=999999;
        $this->put('/admin/packages/'.$package->id,$invalid)->assertSessionHasErrors('features.0.package_feature_id');
        $invalid = $data;
        $invalid['features'][]=$invalid['features'][0];
        $this->put('/admin/packages/'.$package->id,$invalid)->assertSessionHasErrors('features.0.package_feature_id');
        $invalid = $data;
        $invalid['features'][0]['availability']='arbitrary';
        $this->put('/admin/packages/'.$package->id,$invalid)->assertSessionHasErrors('features.0.availability');
        $feature['name_en']='Maintenance reports';
        $feature['is_active']=0;
        $this->put('/admin/package-features/'.$definition->id,$feature)->assertSessionHasNoErrors();
        $this->assertSame('Maintenance reports',$definition->fresh()->name_en);
        $entry = app(ApiResidentPackageService::class)->index([],null)['data']->firstWhere('id',$package->id);
        $this->assertCount(0,$entry['features']);
        $this->assertDatabaseHas('package_feature_values',['package_feature_id'=>$definition->id]);
        $resident=User::create(['name'=>'Resident','email'=>'feature-resident@example.test','password'=>'StrongPassword123!',
            'role_id'=>Role::where('code','resident')->value('id'),'locale'=>'en']);
        $this->actingAs($resident)->post('/admin/package-features',$feature)->assertForbidden();
        $this->put('/admin/package-features/'.$definition->id,$feature)->assertForbidden();
    }
}
