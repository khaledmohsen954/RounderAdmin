<?php

namespace Tests\Feature;

use App\Modules\Identity\Enums\RoleCode;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ResidentMobileUpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_authenticated_resident_can_update_mobile_after_verifying_otp(): void
    {
        $resident = $this->resident('0501111111');
        Sanctum::actingAs($resident);
        Cache::put('otp_0552222222', '084215', now()->addMinutes(3));
        Cache::put('otp_+966552222222', '084215', now()->addMinutes(3));

        $this->putJson('/api/v1/resident/mobile', [
            'mobile' => '+966552222222',
            'otp' => '084215',
        ])->assertOk()
            ->assertJsonPath('data.phone', '0552222222');

        $this->assertSame('0552222222', $resident->fresh()->phone);
        $this->assertFalse(Cache::has('otp_0552222222'));
        $this->assertFalse(Cache::has('otp_+966552222222'));
    }

    public function test_mobile_update_rejects_missing_or_incorrect_otp_and_does_not_change_phone(): void
    {
        $resident = $this->resident('0503333333');
        Sanctum::actingAs($resident);
        Cache::put('otp_0554444444', '198362', now()->addMinutes(3));

        $this->putJson('/api/v1/resident/mobile', [
            'mobile' => '0554444444',
            'otp' => '555555',
        ])->assertUnprocessable()->assertJsonValidationErrors(['otp']);

        $this->assertSame('0503333333', $resident->fresh()->phone);
        $this->assertFalse(Cache::has('otp_0554444444'));
    }

    public function test_mobile_update_rejects_numbers_already_used_by_another_user(): void
    {
        $resident = $this->resident('0505555555');
        $this->resident('0556666666');
        Sanctum::actingAs($resident);

        $this->putJson('/api/v1/resident/mobile', [
            'mobile' => '0556666666',
            'otp' => '123456',
        ])->assertUnprocessable()->assertJsonValidationErrors(['mobile']);
    }

    public function test_mobile_cannot_be_changed_through_the_unverified_profile_endpoint(): void
    {
        $resident = $this->resident('0507777777');
        Sanctum::actingAs($resident);

        $this->putJson('/api/v1/resident/profile', [
            'phone' => '0558888888',
        ])->assertUnprocessable()->assertJsonValidationErrors(['phone']);

        $this->assertSame('0507777777', $resident->fresh()->phone);
    }

    private function resident(string $phone): User
    {
        return User::query()->create([
            'role_id' => Role::query()->where('code', RoleCode::Resident->value)->value('id'),
            'name' => 'Mobile Update Resident',
            'phone' => $phone,
            'password' => 'ResidentPassword123',
            'locale' => 'ar',
        ]);
    }
}
