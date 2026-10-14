<?php
namespace Tests\Feature;

use App\Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminOrderCreationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $resident;
    private int $membership;
    private int $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = $this->user('admin', 'admin@test.example');
        $this->resident = $this->user('resident', 'resident@test.example');
        $home = DB::table('homes')->insertGetId([
            'city_id' => DB::table('cities')->value('id'), 'name' => 'Test building',
            'address' => 'Test address', 'property_code' => 'TEST-ORDER',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->membership = DB::table('resident_homes')->insertGetId([
            'user_id' => $this->resident->id, 'home_id' => $home, 'unit_label' => '12',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->service = DB::table('services')->where('is_active', true)->value('id');
    }

    private function user(string $role, string $email): User
    {
        return User::create(['name' => 'Test user', 'email' => $email, 'password' => 'TestPassword123!',
            'locale' => 'ar', 'role_id' => DB::table('roles')->where('code', $role)->value('id')]);
    }

    public function test_admin_forms_render_and_resident_cannot_access_them(): void
    {
        foreach (['/admin/service-orders/create', '/admin/emergencies/create'] as $url) {
            $this->actingAs($this->admin)->get($url)->assertOk();
            $this->actingAs($this->resident)->get($url)->assertForbidden();
        }
    }

    public function test_order_uses_membership_ownership_and_catalogue_price(): void
    {
        $this->actingAs($this->admin)->post('/admin/service-orders', [
            'resident_home_id' => $this->membership, 'service_id' => $this->service,
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'resident_id' => $this->admin->id, 'total_amount' => 1,
        ])->assertRedirect(route('admin.service-orders.index'));
        $order = DB::table('bookings')->first();
        $this->assertEquals($this->resident->id, $order->resident_id);
        $this->assertEquals($this->membership, $order->resident_home_id);
        $this->assertEquals(DB::table('services')->where('id', $this->service)->value('base_price'), $order->total_amount);
        $this->assertDatabaseHas('booking_status_history', ['booking_id' => $order->id, 'changed_by' => $this->admin->id]);
    }

    public function test_invalid_service_and_past_date_do_not_create_orders(): void
    {
        DB::table('services')->where('id', $this->service)->update(['is_active' => false]);
        $this->actingAs($this->admin)->post('/admin/service-orders', [
            'resident_home_id' => $this->membership, 'service_id' => $this->service,
            'scheduled_at' => now()->subDay()->format('Y-m-d H:i:s'),
        ])->assertSessionHasErrors(['service_id', 'scheduled_at']);
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_emergency_creates_separate_record_and_audit_update(): void
    {
        $this->actingAs($this->admin)->post('/admin/emergencies', [
            'resident_home_id' => $this->membership,
            'emergency_type_id' => DB::table('emergency_types')->where('is_active', true)->value('id'),
            'contact_phone' => '+966500000001', 'location_details' => 'Floor 2 unit 12',
            'description' => 'Water is leaking through the ceiling.', 'confirm_emergency' => true,
        ])->assertRedirect(route('admin.emergencies.index'));
        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseHas('emergency_requests', ['resident_id' => $this->resident->id, 'resident_home_id' => $this->membership]);
        $this->assertDatabaseHas('emergency_request_updates', ['updated_by' => $this->admin->id]);
    }

    public function test_non_admin_cannot_create_requests(): void
    {
        foreach (['/admin/service-orders', '/admin/emergencies'] as $url) {
            $this->actingAs($this->resident)->post($url, [])->assertForbidden();
        }
        $this->assertDatabaseCount('bookings', 0);
        $this->assertDatabaseCount('emergency_requests', 0);
    }
}
