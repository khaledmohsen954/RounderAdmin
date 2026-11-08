<?php

namespace Tests\Feature;

use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use App\Modules\Properties\Models\Home;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserPropertyApiTest extends TestCase
{
    use DatabaseTransactions;

    private User $resident;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $role = Role::create(['code' => 'resident']);
        $this->resident = User::create([
            'role_id' => $role->id,
            'name' => 'محمد السعيد',
            'phone' => '0509876543',
            'locale' => 'ar',
            'password' => 'secret123',
        ]);

        DB::table('cities')->insert(['id' => 1, 'code' => 'RUH', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('city_translations')->insert([
            'city_id' => 1,
            'locale' => 'ar',
            'name' => 'الرياض',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_user_can_add_property_and_be_linked_to_it(): void
    {
        Sanctum::actingAs($this->resident);

        $response = $this->postJson('/api/v1/resident/properties', [
            'name' => 'برج النرجس',
            'address' => 'طريق الملك سلمان، حي النرجس',
            'district' => 'النرجس',
            'city_id' => 1,
            'residence_type' => 'apartment',
            'unit_number' => '104',
            'floor_number' => '2',
            'area_sqm' => 150.0,
            'ownership_type' => 'owner',
            'is_primary' => true,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'data' => [
                    'name' => 'برج النرجس',
                    'address' => 'طريق الملك سلمان، حي النرجس',
                    'district' => 'النرجس',
                    'city' => 'الرياض',
                    'unit_label' => '104',
                    'unit_number' => '104',
                    'residence_type' => 'apartment',
                    'residence_type_label' => 'شقة',
                    'floor_number' => '2',
                    'area_sqm' => 150.0,
                    'ownership_type' => 'owner',
                    'ownership_type_label' => 'مالك',
                    'is_primary' => true,
                ]
            ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data['property_code']);
        $this->assertNotEmpty($data['home_id']);
        $this->assertNotEmpty($data['resident_home_id']);

        // التحقق من إنشاء العقار وربطه بالمستخدم
        $this->assertDatabaseHas('homes', [
            'id' => $data['home_id'],
            'name' => 'برج النرجس',
            'address' => 'طريق الملك سلمان، حي النرجس',
        ]);

        $this->assertDatabaseHas('resident_homes', [
            'user_id' => $this->resident->id,
            'home_id' => $data['home_id'],
            'unit_label' => '104',
            'residence_type' => 'apartment',
            'is_primary' => true,
        ]);
    }

    public function test_user_can_add_villa_without_explicit_unit_number(): void
    {
        Sanctum::actingAs($this->resident);

        $response = $this->postJson('/api/v1/resident/properties', [
            'property_name' => 'فيلا الياسمين',
            'address' => 'حي الياسمين، شارع أنس بن مالك',
            'district' => 'الياسمين',
            'city_id' => 1,
            'residence_type' => 'villa',
            'area_sqm' => 420.0,
            'ownership_type' => 'owner',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'data' => [
                    'name' => 'فيلا الياسمين',
                    'residence_type' => 'villa',
                    'residence_type_label' => 'فيلا',
                    'unit_label' => 'الفيلا',
                    'is_primary' => true,
                ]
            ]);

        $homeId = $response->json('data.home_id');

        $this->assertDatabaseHas('resident_homes', [
            'user_id' => $this->resident->id,
            'home_id' => $homeId,
            'unit_label' => 'الفيلا',
            'residence_type' => 'villa',
        ]);
    }

    public function test_user_can_add_property_via_v1_properties_route(): void
    {
        Sanctum::actingAs($this->resident);

        $response = $this->postJson('/api/v1/properties', [
            'name' => 'عقار الشفا',
            'address' => 'حي الشفا، الرياض',
            'city_id' => 1,
            'residence_type' => 'floor',
            'unit_number' => 'F-1',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'data' => [
                    'name' => 'عقار الشفا',
                    'unit_label' => 'F-1',
                    'residence_type' => 'floor',
                    'residence_type_label' => 'دور',
                ]
            ]);
    }

    public function test_user_can_link_existing_property_by_code(): void
    {
        Sanctum::actingAs($this->resident);

        // إنشاء عقار مسبق بكود
        $home = Home::create([
            'city_id' => 1,
            'name' => 'عمارة الأمل',
            'address' => 'حي العليا، الرياض',
            'district' => 'العليا',
            'property_code' => 'PR-EXISTING1',
        ]);

        $response = $this->postJson('/api/v1/resident/properties', [
            'property_code' => 'PR-EXISTING1',
            'unit_number' => '12',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('resident_homes', [
            'user_id' => $this->resident->id,
            'home_id' => $home->id,
            'unit_label' => '12',
            'is_primary' => true,
        ]);
    }

    public function test_user_can_list_their_properties(): void
    {
        Sanctum::actingAs($this->resident);

        $this->postJson('/api/v1/resident/properties', [
            'name' => 'عقار 1',
            'address' => 'شارع 1',
            'city_id' => 1,
            'unit_number' => '1',
        ]);

        $this->postJson('/api/v1/resident/properties', [
            'name' => 'عقار 2',
            'address' => 'شارع 2',
            'city_id' => 1,
            'unit_number' => '2',
        ]);

        $response = $this->getJson('/api/v1/resident/properties');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'home_id',
                        'property_id',
                        'name',
                        'property_name',
                        'property_code',
                        'unit_label',
                        'residence_type',
                        'is_primary',
                    ]
                ]
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->postJson('/api/v1/resident/properties', [
            'name' => 'عقار بدون تسجيل',
            'address' => 'شارع غير معروف',
        ]);

        $response->assertStatus(401);
    }
}
