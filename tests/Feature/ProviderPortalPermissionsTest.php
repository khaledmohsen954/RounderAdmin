<?php

namespace Tests\Feature;

use App\Modules\Catalog\Models\Service;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Identity\Enums\RoleCode;
use App\Modules\Identity\Models\User;
use App\Modules\Providers\Enums\ContractStatusCode;
use App\Modules\Providers\Models\ProviderContractStatus;
use App\Modules\Properties\Models\ResidentHome;
use App\Modules\Providers\Services\ProviderPortalService;
use App\Modules\Providers\Services\ProviderRegistrationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProviderPortalPermissionsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_provider_staff_are_standard_provider_users_with_scoped_permissions(): void
    {
        $this->seed();
        $service = Service::query()->where('is_active', true)->firstOrFail();
        $profile = app(ProviderRegistrationService::class)->register([
            'company_name' => 'شركة الصيانة',
            'trade_name' => 'فريق الصيانة',
            'provider_type' => 'شركة صيانة',
            'years_of_experience' => 5,
            'service_ids' => [$service->id],
            'phone' => '0557654321',
            'password' => 'StrongPassword123!',
        ]);
        $profile->update(['is_approved' => true, 'contract_accepted_at' => now()]);
        $owner = $profile->user;
        $portal = app(ProviderPortalService::class);

        $this->assertSame(RoleCode::Provider->value, $owner->role->code);
        $this->assertTrue($portal->allows($owner, $profile, 'orders.view'));
        $this->assertFalse(Schema::hasTable('provider_team_members'));
        $this->actingAs($owner, 'provider')->get('/provider/orders')->assertOk();
        $this->get('/provider/prices')->assertOk();
        $this->get('/provider/emergencies')->assertOk();
        $this->get('/provider/finance')->assertOk();
        $this->get('/provider/ratings')->assertOk();
        $this->get('/provider/settings')->assertOk();
        $profile->update(['contract_accepted_at' => null]);
        $this->get('/provider/contract')->assertOk();
        $profile->update(['contract_accepted_at' => now()]);
        $teamResponse = $this->get('/provider/team')->assertOk();
        $teamResponse->assertSee(__('Primary account'))
            ->assertDontSee(route('provider.team.edit', $owner->id))
            ->assertDontSee(route('provider.team.destroy', $owner->id));
        $this->get(route('provider.team.edit', $owner->id))->assertForbidden();
        $this->withSession(['_token' => 'provider-team-test-token'])
            ->withHeader('X-CSRF-TOKEN', 'provider-team-test-token')
            ->put(route('provider.team.update', $owner->id), [])->assertForbidden();
        $this->withSession(['_token' => 'provider-team-test-token'])
            ->withHeader('X-CSRF-TOKEN', 'provider-team-test-token')
            ->delete(route('provider.team.destroy', $owner->id))->assertForbidden();
        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
            'provider_profile_id' => $profile->id,
        ]);

        $staffId = $portal->saveTeamMember($profile, $owner, [
            'name' => 'موظف الطلبات',
            'email' => 'provider-orders-staff@example.test',
            'phone' => '0557654322',
            'locale' => 'ar',
            'password' => 'StaffPassword123!',
            'permissions' => ['dashboard.view'],
        ]);
        $staff = User::query()->with('role')->findOrFail($staffId);

        $this->assertSame(RoleCode::Provider->value, $staff->role->code);
        $this->assertSame($profile->id, $staff->provider_profile_id);
        $this->assertSame(['dashboard.view'], $staff->permissions);

        $this->actingAs($staff, 'provider')->get('/provider/dashboard')
            ->assertOk()->assertDontSee(__('Service orders'));
        $this->get('/provider/orders')->assertForbidden();
        $this->get('/provider/prices')->assertForbidden();
        $this->get('/provider/emergencies')->assertForbidden();
        $this->get('/provider/team')->assertForbidden();
    }

    public function test_provider_created_order_uses_the_shared_booking_status_cycle(): void
    {
        $this->seed();
        $service = Service::query()->where('is_active', true)->firstOrFail();
        $profile = app(ProviderRegistrationService::class)->register([
            'company_name' => 'شركة الصيانة',
            'trade_name' => 'فريق الصيانة',
            'provider_type' => 'شركة صيانة',
            'years_of_experience' => 5,
            'service_ids' => [$service->id],
            'phone' => '0557654331',
            'password' => 'StrongPassword123!',
        ]);
        $profile->update(['is_approved' => true, 'contract_accepted_at' => now()]);
        $contract = $profile->contracts()->firstOrFail();
        $contract->update([
            'status_id' => ProviderContractStatus::query()->where('code', ContractStatusCode::Active->value)->value('id'),
            'starts_on' => today(),
            'terms' => 'Provider contract terms.',
        ]);

        $resident = User::query()->create([
            'name' => 'Test resident',
            'email' => 'provider-order-resident@example.test',
            'password' => 'ResidentPassword123!',
            'locale' => 'ar',
            'role_id' => DB::table('roles')->where('code', RoleCode::Resident->value)->value('id'),
        ]);
        $homeId = DB::table('homes')->insertGetId([
            'city_id' => DB::table('cities')->value('id'),
            'name' => 'Provider order home',
            'address' => 'Test address',
            'property_code' => 'PROVIDER-ORDER-TEST',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $membership = ResidentHome::query()->create([
            'user_id' => $resident->id,
            'home_id' => $homeId,
            'unit_label' => '12',
        ]);

        $subServiceId = DB::table('sub_services')->insertGetId([
            'service_id' => $service->id,
            'code' => 'PROVIDER-ORDER-SUB-' . uniqid(),
            'price_eastern' => 185.50,
            'price_qassim_madinah_kharj' => 185.50,
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('sub_service_translations')->insert([
            'sub_service_id' => $subServiceId,
            'locale' => 'ar',
            'name' => 'خدمة مزود فرعية للاختبار',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $portal = app(ProviderPortalService::class);
        $this->actingAs($profile->user, 'provider')->get('/provider/orders/create')
            ->assertOk()->assertSee('خدمة مزود فرعية للاختبار')->assertSee('185.5');
        $booking = $portal->createOrder($profile, $profile->user, [
            'resident_home_id' => $membership->id,
            'service_id' => $service->id,
            'sub_service_id' => $subServiceId,
            'scheduled_at' => now()->addDay(),
            'notes' => 'Created by provider.',
        ]);
        $this->assertSame('pending', $booking->fresh()->status->code);
        $this->assertSame($subServiceId, $booking->fresh()->sub_service_id);
        $this->assertNull($booking->fresh()->sub_service_price_id);
        $this->assertSame('185.50', $booking->fresh()->total_amount);
        try {
            $portal->removeService($profile, $service->id);
            $this->fail('A service with a pending order must remain linked.');
        } catch (ValidationException) {
            $this->assertTrue($profile->services()->whereKey($service->id)->exists());
        }

        foreach (['confirmed', 'in_progress'] as $status) {
            $portal->transitionOrder($profile, $profile->user, $booking->id, ['status' => $status]);
            $this->assertSame($status, $booking->fresh()->status->code);
        }

        $portal->transitionOrder($profile, $profile->user, $booking->id, [
            'status' => 'completed',
            'gross_amount' => 250,
        ]);
        $this->assertSame('completed', $booking->fresh()->status->code);
        $this->assertDatabaseHas('provider_commission_entries', [
            'booking_id' => $booking->id,
            'provider_profile_id' => $profile->id,
        ]);

        $portal->removeService($profile, $service->id);
        $this->assertFalse($profile->services()->whereKey($service->id)->exists());
    }
}
