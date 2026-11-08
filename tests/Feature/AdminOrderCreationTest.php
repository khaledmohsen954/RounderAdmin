<?php
namespace Tests\Feature;

use App\Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminOrderCreationTest extends TestCase
{
    use DatabaseTransactions;

    private \App\Modules\Identity\Models\Admin $admin;
    private User $resident;
    private int $membership;
    private int $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = \App\Modules\Identity\Models\Admin::create(['name'=>'Admin','email'=>'admin@test.example','password'=>'TestPassword123!','locale'=>'ar','is_active'=>true,'is_super_admin'=>true]);
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

    private function postWithCsrf(string $url, array $data): \Illuminate\Testing\TestResponse
    {
        return $this->withSession(['_token' => 'admin-order-test-token'])
            ->withHeader('X-CSRF-TOKEN', 'admin-order-test-token')
            ->post($url, $data);
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
        $subServiceId = DB::table('sub_services')->where('service_id', $this->service)
            ->where('is_active', true)->value('id');
        $payload = [
            'resident_home_id' => $this->membership, 'service_id' => $this->service,
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'resident_id' => $this->admin->id, 'total_amount' => 1,
        ];
        if ($subServiceId) {
            $payload['sub_service_id'] = $subServiceId;
        }

        $this->actingAs($this->admin);
        $this->postWithCsrf('/admin/service-orders', $payload)->assertRedirect(route('admin.service-orders.index'));
        $order = DB::table('bookings')->where('resident_home_id', $this->membership)->firstOrFail();
        $this->assertEquals($this->resident->id, $order->resident_id);
        $this->assertEquals($this->membership, $order->resident_home_id);
        $this->assertEquals(DB::table('services')->where('id', $this->service)->value('base_price'), $order->total_amount);
        $this->assertDatabaseHas('booking_status_history', ['booking_id' => $order->id, 'admin_changed_by' => $this->admin->id]);
    }

    public function test_order_requires_and_saves_a_sub_service_for_the_selected_service(): void
    {
        $subServiceId = DB::table('sub_services')->insertGetId([
            'service_id' => $this->service,
            'code' => 'TEST-SUB-SERVICE-' . uniqid(),
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('sub_service_translations')->insert([
            'sub_service_id' => $subServiceId,
            'locale' => 'ar',
            'name' => 'خدمة فرعية للاختبار',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($this->admin)->get('/admin/service-orders/create')
            ->assertOk()->assertSee('sub_service_id')->assertSee('خدمة فرعية للاختبار');

        $payload = [
            'resident_home_id' => $this->membership,
            'service_id' => $this->service,
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ];
        $initialBookingCount = DB::table('bookings')->count();
        $this->postWithCsrf('/admin/service-orders', $payload)->assertSessionHasErrors('sub_service_id');
        $this->assertSame($initialBookingCount, DB::table('bookings')->count());

        $this->postWithCsrf('/admin/service-orders', $payload + ['sub_service_id' => $subServiceId])
            ->assertRedirect(route('admin.service-orders.index'));
        $this->assertDatabaseHas('bookings', [
            'resident_home_id' => $this->membership,
            'service_id' => $this->service,
            'sub_service_id' => $subServiceId,
        ]);
    }

    public function test_invalid_service_and_past_date_do_not_create_orders(): void
    {
        $initialBookingCount = DB::table('bookings')->count();
        DB::table('services')->where('id', $this->service)->update(['is_active' => false]);
        $this->actingAs($this->admin);
        $this->postWithCsrf('/admin/service-orders', [
            'resident_home_id' => $this->membership, 'service_id' => $this->service,
            'scheduled_at' => now()->subDay()->format('Y-m-d H:i:s'),
        ])->assertSessionHasErrors(['service_id', 'scheduled_at']);
        $this->assertSame($initialBookingCount, DB::table('bookings')->count());
    }

    public function test_emergency_creates_separate_record_and_audit_update(): void
    {
        $initialBookingCount = DB::table('bookings')->count();
        $this->actingAs($this->admin);
        $this->postWithCsrf('/admin/emergencies', [
            'resident_home_id' => $this->membership,
            'emergency_type_id' => DB::table('emergency_types')->where('is_active', true)->value('id'),
            'contact_phone' => '+966500000001', 'location_details' => 'Floor 2 unit 12',
            'description' => 'Water is leaking through the ceiling.', 'confirm_emergency' => true,
        ])->assertRedirect(route('admin.emergencies.index'));
        $this->assertSame($initialBookingCount, DB::table('bookings')->count());
        $this->assertDatabaseHas('emergency_requests', ['resident_id' => $this->resident->id, 'resident_home_id' => $this->membership]);
        $this->assertDatabaseHas('emergency_request_updates', ['admin_updated_by' => $this->admin->id]);
    }

    public function test_non_admin_cannot_create_requests(): void
    {
        $initialBookingCount = DB::table('bookings')->count();
        $initialEmergencyCount = DB::table('emergency_requests')->count();
        foreach (['/admin/service-orders', '/admin/emergencies'] as $url) {
            $this->actingAs($this->resident);
            $this->postWithCsrf($url, [])->assertForbidden();
        }
        $this->assertSame($initialBookingCount, DB::table('bookings')->count());
        $this->assertSame($initialEmergencyCount, DB::table('emergency_requests')->count());
    }
}
