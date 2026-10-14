<?php

namespace Tests\Feature;

use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ResidentEmergencyRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $resident;
    private int $membershipId;
    private int $emergencyTypeId;
    private int $receivedStatusId;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['code' => 'resident']);
        $this->resident = User::create([
            'role_id' => $role->id,
            'name' => 'Test Resident',
            'email' => 'resident@example.test',
            'phone' => '+966500000001',
            'locale' => 'en',
            'password' => 'StrongPassword123',
        ]);

        $cityId = DB::table('cities')->insertGetId(['code' => 'test-city', 'created_at' => now(), 'updated_at' => now()]);
        $homeId = DB::table('homes')->insertGetId([
            'city_id' => $cityId,
            'name' => 'Test residence',
            'address' => 'Test address',
            'property_code' => 'TEST-HOME-1',
            'health_percent' => 100,
            'association_balance' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->membershipId = DB::table('resident_homes')->insertGetId([
            'user_id' => $this->resident->id,
            'home_id' => $homeId,
            'unit_label' => '1204',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->emergencyTypeId = DB::table('emergency_types')->insertGetId([
            'code' => 'fire',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->receivedStatusId = DB::table('emergency_request_statuses')->insertGetId([
            'code' => 'received',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Sanctum::actingAs($this->resident);
    }

    public function test_resident_can_create_an_emergency_request_separate_from_a_service_booking(): void
    {
        $response = $this->postJson('/api/v1/resident/emergency-requests', [
            'resident_home_id' => $this->membershipId,
            'emergency_type_id' => $this->emergencyTypeId,
            'contact_phone' => '+966500000001',
            'location_details' => 'Building A, floor 3',
            'description' => 'Smoke is coming from the electrical room.',
            'confirm_emergency' => true,
        ]);

        $response->assertCreated()->assertJsonPath('data.resident_id', $this->resident->id);
        $this->assertDatabaseHas('emergency_requests', [
            'resident_id' => $this->resident->id,
            'resident_home_id' => $this->membershipId,
            'emergency_type_id' => $this->emergencyTypeId,
            'status_id' => $this->receivedStatusId,
        ]);
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_resident_cannot_create_an_emergency_request_for_another_residents_home(): void
    {
        $otherRole = Role::create(['code' => 'resident_other']);
        $other = User::create([
            'role_id' => $otherRole->id,
            'name' => 'Other Resident',
            'email' => 'other@example.test',
            'phone' => '+966500000002',
            'locale' => 'en',
            'password' => 'StrongPassword123',
        ]);
        $otherHomeId = DB::table('homes')->insertGetId([
            'city_id' => DB::table('cities')->value('id'),
            'name' => 'Other residence',
            'address' => 'Other address',
            'property_code' => 'TEST-HOME-2',
            'health_percent' => 100,
            'association_balance' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $otherMembershipId = DB::table('resident_homes')->insertGetId([
            'user_id' => $other->id,
            'home_id' => $otherHomeId,
            'unit_label' => '1',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson('/api/v1/resident/emergency-requests', [
            'resident_home_id' => $otherMembershipId,
            'emergency_type_id' => $this->emergencyTypeId,
            'contact_phone' => '+966500000001',
            'location_details' => 'Building A, floor 3',
            'description' => 'Smoke is coming from the electrical room.',
            'confirm_emergency' => true,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('emergency_requests', 0);
    }
}
