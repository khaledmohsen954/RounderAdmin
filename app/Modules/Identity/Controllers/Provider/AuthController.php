<?php

namespace App\Modules\Identity\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Enums\RoleCode;
use App\Modules\Identity\Models\User;
use App\Modules\Providers\Services\ProviderPortalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function show(): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        if (Auth::guard('provider')->check()) {
            $user = Auth::guard('provider')->user();
            $profile = app(ProviderPortalService::class)->profileFor($user);

            return $this->destination($profile);
        }

        return view('provider.login');
    }

    public function login(Request $request, ProviderPortalService $portal): \Illuminate\Http\RedirectResponse
    {
        if (Auth::guard('provider')->check()) {
            return $this->destination($portal->profileFor(Auth::guard('provider')->user()));
        }

        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);
        $login = trim($data['login']);
        $key = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if ($key === 'phone') {
            $login = \App\Core\Support\SaudiPhone::normalize($login);
        }

        $user = User::query()->where($key, $login)
            ->whereHas('role', fn ($query) => $query->where('code', RoleCode::Provider->value))
            ->first();

        if (!$user || !$portal->profileFor($user) ||
            !Auth::guard('provider')->attempt(['id' => $user->id, 'password' => $data['password']])) {
            Auth::guard('provider')->logout();
            throw ValidationException::withMessages(['login' => __('auth.failed')]);
        }

        $request->session()->regenerate();

        return $this->destination($portal->profileFor($user));
    }

    public function logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        Auth::guard('provider')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('provider.login');
    }

    private function destination(?\App\Modules\Providers\Models\ProviderProfile $profile): \Illuminate\Http\RedirectResponse
    {
        abort_unless($profile, 403);

        if (!$profile->is_approved) {
            return redirect()->route('provider.pending');
        }

        if (!$profile->contract_accepted_at) {
            return redirect()->route('provider.contract');
        }

        return redirect()->route('provider.dashboard');
    }
}
