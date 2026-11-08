<?php

namespace App\Http\Middleware;

use App\Modules\Identity\Models\User;
use App\Modules\Identity\Enums\RoleCode;
use App\Modules\Providers\Services\ProviderPortalService;
use Closure;
use Illuminate\Http\Request;

class EnsureProvider
{
    public function handle(Request $request, Closure $next, string ...$permissions): mixed
    {
        $user = $request->user('provider');
        abort_unless($user instanceof User && $user->role?->code === RoleCode::Provider->value, 403);

        $portal = app(ProviderPortalService::class);
        $profile = $portal->profileFor($user);
        abort_unless($profile && $profile->is_approved && $profile->contract_accepted_at, 403);

        foreach ($permissions as $permission) {
            abort_unless($portal->allows($user, $profile, $permission), 403);
        }

        return $next($request);
    }
}
