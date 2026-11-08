<?php

namespace App\Modules\Providers\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Modules\Providers\Requests\ProviderRegistrationRequest;
use App\Modules\Providers\Models\ProviderProfile;
use App\Modules\Providers\Services\ProviderPortalService;
use App\Modules\Providers\Services\ProviderRegistrationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProviderRegistrationController extends Controller
{
    public function show(ProviderRegistrationService $registration, ProviderPortalService $portal): View|RedirectResponse
    {
        if (Auth::guard('provider')->check()) {
            return $this->destination($portal->profileFor(Auth::guard('provider')->user()));
        }

        return view('provider.register', ['services' => $registration->services()]);
    }

    public function store(
        ProviderRegistrationRequest $request,
        ProviderRegistrationService $registration,
        ProviderPortalService $portal
    ): RedirectResponse {
        if (Auth::guard('provider')->check()) {
            return $this->destination($portal->profileFor(Auth::guard('provider')->user()));
        }

        $registration->register($request->validated());

        return redirect()->route('provider.login')->with('status', __('تم إرسال طلبك بنجاح، وسيتم تفعيل الحساب بعد موافقة الإدارة.'));
    }

    private function destination(?ProviderProfile $profile): RedirectResponse
    {
        abort_unless($profile, 403);

        if (!$profile->is_approved) {
            return redirect()->route('provider.pending');
        }

        return redirect()->route($profile->contract_accepted_at ? 'provider.dashboard' : 'provider.contract');
    }
}