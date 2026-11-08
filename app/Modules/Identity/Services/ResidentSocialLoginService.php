<?php

namespace App\Modules\Identity\Services;

use App\Core\Enums\SupportedLocale;
use App\Modules\Identity\Enums\RoleCode;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\SocialAccount;
use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ResidentSocialLoginService
{
    public function __construct(private readonly SocialIdentityTokenVerifier $tokens)
    {
    }

    /** @return array{data: array{user: array<string, mixed>, token: string}} */
    public function login(string $provider, string $token): array
    {
        $identity = $this->tokens->verify($provider, $token);

        return DB::transaction(function () use ($provider, $identity): array {
            $socialAccount = SocialAccount::query()
                ->where('provider', $provider)
                ->where('provider_user_id', $identity['sub'])
                ->with('user.role')
                ->first();

            if ($socialAccount) {
                $user = $socialAccount->user;
                if ($user->role->code !== RoleCode::Resident->value) {
                    $this->rejectIdentity();
                }

                return $this->issueToken($user);
            }

            $email = strtolower(trim((string) ($identity['email'] ?? '')));
            if ($email === ''
                || strlen($email) > 254
                || filter_var($email, FILTER_VALIDATE_EMAIL) === false
                || !$this->isVerified($identity['email_verified'] ?? false)) {
                $this->rejectIdentity();
            }

            $user = User::query()->where('email', $email)->first();
            if ($user) {
                if ($user->role->code !== RoleCode::Resident->value) {
                    $this->rejectIdentity();
                }
                if (!$user->email_verified_at) {
                    $user->forceFill(['email_verified_at' => now()])->save();
                }
            } else {
                $role = Role::query()->where('code', RoleCode::Resident->value)->firstOrFail();
                $name = trim((string) ($identity['name'] ?? ''));
                if ($name === '') {
                    $name = strstr($email, '@', true) ?: $email;
                }

                $user = User::query()->create([
                    'role_id' => $role->id,
                    'name' => Str::limit($name, 120, ''),
                    'email' => $email,
                    'locale' => SupportedLocale::Arabic->value,
                    'password' => Str::random(64),
                ]);
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            SocialAccount::query()->create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_user_id' => $identity['sub'],
            ]);

            return $this->issueToken($user);
        });
    }

    /** @param mixed $value */
    private function isVerified(mixed $value): bool
    {
        return $value === true || $value === 1 || $value === '1' || $value === 'true';
    }

    private function rejectIdentity(): never
    {
        throw ValidationException::withMessages([
            'id_token' => __('This social account cannot be used to sign in.'),
        ]);
    }

    /** @return array{data: array{user: array<string, mixed>, token: string}} */
    private function issueToken(User $user): array
    {
        return [
            'data' => [
                'user' => $user->only(['id', 'name', 'email', 'phone', 'locale'])
                    + ['mobile' => $user->phone],
                'token' => $user->createToken('rounder-mobile')->plainTextToken,
            ],
        ];
    }
}
