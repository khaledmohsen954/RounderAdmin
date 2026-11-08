<?php

namespace Tests\Feature;

use App\Modules\Bookings\Models\Booking;
use App\Modules\Bookings\Models\BookingStatus;
use App\Modules\Catalog\Models\Service;
use App\Modules\Catalog\Models\ServiceCategory;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use App\Modules\Providers\Enums\ContractStatusCode;
use App\Modules\Providers\Models\ProviderContract;
use App\Modules\Providers\Models\ProviderContractStatus;
use App\Modules\Providers\Models\ProviderProfile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ResidentBookingServiceProviderTest extends TestCase
{
    use DatabaseTransactions;

    private User $resident;
    private int $homeId;
    private Service $service;
    private ProviderProfile $approvedProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $residentRole = Role::where('code', 'resident')->firstOrFail();
        $this->resident = User::create([
            'role_id' => $residentRole->id,
            'name' => 'Resident Tester',
            'phone' => '0501112233',
            'locale' => 'ar',
            'password' => 'Password123!',
        ]);

        $cityId = DB::table('cities')->insertGetId(['code' => 'RUH', 'created_at' => now(), 'updated_at' => now()]);
        $this->homeId = DB::table('homes')->insertGetId([
            'city_id' => $cityId,
            'name' => 'Al-Nakheel Home',
            'address' => 'Riyadh, Al-Nakheel',
            'property_code' => 'PROP-NAKHEEL',
            'health_percent' => 100,
            'association_balance' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('resident_homes')->insert([
            'user_id' => $this->resident->id,
            'home_id' => $this->homeId,
            'unit_label' => 'U-101',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $category = ServiceCategory::create(['code' => 'plumbing']);
        $this->service = Service::create([
            'service_category_id' => $category->id,
            'code' => 'pipe_repair',
            'base_price' => 150.00,
            'is_active' => true,
        ]);

        $providerRole = Role::where('code', 'provider')->firstOrFail();
        $providerUser = User::create([
            'role_id' => $providerRole->id,
            'name' => 'Provider User',
            'phone' => '0555998877',
            'locale' => 'ar',
            'password' => 'Password123!',
        ]);

        $this->approvedProvider = ProviderProfile::create([
            'user_id' => $providerUser->id,
            'company_name' => 'شركة الصيانة السريعة',
            'is_approved' => true,
        ]);

        $contractStatus = ProviderContractStatus::firstOrCreate(
            ['code' => ContractStatusCode::Active->value]
        );

        ProviderContract::create([
            'provider_profile_id' => $this->approvedProvider->id,
            'reference' => 'CNT-TEST-001',
            'status_id' => $contractStatus->id,
            'service_commission_percent' => 10.00,
            'emergency_commission_percent' => 15.00,
            'starts_on' => now()->subDay()->toDateString(),
            'terms' => 'Approved terms',
        ]);
    }

    public function test_resident_can_fetch_approved_service_providers(): void
    {
        Sanctum::actingAs($this->resident);

        // An unapproved provider should not show up
        $unapprovedUser = User::create([
            'role_id' => Role::where('code', 'provider')->value('id'),
            'name' => 'Pending Provider',
            'phone' => '0599001122',
            'password' => 'Password123!',
        ]);
        ProviderProfile::create([
            'user_id' => $unapprovedUser->id,
            'company_name' => 'شركة غير معتمدة',
            'is_approved' => false,
        ]);

        $response = $this->getJson('/api/v1/resident/service-providers');

        $response->assertOk()
            ->assertJsonPath('data.0.id', $this->approvedProvider->id)
            ->assertJsonPath('data.0.company_name', 'شركة الصيانة السريعة')
            ->assertJsonMissing(['company_name' => 'شركة غير معتمدة']);
    }

    public function test_service_providers_can_be_filtered_by_service_id(): void
    {
        Sanctum::actingAs($this->resident);

        $unrelatedUser = User::create([
            'role_id' => Role::where('code', 'provider')->value('id'),
            'name' => 'Unrelated Provider',
            'phone' => '0555111222',
            'password' => 'Password123!',
        ]);
        $unrelatedProvider = ProviderProfile::create([
            'user_id' => $unrelatedUser->id,
            'company_name' => 'شركة لخدمة أخرى',
            'is_approved' => true,
        ]);
        $otherService = Service::create([
            'service_category_id' => $this->service->service_category_id,
            'code' => 'electrical_work',
            'base_price' => 200.00,
            'is_active' => true,
        ]);
        $unrelatedProvider->services()->attach($otherService->id);

        $unapprovedUser = User::create([
            'role_id' => Role::where('code', 'provider')->value('id'),
            'name' => 'Unapproved Provider',
            'phone' => '0555222333',
            'password' => 'Password123!',
        ]);
        $unapprovedProvider = ProviderProfile::create([
            'user_id' => $unapprovedUser->id,
            'company_name' => 'شركة غير معتمدة لهذه الخدمة',
            'is_approved' => false,
        ]);

        $this->approvedProvider->services()->attach($this->service->id);
        $unapprovedProvider->services()->attach($this->service->id);

        $this->getJson('/api/v1/resident/service-providers?service_id='.$this->service->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->approvedProvider->id);

        $this->getJson('/api/v1/resident/service-providers?service_id=999999')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['service_id']);
    }

    public function test_resident_can_create_booking_with_service_provider_id(): void
    {
        Sanctum::actingAs($this->resident);

        $scheduledAt = now()->addDays(2)->format('Y-m-d H:i:s');

        $response = $this->postJson('/api/v1/resident/bookings', [
            'home_id' => $this->homeId,
            'service_id' => $this->service->id,
            'service_provider_id' => $this->approvedProvider->id,
            'scheduled_at' => $scheduledAt,
            'notes' => 'Please arrive on time',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.service_provider_id', $this->approvedProvider->id)
            ->assertJsonPath('data.provider_id', $this->approvedProvider->user_id)
            ->assertJsonPath('data.service_provider.company_name', 'شركة الصيانة السريعة');

        $this->assertDatabaseHas('bookings', [
            'resident_id' => $this->resident->id,
            'home_id' => $this->homeId,
            'service_id' => $this->service->id,
            'service_provider_id' => $this->approvedProvider->id,
            'provider_id' => $this->approvedProvider->user_id,
        ]);

        $this->assertDatabaseHas('booking_provider_assignments', [
            'provider_profile_id' => $this->approvedProvider->id,
        ]);
    }

    public function test_booking_creation_rejects_invalid_service_provider_id(): void
    {
        Sanctum::actingAs($this->resident);

        $response = $this->postJson('/api/v1/resident/bookings', [
            'home_id' => $this->homeId,
            'service_id' => $this->service->id,
            'service_provider_id' => 999999, // Non-existent
            'scheduled_at' => now()->addDays(2)->toIso8601String(),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['service_provider_id']);
    }

    public function test_resident_can_still_create_booking_without_service_provider(): void
    {
        Sanctum::actingAs($this->resident);

        $response = $this->postJson('/api/v1/resident/bookings', [
            'home_id' => $this->homeId,
            'service_id' => $this->service->id,
            'scheduled_at' => now()->addDays(2)->toIso8601String(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.service_provider_id', null);
    }

    public function test_resident_bookings_list_includes_service_provider_information(): void
    {
        Sanctum::actingAs($this->resident);

        $this->postJson('/api/v1/resident/bookings', [
            'home_id' => $this->homeId,
            'service_id' => $this->service->id,
            'service_provider_id' => $this->approvedProvider->id,
            'scheduled_at' => now()->addDays(2)->toIso8601String(),
        ])->assertCreated();

        $response = $this->getJson('/api/v1/resident/bookings');

        $response->assertOk()
            ->assertJsonPath('data.0.service_provider_id', $this->approvedProvider->id)
            ->assertJsonPath('data.0.service_provider_name', 'شركة الصيانة السريعة')
            ->assertJsonPath('data.0.status_id', fn ($id) => is_int($id));
    }

    public function test_resident_can_fetch_booking_statuses(): void
    {
        Sanctum::actingAs($this->resident);

        $response = $this->getJson('/api/v1/resident/booking-statuses');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'code', 'name']
                ]
            ]);

        $statuses = $response->json('data');
        $this->assertNotEmpty($statuses);
    }

    public function test_resident_can_filter_bookings_by_status_id(): void
    {
        Sanctum::actingAs($this->resident);

        $pendingStatus = BookingStatus::where('code', 'pending')->firstOrFail();
        $confirmedStatus = BookingStatus::where('code', 'confirmed')->firstOrFail();

        // Booking 1: Pending
        $booking1 = Booking::create([
            'resident_id' => $this->resident->id,
            'home_id' => $this->homeId,
            'service_id' => $this->service->id,
            'booking_status_id' => $pendingStatus->id,
            'scheduled_at' => now()->addDays(2),
            'total_amount' => 100,
        ]);

        // Booking 2: Confirmed
        $booking2 = Booking::create([
            'resident_id' => $this->resident->id,
            'home_id' => $this->homeId,
            'service_id' => $this->service->id,
            'booking_status_id' => $confirmedStatus->id,
            'scheduled_at' => now()->addDays(3),
            'total_amount' => 150,
        ]);

        // Fetch without filter (both returned)
        $this->getJson('/api/v1/resident/bookings')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        // Fetch filtered by pending status_id
        $responsePending = $this->getJson('/api/v1/resident/bookings?status_id=' . $pendingStatus->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $booking1->id)
            ->assertJsonPath('data.0.status_id', $pendingStatus->id);

        // Fetch filtered by confirmed status_id
        $responseConfirmed = $this->getJson('/api/v1/resident/bookings?status_id=' . $confirmedStatus->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $booking2->id)
            ->assertJsonPath('data.0.status_id', $confirmedStatus->id);
    }
}
