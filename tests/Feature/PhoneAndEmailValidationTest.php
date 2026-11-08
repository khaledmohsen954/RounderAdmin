<?php

namespace Tests\Feature;

use App\Modules\Access\Models\PermissionSet;
use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use App\Modules\Providers\Models\ProviderProfile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PhoneAndEmailValidationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    private function superAdmin(): Admin
    {
        return Admin::create([
            'is_super_admin' => true,
            'is_active' => true,
            'name' => 'Super Admin',
            'email' => 'super@example.test',
            'phone' => '0500000001',
            'password' => 'Password123!Aa',
            'locale' => 'ar',
        ]);
    }

    public function test_admin_user_can_be_created_without_email_but_phone_is_required_saudi(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin);

        $permSet = PermissionSet::create([
            'name_ar' => 'مجموعة',
            'name_en' => 'Set',
            'permissions' => ['admin.dashboard'],
        ]);

        // 1. Missing phone fails (phone is required)
        $respMissingPhone = $this->post('/admin/users', [
            'name' => 'New Admin',
            'locale' => 'ar',
            'permission_set_id' => $permSet->id,
            'is_active' => 1,
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
        ]);
        $respMissingPhone->assertSessionHasErrors(['phone']);

        // 2. Non-05 phone fails
        $respBadPhone = $this->post('/admin/users', [
            'name' => 'New Admin',
            'phone' => '0612345678',
            'locale' => 'ar',
            'permission_set_id' => $permSet->id,
            'is_active' => 1,
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
        ]);
        $respBadPhone->assertSessionHasErrors(['phone']);

        // 3. 9 digits or 11 digits fails
        $respShortPhone = $this->post('/admin/users', [
            'name' => 'New Admin',
            'phone' => '051234567',
            'locale' => 'ar',
            'permission_set_id' => $permSet->id,
            'is_active' => 1,
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
        ]);
        $respShortPhone->assertSessionHasErrors(['phone']);

        // 4. Valid 10-digit Saudi phone without email succeeds
        $respSuccess = $this->post('/admin/users', [
            'name' => 'New Admin',
            'phone' => '0512345678',
            'email' => '', // optional/nullable
            'locale' => 'ar',
            'permission_set_id' => $permSet->id,
            'is_active' => 1,
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
        ]);
        $respSuccess->assertRedirect(route('admin.users.index'))->assertSessionHasNoErrors();

        $created = Admin::where('phone', '0512345678')->firstOrFail();
        $this->assertNull($created->email);
        $this->assertSame('0512345678', $created->phone);
    }

    public function test_provider_can_be_created_without_email_and_requires_saudi_phone(): void
    {
        $admin = $this->superAdmin();
        $this->actingAs($admin);

        // 1. Missing phone fails
        $respNoPhone = $this->post('/admin/providers', [
            'name' => 'Provider Manager',
            'company_name' => 'Acme Maintenance',
            'locale' => 'ar',
            'is_approved' => 1,
            'service_commission_percent' => '10.00',
            'emergency_commission_percent' => '15.00',
            'starts_on' => '2026-10-01',
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
        ]);
        $respNoPhone->assertSessionHasErrors(['phone']);

        // 2. Invalid phone format fails
        $respBadPhone = $this->post('/admin/providers', [
            'name' => 'Provider Manager',
            'phone' => '0112345678',
            'company_name' => 'Acme Maintenance',
            'locale' => 'ar',
            'is_approved' => 1,
            'service_commission_percent' => '10.00',
            'emergency_commission_percent' => '15.00',
            'starts_on' => '2026-10-01',
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
        ]);
        $respBadPhone->assertSessionHasErrors(['phone']);

        // 3. Valid 10-digit Saudi phone without email succeeds
        $respSuccess = $this->post('/admin/providers', [
            'name' => 'Provider Manager',
            'phone' => '0598765432',
            'email' => '', // optional/nullable
            'company_name' => 'Acme Maintenance',
            'locale' => 'ar',
            'is_approved' => 1,
            'service_commission_percent' => '10.00',
            'emergency_commission_percent' => '15.00',
            'starts_on' => '2026-10-01',
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
        ]);
        $respSuccess->assertRedirect(route('admin.providers.index'))->assertSessionHasNoErrors();

        $provider = ProviderProfile::where('company_name', 'Acme Maintenance')->firstOrFail();
        $this->assertSame('0598765432', $provider->user->phone);
        $this->assertNull($provider->user->email);
    }

    public function test_admin_can_login_with_phone_number(): void
    {
        $admin = Admin::create([
            'is_super_admin' => true,
            'is_active' => true,
            'name' => 'Phone Login Admin',
            'email' => null,
            'phone' => '0555123456',
            'password' => 'AdminPass123!Aa',
            'locale' => 'ar',
        ]);

        $response = $this->post('/admin/login', [
            'email' => '0555123456',
            'password' => 'AdminPass123!Aa',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'web');
    }

    public function test_visit_creation_requires_10_digit_saudi_phone(): void
    {
        $role = Role::where('code', 'resident')->firstOrFail();
        $resident = User::create([
            'role_id' => $role->id,
            'name' => 'Resident Test',
            'phone' => '0511223344',
            'password' => 'Secret123456',
            'locale' => 'ar',
        ]);

        $cityId = DB::table('cities')->insertGetId(['code' => 'TEST-CITY', 'created_at' => now(), 'updated_at' => now()]);
        $homeId = DB::table('homes')->insertGetId([
            'city_id' => $cityId,
            'name' => 'Home 1',
            'address' => 'Address 1',
            'property_code' => 'PROP-1',
            'health_percent' => 100,
            'association_balance' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('resident_homes')->insert([
            'user_id' => $resident->id,
            'home_id' => $homeId,
            'unit_label' => '101',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($resident);

        // 1. Missing visitor_phone fails
        $this->postJson('/api/v1/resident/visits', [
            'visitor_name' => 'Visitor 1',
            'starts_at' => now()->addDay()->toIso8601String(),
            'ends_at' => now()->addDays(2)->toIso8601String(),
        ])->assertUnprocessable()->assertJsonValidationErrors(['visitor_phone']);

        // 2. Invalid visitor_phone (not 05 or not 10 digits) fails
        $this->postJson('/api/v1/resident/visits', [
            'visitor_name' => 'Visitor 1',
            'visitor_phone' => '0612345678',
            'starts_at' => now()->addDay()->toIso8601String(),
            'ends_at' => now()->addDays(2)->toIso8601String(),
        ])->assertUnprocessable()->assertJsonValidationErrors(['visitor_phone']);

        // 3. Valid 10-digit Saudi phone succeeds
        $resp = $this->postJson('/api/v1/resident/visits', [
            'visitor_name' => 'Visitor 1',
            'visitor_phone' => '0533333333',
            'starts_at' => now()->addDay()->toIso8601String(),
            'ends_at' => now()->addDays(2)->toIso8601String(),
        ]);
        $resp->assertCreated();
    }
}
