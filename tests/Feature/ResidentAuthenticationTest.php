<?php

namespace Tests\Feature;

use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ResidentAuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    public function test_resident_registration_with_mobile_hashes_password_and_issues_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test Resident',
            'mobile' => '0512345678',
            'password' => 'RounderPass123',
            'password_confirmation' => 'RounderPass123',
            'locale' => 'ar',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user.phone', '0512345678')
            ->assertJsonPath('data.user.mobile', '0512345678')
            ->assertJsonStructure(['data' => ['user' => ['id', 'name', 'phone', 'mobile', 'locale'], 'token']]);

        $user = User::where('phone', '0512345678')->firstOrFail();
        $this->assertNotSame('RounderPass123', $user->password);
        $this->assertSame('resident', $user->role->code);
    }

    public function test_registration_rejects_invalid_mobile_not_starting_with_05(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Bad Mobile Resident',
            'mobile' => '0612345678',
            'password' => 'RounderPass123',
            'password_confirmation' => 'RounderPass123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['mobile']);
    }

    public function test_registration_rejects_invalid_mobile_length(): void
    {
        // 9 chars
        $responseShort = $this->postJson('/api/v1/auth/register', [
            'name' => 'Short Mobile',
            'mobile' => '051234567',
            'password' => 'RounderPass123',
            'password_confirmation' => 'RounderPass123',
        ]);
        $responseShort->assertUnprocessable()->assertJsonValidationErrors(['mobile']);

        // 11 chars
        $responseLong = $this->postJson('/api/v1/auth/register', [
            'name' => 'Long Mobile',
            'mobile' => '05123456789',
            'password' => 'RounderPass123',
            'password_confirmation' => 'RounderPass123',
        ]);
        $responseLong->assertUnprocessable()->assertJsonValidationErrors(['mobile']);
    }

    public function test_registration_rejects_weak_password_and_duplicate_mobile(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Test Resident',
            'mobile' => '0511112222',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ])->assertUnprocessable();

        $residentRole = Role::where('code', 'resident')->firstOrFail();
        User::create([
            'name' => 'Existing',
            'phone' => '0511112222',
            'password' => 'RounderPass123',
            'role_id' => $residentRole->id,
            'locale' => 'en',
        ]);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Another',
            'mobile' => '0511112222',
            'password' => 'RounderPass123',
            'password_confirmation' => 'RounderPass123',
        ])->assertUnprocessable()->assertJsonValidationErrors(['mobile']);
    }

    public function test_resident_login_with_mobile_succeeds_and_issues_token(): void
    {
        $residentRole = Role::where('code', 'resident')->firstOrFail();
        User::create([
            'name' => 'Login Resident',
            'phone' => '0555556666',
            'password' => 'RounderPass123',
            'role_id' => $residentRole->id,
            'locale' => 'ar',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'mobile' => '0555556666',
            'password' => 'RounderPass123',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.phone', '0555556666')
            ->assertJsonPath('data.user.mobile', '0555556666')
            ->assertJsonStructure(['data' => ['user' => ['id', 'name', 'phone', 'mobile'], 'token']]);
    }

    public function test_login_rejects_invalid_mobile_format_and_wrong_password(): void
    {
        // Invalid mobile format (not starting with 05)
        $this->postJson('/api/v1/auth/login', [
            'mobile' => '0655556666',
            'password' => 'RounderPass123',
        ])->assertUnprocessable()->assertJsonValidationErrors(['mobile']);

        // Wrong password
        $residentRole = Role::where('code', 'resident')->firstOrFail();
        User::create([
            'name' => 'Wrong Password Resident',
            'phone' => '0599998888',
            'password' => 'RounderPass123',
            'role_id' => $residentRole->id,
            'locale' => 'ar',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'mobile' => '0599998888',
            'password' => 'WrongPass999',
        ])->assertUnprocessable()->assertJsonValidationErrors(['mobile']);
    }

    public function test_registration_and_login_work_with_phone_alias(): void
    {
        $regResponse = $this->postJson('/api/v1/auth/register', [
            'name' => 'Phone Alias Resident',
            'phone' => '0533334444',
            'password' => 'RounderPass123',
            'password_confirmation' => 'RounderPass123',
        ]);

        $regResponse->assertCreated()
            ->assertJsonPath('data.user.phone', '0533334444');

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'phone' => '0533334444',
            'password' => 'RounderPass123',
        ]);

        $loginResponse->assertOk()
            ->assertJsonPath('data.user.phone', '0533334444');
    }

    public function test_validate_mobile_checks_format_and_registration_status(): void
    {
        // Unregistered mobile
        $respNew = $this->postJson('/api/v1/auth/validate-mobile', [
            'mobile' => '0544445555',
        ]);
        $respNew->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.is_registered', false)
            ->assertJsonPath('data.formatted_mobile', '+966544445555');

        // Registered user
        $role = Role::where('code', 'resident')->firstOrFail();
        User::create([
            'name' => 'Registered User',
            'phone' => '0544445555',
            'password' => 'RounderPass123',
            'role_id' => $role->id,
        ]);

        $respExist = $this->postJson('/api/v1/auth/validate-mobile', [
            'mobile' => '0544445555',
        ]);
        $respExist->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.is_registered', true)
            ->assertJsonPath('data.user.name', 'Registered User');

        // Invalid mobile
        $this->postJson('/api/v1/auth/validate-mobile', [
            'mobile' => '0612345678',
        ])->assertUnprocessable()->assertJsonValidationErrors(['mobile']);
    }

    public function test_send_and_verify_otp_flow_for_existing_and_new_users(): void
    {
        // 1. Send OTP
        $sendResp = $this->postJson('/api/v1/auth/send-otp', [
            'mobile' => '0566667777',
        ]);
        $sendResp->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.expires_in_seconds', 180);

        $otp = $sendResp->json('data.otp') ?? '123456';

        // 2. Verify OTP for unregistered user
        $verifyRespNew = $this->postJson('/api/v1/auth/verify-otp', [
            'mobile' => '0566667777',
            'otp' => $otp,
        ]);
        $verifyRespNew->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.is_registered', false);

        // 3. Register user
        $this->postJson('/api/v1/auth/register', [
            'name' => 'New Resident Via OTP',
            'mobile' => '0566667777',
            'password' => 'RounderPass123',
            'password_confirmation' => 'RounderPass123',
        ])->assertCreated();

        // 4. Send OTP again for registered user
        $sendRespExisting = $this->postJson('/api/v1/auth/send-otp', [
            'mobile' => '0566667777',
        ])->assertOk();

        // 5. Verify OTP for registered user -> logs in and issues token
        $verifyRespExist = $this->postJson('/api/v1/auth/verify-otp', [
            'mobile' => '0566667777',
            'otp' => $sendRespExisting->json('data.otp'),
        ]);
        $verifyRespExist->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.is_registered', true)
            ->assertJsonStructure(['data' => ['user' => ['id', 'name', 'phone'], 'token']]);
    }

    public function test_send_otp_returns_server_error_and_does_not_cache_code_when_sms_fails(): void
    {
        config(['app.sms_url' => null]);
        Cache::forget('otp_0577778888');
        Cache::forget('otp_+966577778888');

        $this->postJson('/api/v1/auth/send-otp', [
            'mobile' => '0577778888',
        ])->assertStatus(500)
            ->assertJsonMissingPath('data.otp');

        $this->assertNull(Cache::get('otp_0577778888'));
        $this->assertNull(Cache::get('otp_+966577778888'));
    }
}
