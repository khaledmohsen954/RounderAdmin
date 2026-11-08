<?php

namespace App\Modules\Identity\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use stdClass;

class SocialIdentityTokenVerifier
{
    private const PROVIDERS = [
        'google' => [
            'jwks' => 'https://www.googleapis.com/oauth2/v3/certs',
            'issuers' => ['https://accounts.google.com', 'accounts.google.com'],
        ],
        'apple' => [
            'jwks' => 'https://appleid.apple.com/auth/keys',
            'issuers' => ['https://appleid.apple.com'],
        ],
    ];

    /** @return array{sub: string, email?: string, email_verified?: bool|string} */
    public function verify(string $provider, string $token): array
    {
        $providerConfig = self::PROVIDERS[$provider] ?? null;
        $clientIds = config("services.{$provider}.client_ids", []);

        if ($providerConfig === null || !is_array($clientIds) || $clientIds === []) {
            abort(503, __('Social sign-in is not configured.'));
        }

        try {
            $headers = new stdClass();
            $claims = JWT::decode($token, $this->keys($provider, $providerConfig['jwks']), $headers);
        } catch (\DomainException|\InvalidArgumentException|\UnexpectedValueException $exception) {
            throw ValidationException::withMessages([
                'id_token' => __('The social identity token is invalid or expired.'),
            ]);
        }

        $audienceClaim = $claims->aud ?? null;
        $audiences = is_string($audienceClaim)
            ? [$audienceClaim]
            : (is_array($audienceClaim) ? $audienceClaim : []);
        $authorizedParty = $claims->azp ?? null;
        $isAudienceAllowed = $audiences !== []
            && count(array_filter($audiences, 'is_string')) === count($audiences)
            && count(array_intersect($audiences, $clientIds)) > 0;
        $isAuthorizedPartyAllowed = $authorizedParty === null || in_array($authorizedParty, $clientIds, true);

        if (($headers->alg ?? null) !== 'RS256'
            || !in_array($claims->iss ?? null, $providerConfig['issuers'], true)
            || !$isAudienceAllowed
            || !$isAuthorizedPartyAllowed
            || !isset($claims->exp)
            || !isset($claims->iat)
            || (count($audiences) > 1 && $authorizedParty === null)
            || !is_string($claims->sub ?? null)
            || $claims->sub === '') {
            throw ValidationException::withMessages([
                'id_token' => __('The social identity token is invalid or expired.'),
            ]);
        }

        return [
            'sub' => $claims->sub,
            'email' => is_string($claims->email ?? null) ? $claims->email : null,
            'email_verified' => $claims->email_verified ?? false,
            'name' => is_string($claims->name ?? null) ? $claims->name : null,
        ];
    }

    /** @return array<string, \Firebase\JWT\Key> */
    private function keys(string $provider, string $url): array
    {
        try {
            $jwks = Cache::remember(
                "social-login.{$provider}.jwks",
                now()->addMinutes(30),
                static fn () => Http::acceptJson()
                    ->timeout(5)
                    ->get($url)
                    ->throw()
                    ->json()
            );
        } catch (ConnectionException|RequestException $exception) {
            report($exception);
            abort(503, __('Social sign-in is temporarily unavailable.'));
        }

        if (!is_array($jwks) || !is_array($jwks['keys'] ?? null)) {
            abort(503, __('Social sign-in is temporarily unavailable.'));
        }

        $rsaKeys = array_values(array_filter(
            $jwks['keys'],
            static fn (mixed $key): bool => is_array($key)
                && ($key['kty'] ?? null) === 'RSA'
                && ($key['use'] ?? 'sig') === 'sig'
                && ($key['alg'] ?? 'RS256') === 'RS256'
        ));

        if ($rsaKeys === []) {
            abort(503, __('Social sign-in is temporarily unavailable.'));
        }

        try {
            return JWK::parseKeySet(['keys' => $rsaKeys], 'RS256');
        } catch (\DomainException|\InvalidArgumentException|\UnexpectedValueException $exception) {
            report($exception);
            abort(503, __('Social sign-in is temporarily unavailable.'));
        }
    }
}
