<?php

namespace App\Modules\Providers\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Modules\Identity\Models\User;
use App\Modules\Providers\Models\ProviderProfile;
use App\Modules\Providers\Requests\ProviderPriceRequest;
use App\Modules\Providers\Services\ProviderPortalService;
use App\Core\Support\SaudiPhone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProviderPortalController extends Controller
{
    public function __construct(private readonly ProviderPortalService $portal)
    {
    }

    public function dashboard(Request $request): \Illuminate\Contracts\View\View
    {
        return view('provider.dashboard', $this->portal->dashboard($this->profile($request)));
    }

    public function pending(Request $request): \Illuminate\Contracts\View\View
    {
        $profile = $this->profile($request);
        abort_if($profile->is_approved, 404);

        return view('provider.pending', compact('profile'));
    }

    public function contract(Request $request): \Illuminate\Contracts\View\View
    {
        $profile = $this->profile($request);
        abort_unless($profile->is_approved, 403);
        abort_unless($this->portal->allows($this->actor($request), $profile, 'contract.view'), 403);
        $contract = $this->portal->contractFor($profile);
        abort_unless($contract, 404);
        $terms = app(\App\Services\RichTextSanitizer::class)->clean($contract->terms);

        return view('provider.contract', compact('profile', 'contract', 'terms'));
    }

    public function downloadContract(Request $request): \Illuminate\Http\Response
    {
        $profile = $this->profile($request);
        abort_unless($this->portal->allows($this->actor($request), $profile, 'contract.view'), 403);
        abort_unless($profile->is_approved && $this->portal->contractFor($profile), 404);

        return app(\App\Modules\Providers\Controllers\Admin\ProviderContractPdfController::class)
            ->download($profile);
    }

    public function acceptContract(Request $request): \Illuminate\Http\RedirectResponse
    {
        $profile = $this->profile($request);
        abort_unless($this->portal->allows($this->actor($request), $profile, 'contract.view'), 403);
        $request->validate(['accept_contract' => ['required', 'accepted']]);
        $this->portal->acceptContract($profile);

        return redirect()->route('provider.dashboard')->with('status', __('تم قبول العقد، مرحباً بك في لوحة مزود الخدمة.'));
    }

    public function orders(Request $request): \Illuminate\Contracts\View\View
    {
        return view('provider.orders.index', $this->portal->orders($this->profile($request)));
    }

    public function showOrder(Request $request, int $bookingId): \Illuminate\Contracts\View\View
    {
        return view('provider.orders.show', $this->portal->showOrder($this->profile($request), $bookingId));
    }

    public function createOrder(Request $request): \Illuminate\Contracts\View\View
    {
        return view('provider.orders.create', $this->portal->orderForm($this->profile($request)));
    }

    public function storeOrder(Request $request): \Illuminate\Http\RedirectResponse
    {
        $input = $request->validate([
            'resident_home_id' => ['required', 'integer', 'exists:resident_homes,id'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'sub_service_id' => ['nullable', 'integer', 'exists:sub_services,id'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $booking = $this->portal->createOrder($this->profile($request), $this->actor($request), $input);

        return redirect()->route('provider.orders.show', $booking->id)->with('status', __('Service order created successfully.'));
    }

    public function updateOrder(Request $request, int $bookingId): \Illuminate\Http\RedirectResponse
    {
        $input = $request->validate([
            'status' => ['required', Rule::in(['confirmed', 'in_progress', 'completed'])],
            'gross_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);
        $message = $this->portal->transitionOrder($this->profile($request), $this->actor($request), $bookingId, $input);

        return back()->with('status', $message);
    }

    public function prices(Request $request): \Illuminate\Contracts\View\View
    {
        return view('provider.prices.index', $this->portal->prices($this->profile($request)));
    }

    public function emergencies(Request $request): \Illuminate\Contracts\View\View
    {
        return view('provider.emergencies.index', $this->portal->emergencies($this->profile($request)));
    }

    public function financialAccount(Request $request): \Illuminate\Contracts\View\View
    {
        return view('provider.finance.index', $this->portal->financialAccount($this->profile($request)));
    }

    public function ratings(Request $request): \Illuminate\Contracts\View\View
    {
        return view('provider.ratings.index', $this->portal->ratings($this->profile($request)));
    }

    public function settings(Request $request): \Illuminate\Contracts\View\View
    {
        return view('provider.settings.index', $this->portal->profileSettings($this->profile($request)));
    }

    public function updateSettings(Request $request): \Illuminate\Http\RedirectResponse
    {
        $input = $request->validate([
            'company_name' => ['required', 'string', 'max:160'],
            'trade_name' => ['nullable', 'string', 'max:160'],
            'provider_type' => ['nullable', 'string', 'max:80'],
            'years_of_experience' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);
        $this->portal->updateProfile($this->profile($request), $input);

        return back()->with('status', __('Provider profile updated successfully.'));
    }

    public function addService(Request $request): \Illuminate\Http\RedirectResponse
    {
        $input = $request->validate([
            'service_id' => ['required', 'integer', 'exists:services,id'],
        ]);
        $this->portal->addService($this->profile($request), (int) $input['service_id']);

        return back()->with('status', __('Service added to your provider profile.'));
    }

    public function removeService(Request $request, int $serviceId): \Illuminate\Http\RedirectResponse
    {
        $this->portal->removeService($this->profile($request), $serviceId);

        return back()->with('status', __('Service removed from your provider profile.'));
    }

    public function createPrice(Request $request): \Illuminate\Contracts\View\View
    {
        return view('provider.prices.form', $this->portal->priceForm($this->profile($request)));
    }

    public function storePrice(ProviderPriceRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->portal->createPrice($this->profile($request), $request->validated());

        return redirect()->route('provider.prices.index')->with('status', __('Price submitted for admin approval.'));
    }

    public function editPrice(Request $request, int $priceId): \Illuminate\Contracts\View\View
    {
        return view('provider.prices.form', $this->portal->priceForm($this->profile($request), $priceId));
    }

    public function updatePrice(ProviderPriceRequest $request, int $priceId): \Illuminate\Http\RedirectResponse
    {
        $this->portal->resubmitPrice($this->profile($request), $priceId, $request->validated());

        return redirect()->route('provider.prices.index')->with('status', __('Price resubmitted for admin approval.'));
    }

    public function team(Request $request): \Illuminate\Contracts\View\View
    {
        return view('provider.team.index', $this->portal->team($this->profile($request)));
    }

    public function createTeamMember(): \Illuminate\Contracts\View\View
    {
        return view('provider.team.form', ['member' => null, 'permissions' => ProviderPortalService::PERMISSIONS]);
    }

    public function storeTeamMember(Request $request): \Illuminate\Http\RedirectResponse
    {
        $input = $this->teamInput($request);
        $this->portal->saveTeamMember($this->profile($request), $this->actor($request), $input);

        return redirect()->route('provider.team.index')->with('status', __('Team member added successfully.'));
    }

    public function editTeamMember(Request $request, int $memberId): \Illuminate\Contracts\View\View
    {
        return view('provider.team.form', $this->portal->teamMember($this->profile($request), $memberId));
    }

    public function updateTeamMember(Request $request, int $memberId): \Illuminate\Http\RedirectResponse
    {
        abort_unless($memberId !== $this->actor($request)->id, 403);
        $input = $this->teamInput($request, $memberId);
        $this->portal->saveTeamMember($this->profile($request), $this->actor($request), $input, $memberId);

        return redirect()->route('provider.team.index')->with('status', __('Team member updated successfully.'));
    }

    public function destroyTeamMember(Request $request, int $memberId): \Illuminate\Http\RedirectResponse
    {
        abort_unless($memberId !== $this->actor($request)->id, 403);
        $this->portal->removeTeamMember($this->profile($request), $memberId);

        return redirect()->route('provider.team.index')->with('status', __('Team member access revoked.'));
    }

    private function teamInput(Request $request, ?int $memberId = null): array
    {
        if ($request->has('phone')) {
            $request->merge(['phone' => SaudiPhone::normalize((string) $request->input('phone'))]);
        }
        $userId = $memberId ?? 0;
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['required', 'string', 'regex:' . SaudiPhone::REGEX, Rule::unique('users', 'phone')->ignore($userId)],
            'locale' => ['required', Rule::in(['ar', 'en'])],
            'password' => [$memberId === null ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', Rule::in(ProviderPortalService::PERMISSIONS)],
        ]);
        $data['permissions'] = $data['permissions'] ?? [];

        return $data;
    }

    private function actor(Request $request): User
    {
        return $request->user('provider');
    }

    private function profile(Request $request): ProviderProfile
    {
        $profile = $this->portal->profileFor($this->actor($request));
        abort_unless($profile, 403);

        return $profile;
    }
}
