<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('Provider portal')) · Rounder</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap{{ app()->getLocale() === 'ar' ? '.rtl' : '' }}.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <style>
        :root{--petrol:#02333e;--petrol-dark:#01242c;--petrol-light:#025968;--orange:#fb822f;--sand:#f8fafb;--mint:#ddf3ee}
        *{box-sizing:border-box}
        body{margin:0;background:var(--sand);color:#17343a;font-family:"Tajawal",system-ui,sans-serif}
        .provider-topbar{height:68px;background:#fff;border-bottom:1px solid #e2ece9;display:flex;align-items:center;justify-content:space-between;padding:0 1.5rem;gap:1rem}
        .provider-brand{color:var(--orange);font-size:1.55rem;font-weight:800;text-decoration:none;letter-spacing:-.03em}
        .provider-brand small{color:var(--petrol);font-size:.76rem;letter-spacing:0}
        .provider-topbar-actions{display:flex;align-items:center;gap:.65rem}
        .provider-sidebar{position:fixed;inset-block:68px 0;inset-inline-start:0;width:260px;overflow-y:auto;background:linear-gradient(180deg,#02333e 0%,#023945 50%,#012831 100%);color:white;padding:1.1rem .85rem}
        [dir="rtl"] .provider-sidebar{inset-inline-start:auto;inset-inline-end:0}
        .provider-sidebar-link{display:flex;align-items:center;gap:.75rem;padding:.7rem .85rem;margin:.18rem 0;border-radius:10px;color:#c0ded8;text-decoration:none;font-size:.94rem;font-weight:600;transition:all .15s ease}
        .provider-sidebar-link i{width:1.25rem;text-align:center;color:#a6c9c2}
        .provider-sidebar-link:hover{background:rgba(255,255,255,.08);color:#fff}
        .provider-sidebar-link.active{background:var(--orange);color:#fff;box-shadow:0 3px 12px rgba(251,130,47,.3)}
        .provider-sidebar-link.active i{color:#fff}
        .provider-main{margin-inline-start:260px;min-height:calc(100vh - 68px);padding:1.5rem}
        [dir="rtl"] .provider-main{margin-inline-start:260px}
        .panel,.metric{background:#fff;border:1px solid #e2ece9;border-radius:16px;box-shadow:0 3px 16px rgba(2,51,62,.04)}
        .metric{padding:1.1rem;height:100%}.metric .value{font-size:1.8rem;font-weight:800;color:var(--petrol)}
        .btn-primary{background:var(--petrol);border-color:var(--petrol)}.btn-primary:hover{background:#025968;border-color:#025968}
        .btn-orange{background:var(--orange);border-color:var(--orange);color:white}.btn-orange:hover{background:#e67120;border-color:#e67120;color:white}
        .form-control,.form-select{border-radius:9px}
        .table>:not(caption)>*>*{padding:.75rem}
        @media(max-width:991.98px){
            .provider-sidebar{position:static;width:auto;display:flex;gap:.25rem;overflow-x:auto;white-space:nowrap;padding:.5rem .75rem}
            .provider-sidebar-link{flex:0 0 auto;margin:0;padding:.6rem .75rem}
            .provider-main,[dir="rtl"] .provider-main{margin:0;padding:1rem}
            .provider-topbar{padding:0 1rem}
        }
        @media print{
            .provider-topbar,.provider-sidebar,.d-print-none{display:none!important}
            .provider-main,[dir="rtl"] .provider-main{margin:0;padding:0}
            body{background:#fff}
        }
    </style>
    @stack('styles')
</head>
<body>
@auth('provider')
    @php
        $portalUser = auth('provider')->user();
        $portal = app(\App\Modules\Providers\Services\ProviderPortalService::class);
        $providerProfile = $portal->profileFor($portalUser);
        $providerLinks = [
            ['dashboard.view', 'provider.dashboard', 'provider.dashboard', 'fa-chart-line', __('Dashboard')],
            ['orders.view', 'provider.orders.index', 'provider.orders.*', 'fa-calendar-check', __('Bookings')],
            ['emergencies.view', 'provider.emergencies.index', 'provider.emergencies.*', 'fa-triangle-exclamation', __('Emergency requests')],
            ['prices.view', 'provider.prices.index', 'provider.prices.*', 'fa-tags', __('Prices and approvals')],
            ['finance.view', 'provider.finance.index', 'provider.finance.*', 'fa-coins', __('Financial accounts')],
            ['ratings.view', 'provider.ratings.index', 'provider.ratings.*', 'fa-star', __('Ratings')],
            ['users.manage', 'provider.team.index', 'provider.team.*', 'fa-users-gear', __('Users and permissions')],
            ['profile.manage', 'provider.settings.index', 'provider.settings.*', 'fa-gear', __('Settings / Profile')],
            ['contract.view', 'provider.contract', 'provider.contract*', 'fa-file-contract', __('Provider contract')],
        ];
        $portalHome = null;
        if ($providerProfile->is_approved) {
            if (!$providerProfile->contract_accepted_at) {
                $portalHome = $portal->allows($portalUser, $providerProfile, 'contract.view')
                    ? 'provider.contract'
                    : null;
            } else {
                foreach ($providerLinks as [$permission, $route]) {
                    if ($portal->allows($portalUser, $providerProfile, $permission)) {
                        $portalHome = $route;
                        break;
                    }
                }
            }
        }
    @endphp
    <header class="provider-topbar">
        <div class="d-flex align-items-center gap-3">
            <a class="provider-brand" href="{{ $portalHome ? route($portalHome) : '#' }}">rounder</a>
            <span class="d-none d-md-inline text-secondary border-start ps-3">{{ __('Provider portal') }}</span>
        </div>
        <div class="provider-topbar-actions">
            <span class="text-secondary small d-none d-lg-inline">{{ $providerProfile->company_name }}</span>
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">{{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}</button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach(['ar' => 'العربية', 'en' => 'English'] as $locale => $label)
                        <li>
                            <form method="post" action="{{ route('locale.update') }}">
                                @csrf
                                <input type="hidden" name="locale" value="{{ $locale }}">
                                <button class="dropdown-item" type="submit">{{ $label }}</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
            <form method="post" action="{{ route('provider.logout') }}" class="m-0">
                @csrf
                <button class="btn btn-sm btn-outline-danger" type="submit">{{ __('Sign out') }}</button>
            </form>
        </div>
    </header>
    @if($providerProfile->is_approved)
        <aside class="provider-sidebar" aria-label="{{ __('Provider navigation') }}">
            @if($providerProfile->contract_accepted_at)
                @foreach($providerLinks as [$permission, $route, $activeRoute, $icon, $label])
                    @if($portal->allows($portalUser, $providerProfile, $permission))
                        <a class="provider-sidebar-link {{ request()->routeIs($activeRoute) ? 'active' : '' }}" href="{{ route($route) }}">
                            <i class="fa-solid {{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span>
                        </a>
                    @endif
                @endforeach
            @elseif($portal->allows($portalUser, $providerProfile, 'contract.view'))
                <a class="provider-sidebar-link active" href="{{ route('provider.contract') }}">
                    <i class="fa-solid fa-file-contract" aria-hidden="true"></i><span>{{ __('Provider contract') }}</span>
                </a>
            @endif
        </aside>
    @endif
@endauth
<main class="provider-main">
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @yield('body')
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
