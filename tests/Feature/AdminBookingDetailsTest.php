<?php

namespace Tests\Feature;

use App\Modules\Bookings\Models\Booking;
use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminBookingDetailsTest extends TestCase
{
    use DatabaseTransactions;

    private Admin $admin;
    private User $resident;
    private int $bookingId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin-details@test.example',
            'password' => 'TestPassword123!',
            'locale' => 'ar',
            'is_active' => true,
            'is_super_admin' => true,
        ]);

        $this->resident = User::create([
            'name' => 'Khaled Resident',
            'email' => 'resident-details@test.example',
            'phone' => '+966500000001',
            'password' => 'TestPassword123!',
            'locale' => 'ar',
            'role_id' => DB::table('roles')->where('code', 'resident')->value('id'),
        ]);

        $homeId = DB::table('homes')->insertGetId([
            'city_id' => DB::table('cities')->value('id'),
            'name' => 'Al-Nakheel Tower',
            'address' => 'King Fahd Road, Riyadh',
            'property_code' => 'NAKHEEL-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $residentHomeId = DB::table('resident_homes')->insertGetId([
            'user_id' => $this->resident->id,
            'home_id' => $homeId,
            'unit_label' => 'A-102',
            'floor_number' => 1,
            'area_sqm' => 150.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $serviceId = DB::table('services')->where('is_active', true)->value('id');
        $statusId = DB::table('booking_statuses')->where('code', 'pending')->value('id');

        $this->bookingId = DB::table('bookings')->insertGetId([
            'resident_id' => $this->resident->id,
            'home_id' => $homeId,
            'resident_home_id' => $residentHomeId,
            'service_id' => $serviceId,
            'booking_status_id' => $statusId,
            'scheduled_at' => now()->addDays(2),
            'total_amount' => 350.00,
            'notes' => 'Please arrive at 10 AM sharp and bring tools.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Add booking status history
        DB::table('booking_status_history')->insert([
            'booking_id' => $this->bookingId,
            'booking_status_id' => $statusId,
            'changed_by' => $this->resident->id,
            'note' => 'Initial booking submission by resident',
            'created_at' => now(),
        ]);
    }

    public function test_admin_can_view_booking_details_page_in_both_languages(): void
    {
        foreach (['ar', 'en'] as $locale) {
            $response = $this->actingAs($this->admin)
                ->withSession(['locale' => $locale])
                ->get('/admin/service-orders/' . $this->bookingId);

            $response->assertOk();
            $response->assertSee('Khaled Resident');
            $response->assertSee('Al-Nakheel Tower');
            $response->assertSee('A-102');
            $response->assertSee('350.00');
            $response->assertSee('Please arrive at 10 AM sharp and bring tools.');
        }
    }

    public function test_guest_and_resident_cannot_view_booking_details(): void
    {
        // Unauthenticated guest
        $this->get('/admin/service-orders/' . $this->bookingId)
            ->assertRedirect(route('login'));

        // Resident user
        $this->actingAs($this->resident)
            ->get('/admin/service-orders/' . $this->bookingId)
            ->assertForbidden();
    }

    public function test_bookings_route_alias_redirects_to_service_orders_show(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/bookings/' . $this->bookingId)
            ->assertRedirect(route('admin.service-orders.show', $this->bookingId));
    }

    public function test_non_existent_booking_returns_404(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/service-orders/999999')
            ->assertNotFound();
    }
}
