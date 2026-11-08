<?php

namespace Tests\Feature;

use Firebase\JWT\JWT;
use App\Modules\Identity\Enums\RoleCode;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\SocialAccount;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\SocialIdentityTokenVerifier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ResidentSocialAuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
    }

    public function test_social_login_creates_a_resident_and_issues_a_sanctum_token(): void
    {
        $this->mock(SocialIdentityTokenVerifier::class)
            ->shouldReceive('verify')
            ->once()
            ->with('google', 'verified-google-token')
            ->andReturn([
                'sub' => 'google-sub-123',
                'email' => 'resident@example.test',
                'email_verified' => true,
                'name' => 'Google Resident',
            ]);

        $response = $this->postJson('/api/v1/auth/social', [
            'provider' => 'google',
            'id_token' => 'verified-google-token',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.email', 'resident@example.test')
            ->assertJsonPath('data.user.name', 'Google Resident')
            ->assertJsonPath('data.user.phone', null)
            ->assertJsonStructure(['data' => ['user' => ['id', 'name', 'email', 'phone', 'locale'], 'token']]);

        $user = User::query()->where('email', 'resident@example.test')->firstOrFail();
        $this->assertSame(RoleCode::Resident->value, $user->role->code);
        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-sub-123',
        ]);
    }

    public function test_social_login_links_a_verified_identity_to_an_existing_resident(): void
    {
        $resident = User::query()->create([
            'role_id' => Role::query()->where('code', RoleCode::Resident->value)->value('id'),
            'name' => 'Existing Resident',
            'email' => 'existing@example.test',
            'phone' => '0555555555',
            'password' => 'ResidentPassword123',
            'locale' => 'ar',
        ]);

        $this->mock(SocialIdentityTokenVerifier::class)
            ->shouldReceive('verify')
            ->once()
            ->andReturn([
                'sub' => 'apple-sub-456',
                'email' => 'existing@example.test',
                'email_verified' => 'true',
            ]);

        $response = $this->postJson('/api/v1/auth/social', [
            'provider' => 'apple',
            'id_token' => 'verified-apple-token',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.user.id', $resident->id)
            ->assertJsonPath('data.user.phone', '0555555555');
        $this->assertSame(1, SocialAccount::query()->where('user_id', $resident->id)->count());
    }

    public function test_social_login_rejects_an_unverified_email_without_creating_an_account(): void
    {
        $this->mock(SocialIdentityTokenVerifier::class)
            ->shouldReceive('verify')
            ->once()
            ->andReturn([
                'sub' => 'google-sub-unverified',
                'email' => 'unverified@example.test',
                'email_verified' => false,
                'name' => 'Unverified User',
            ]);

        $this->postJson('/api/v1/auth/social', [
            'provider' => 'google',
            'id_token' => 'unverified-google-token',
        ])->assertUnprocessable()->assertJsonValidationErrors(['id_token']);

        $this->assertDatabaseMissing('users', ['email' => 'unverified@example.test']);
    }

    public function test_social_login_cannot_authenticate_non_resident_accounts(): void
    {
        $providerRoleId = Role::query()->where('code', RoleCode::Provider->value)->value('id');
        $provider = User::query()->create([
            'role_id' => $providerRoleId,
            'name' => 'Provider Account',
            'email' => 'provider@example.test',
            'password' => 'ProviderPassword123',
            'locale' => 'ar',
        ]);
        SocialAccount::query()->create([
            'user_id' => $provider->id,
            'provider' => 'google',
            'provider_user_id' => 'google-provider-sub',
        ]);

        $this->mock(SocialIdentityTokenVerifier::class)
            ->shouldReceive('verify')
            ->once()
            ->andReturn(['sub' => 'google-provider-sub']);

        $this->postJson('/api/v1/auth/social', [
            'provider' => 'google',
            'id_token' => 'provider-google-token',
        ])->assertUnprocessable()->assertJsonValidationErrors(['id_token']);
    }

    public function test_social_login_validates_supported_providers_and_token_presence(): void
    {
        $this->postJson('/api/v1/auth/social', [
            'provider' => 'facebook',
            'id_token' => 'token',
        ])->assertUnprocessable()->assertJsonValidationErrors(['provider']);

        $this->postJson('/api/v1/auth/social', [
            'provider' => 'google',
        ])->assertUnprocessable()->assertJsonValidationErrors(['id_token']);
    }

    public function test_google_id_token_signature_and_audience_are_verified(): void
    {
        config(['services.google.client_ids' => ['rounder-mobile-client']]);
        Cache::forget('social-login.google.jwks');
        $key = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
        $this->assertNotFalse($key);
        $this->assertTrue(openssl_pkey_export($key, $privateKey));
        $details = openssl_pkey_get_details($key);
        $this->assertIsArray($details);

        Http::fake([
            'https://www.googleapis.com/oauth2/v3/certs' => Http::response([
                'keys' => [[
                    'kid' => 'rounder-test-key',
                    'kty' => 'RSA',
                    'alg' => 'RS256',
                    'use' => 'sig',
                    'n' => $this->base64UrlEncode($details['rsa']['n']),
                    'e' => $this->base64UrlEncode($details['rsa']['e']),
                ]],
            ]),
        ]);

        $validToken = JWT::encode([
            'iss' => 'https://accounts.google.com',
            'aud' => 'rounder-mobile-client',
            'sub' => 'google-verified-sub',
            'email' => 'verified@example.test',
            'email_verified' => true,
            'exp' => time() + 3600,
            'iat' => time(),
        ], $privateKey, 'RS256', 'rounder-test-key');

        $this->postJson('/api/v1/auth/social', [
            'provider' => 'google',
            'id_token' => $validToken,
        ])->assertOk()->assertJsonPath('data.user.email', 'verified@example.test');

        $invalidAudienceToken = JWT::encode([
            'iss' => 'https://accounts.google.com',
            'aud' => 'another-client',
            'sub' => 'google-wrong-audience',
            'email' => 'wrong-audience@example.test',
            'email_verified' => true,
            'exp' => time() + 3600,
            'iat' => time(),
        ], $privateKey, 'RS256', 'rounder-test-key');

        $this->postJson('/api/v1/auth/social', [
            'provider' => 'google',
            'id_token' => $invalidAudienceToken,
        ])->assertUnprocessable()->assertJsonValidationErrors(['id_token']);

        $otherKey = openssl_pkey_new(['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]);
        $this->assertNotFalse($otherKey);
        $this->assertTrue(openssl_pkey_export($otherKey, $otherPrivateKey));
        $invalidSignatureToken = JWT::encode([
            'iss' => 'https://accounts.google.com',
            'aud' => 'rounder-mobile-client',
            'sub' => 'google-invalid-signature',
            'email' => 'invalid-signature@example.test',
            'email_verified' => true,
            'exp' => time() + 3600,
            'iat' => time(),
        ], $otherPrivateKey, 'RS256', 'rounder-test-key');

        $this->postJson('/api/v1/auth/social', [
            'provider' => 'google',
            'id_token' => $invalidSignatureToken,
        ])->assertUnprocessable()->assertJsonValidationErrors(['id_token']);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
