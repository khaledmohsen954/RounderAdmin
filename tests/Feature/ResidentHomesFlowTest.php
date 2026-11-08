<?php

namespace Tests\Feature;

use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ResidentHomesFlowTest extends TestCase
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
            'name' => 'خالد بن فهد',
            'phone' => '0501234567',
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

    public function test_resident_can_add_unit_following_figma_flow(): void
    {
        Sanctum::actingAs($this->resident);

        $response = $this->postJson('/api/v1/resident/homes/add-unit', [
            'address' => 'حي النرجس، شارع أنس بن مالك',
            'district' => 'النرجس',
            'city_id' => 1,
            'residence_type' => 'apartment',
            'unit_number' => 'A-102',
            'floor_number' => '1',
            'custom_name' => 'شقتي',
            'area_sqm' => 135.5,
            'ownership_type' => 'owner',
            'is_primary' => true,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => true,
                'data' => [
                    'address' => 'حي النرجس، شارع أنس بن مالك',
                    'district' => 'النرجس',
                    'unit_label' => 'A-102',
                    'residence_type' => 'apartment',
                    'residence_type_label' => 'شقة',
                    'custom_name' => 'شقتي',
                    'area_sqm' => 135.5,
                    'ownership_type' => 'owner',
                    'ownership_type_label' => 'مالك',
                    'is_primary' => true,
                ]
            ]);

        $this->assertDatabaseHas('resident_homes', [
            'user_id' => $this->resident->id,
            'unit_label' => 'A-102',
            'residence_type' => 'apartment',
            'ownership_type' => 'owner',
            'is_primary' => true,
        ]);
    }

    public function test_resident_can_list_homes_with_figma_details(): void
    {
        Sanctum::actingAs($this->resident);

        $this->postJson('/api/v1/resident/homes/add-unit', [
            'address' => 'حي الملقا',
            'district' => 'الملقا',
            'city_id' => 1,
            'residence_type' => 'villa',
            'unit_number' => 'V-12',
            'floor_number' => '2',
            'custom_name' => 'فيلا الملقا',
            'area_sqm' => 450,
            'ownership_type' => 'owner',
            'is_primary' => true,
        ]);

        $response = $this->getJson('/api/v1/resident/homes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'resident_home_id',
                        'name',
                        'custom_name',
                        'unit_label',
                        'unit_number',
                        'residence_type',
                        'residence_type_label',
                        'floor_number',
                        'area_sqm',
                        'ownership_type',
                        'ownership_type_label',
                        'is_primary',
                        'address',
                        'district',
                        'city',
                        'property_code',
                    ]
                ]
            ]);

        $home = $response->json('data.0');
        $this->assertEquals('فيلا الملقا', $home['custom_name']);
        $this->assertEquals('villa', $home['residence_type']);
        $this->assertEquals('فيلا', $home['residence_type_label']);
        $this->assertEquals('V-12', $home['unit_label']);
    }

    public function test_resident_can_switch_primary_home(): void
    {
        Sanctum::actingAs($this->resident);

        // Unit 1
        $unit1 = $this->postJson('/api/v1/resident/homes/add-unit', [
            'address' => 'حي النرجس',
            'district' => 'النرجس',
            'city_id' => 1,
            'residence_type' => 'apartment',
            'unit_number' => '101',
            'custom_name' => 'شقة 1',
            'is_primary' => true,
        ])->json('data');

        // Unit 2
        $unit2 = $this->postJson('/api/v1/resident/homes/add-unit', [
            'address' => 'حي الياسمين',
            'district' => 'الياسمين',
            'city_id' => 1,
            'residence_type' => 'apartment',
            'unit_number' => '202',
            'custom_name' => 'شقة 2',
            'is_primary' => false,
        ])->json('data');

        // Switch to unit 2
        $switchResponse = $this->postJson('/api/v1/resident/homes/set-primary', [
            'resident_home_id' => $unit2['id'],
        ]);

        $switchResponse->assertStatus(200)
            ->assertJson([
                'status' => true,
                'data' => [
                    'resident_home_id' => $unit2['id'],
                    'is_primary' => true,
                ]
            ]);

        // Check dashboard shows unit 2
        $dashResponse = $this->getJson('/api/v1/resident/dashboard');
        $dashResponse->assertStatus(200);
        $this->assertEquals($unit2['home_id'], $dashResponse->json('data.home_id'));
    }
}
