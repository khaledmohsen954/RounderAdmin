<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale()==='ar'?'rtl':'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>@yield('title',__('Rounder admin'))</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap{{ app()->getLocale()==='ar'?'.rtl':'' }}.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
<style>
:root{--petrol:#073B43;--orange:#FF7A1A;--mint:#DDF3EE;--sand:#FFF9EF}body{background:#FFF9EF;color:#17343a;font-family:"Tajawal",system-ui,sans-serif}.brand{font-weight:800;letter-spacing:.02em;color:var(--petrol)!important}.topbar{background:#fff;border-bottom:1px solid #e7eeeb}.sidebar{background:var(--petrol);min-height:calc(100vh - 66px);border-radius:20px}.sidebar a{color:#DDF3EE;text-decoration:none;display:block;padding:.8rem 1rem;border-radius:12px}.sidebar a:hover,.sidebar a.active{background:#ffffff1c;color:white}.panel,.metric{background:#fff;border:1px solid #e8efed;border-radius:18px;box-shadow:0 8px 26px #073B430a}.metric{padding:1.15rem;height:100%}.metric .value{font-size:1.8rem;font-weight:800;color:var(--petrol)}.metric .mark{color:var(--orange)}.content{padding:1.5rem}.btn-primary{--bs-btn-bg:var(--petrol);--bs-btn-border-color:var(--petrol);--bs-btn-hover-bg:#0a555d}.pill{background:var(--mint);color:var(--petrol);border-radius:50rem;padding:.3rem .7rem;font-size:.85rem}.table>:not(caption)>*>*{padding:.85rem .7rem}@media(max-width:767px){.sidebar{min-height:auto}.content{padding:1rem}}

.admin-sidebar{--bs-offcanvas-width:240px;background:var(--petrol)!important;color:white}
.sidebar-links a{display:block;padding:.8rem 1rem;margin-bottom:.25rem;border-radius:10px;text-decoration:none;color:var(--mint)}
.sidebar-links a:hover,.sidebar-links a.active{background:#ffffff20;color:white}
.admin-main{min-width:0}.admin-main .container{max-width:1400px}
@media(min-width:992px){.admin-sidebar{position:fixed;top:66px;bottom:0;inset-inline-start:0;width:240px;overflow-y:auto}.admin-main{margin-inline-start:240px}.topbar{min-height:66px}}
@media(max-width:991.98px){.admin-main{margin:0}.admin-main .container{padding:1rem}.content{padding:0!important}.panel{padding:1rem!important}.table-responsive{max-width:100%}.navbar{gap:.5rem}.navbar-brand{font-size:1rem}input,select,textarea{max-width:100%}}

body,button,input,select,textarea{font-family:"Tajawal",system-ui,sans-serif}
.kpi-card{height:100%;min-height:154px;padding:1.25rem;background:#fff;border:1px solid #e4ecea;border-radius:18px;box-shadow:0 6px 20px #073b4305;display:flex;flex-direction:column;justify-content:space-between;gap:1.1rem}
.kpi-card .kpi-label{font-size:.95rem;font-weight:500;color:#61757b;line-height:1.6}
.kpi-icon{flex-shrink:0;width:44px;height:44px;border-radius:13px;display:grid;place-items:center;color:var(--petrol);background:#e8f4f0;font-size:1.2rem}
.kpi-card.financial .kpi-icon{background:#fff0df;color:#b6530b}
.kpi-card.urgent .kpi-icon{background:#fff0f0;color:#c54848}
.kpi-value{font-size:2rem;font-weight:800;line-height:1.2;letter-spacing:.01em;color:var(--petrol);font-variant-numeric:tabular-nums;overflow-wrap:anywhere}
.kpi-currency{font-size:.8rem;font-weight:500;color:#75868b}
.dashboard-intro{padding:1.25rem 0}.dashboard-intro h1{font-weight:800}
@media(max-width:575.98px){.kpi-card{min-height:132px}.kpi-value{font-size:1.8rem}}
.topbar{position:sticky;top:0;z-index:1030}.menu-group summary{cursor:pointer;color:#d9e9e6;font-size:.8rem}.menu-group a{font-size:.9rem;padding-inline-start:1.5rem}.menu-group i{width:1.2rem;text-align:center}
.notification-dropdown{width:min(360px,calc(100vw - 24px))}#notification-list{max-height:380px;overflow-y:auto;overflow-wrap:anywhere}@media(max-width:575.98px){.topbar .notification-dropdown{position:fixed!important;top:70px!important;inset-inline-end:12px!important;inset-inline-start:auto!important;transform:none!important}}
</style></head>
<body>
<nav class="navbar topbar px-3 px-lg-4">@if(auth()->user() instanceof \App\Modules\Identity\Models\Admin)<button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#admin-menu" aria-controls="admin-menu" aria-label="{{ __('Open menu') }}">☰</button>@endif
<a class="navbar-brand brand" href="{{ route('admin.dashboard') }}">rounder <span class="small fw-normal text-secondary">{{ __('Rounder admin') }}</span></a>
<div class="d-flex align-items-center gap-2">
@if(auth()->user() instanceof \App\Modules\Identity\Models\Admin)
@include('admin.partials.notifications')
@endif
<form method="post" action="{{ route('locale.update') }}">@csrf<select name="locale" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="{{ __('Language') }}"><option value="ar" @selected(app()->getLocale()==='ar')>{{ __('Arabic') }}</option><option value="en" @selected(app()->getLocale()==='en')>{{ __('English') }}</option></select></form>
@if(auth()->check())<span class="small d-none d-md-inline">{{ auth()->user()->name }}</span><form method="post" action="{{ route('admin.logout') }}">@csrf<button class="btn btn-sm btn-outline-secondary">{{ __('Sign out') }}</button></form>@endif
</div></nav>
@if(auth()->user() instanceof \App\Modules\Identity\Models\Admin)
<aside class="offcanvas-lg offcanvas-start admin-sidebar" tabindex="-1" id="admin-menu" aria-labelledby="admin-menu-title">
<div class="offcanvas-header"><h2 class="h5 text-white" id="admin-menu-title">Rounder</h2><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#admin-menu" aria-label="{{ __('Close menu') }}"></button></div>
<div class="offcanvas-body d-block p-3"><nav aria-label="{{ __('Administration navigation') }}" class="sidebar-links">
@if(app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(),'admin.dashboard','GET') || app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(),'admin.about','GET'))
<details class="menu-group mb-2" open><summary class="fw-bold px-3 py-2">{{ __('Dashboard') }}</summary>
@adminCan('admin.dashboard')<a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-chart-line me-2" aria-hidden="true"></i>{{ __('Dashboard') }}</a>@endadminCan
@adminCan('admin.about')<a class="{{ request()->routeIs('admin.about') ? 'active' : '' }}" href="{{ route('admin.about') }}"><i class="fa-solid fa-circle-info me-2" aria-hidden="true"></i>{{ __('About Rounder') }}</a>@endadminCan
</details>
@endif
@if(app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(),'admin.users.index','GET') || app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(),'admin.users.create','GET'))
<details class="menu-group mb-2" open><summary class="fw-bold px-3 py-2">{{ __('Users') }}</summary>
@adminCan('admin.users.index')<a class="{{ request()->routeIs('admin.users.index') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="fa-solid fa-users-gear me-2" aria-hidden="true"></i>{{ __('Users') }}</a>@endadminCan
@adminCan('admin.users.create')<a class="{{ request()->routeIs('admin.users.create') ? 'active' : '' }}" href="{{ route('admin.users.create') }}"><i class="fa-solid fa-user-plus me-2" aria-hidden="true"></i>{{ __('Create user') }}</a>@endadminCan
</details>
@endif
@if(app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(),'admin.providers.index','GET') || app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(),'admin.providers.create','GET'))
<details class="menu-group mb-2" open><summary class="fw-bold px-3 py-2">{{ __('Providers and contracts') }}</summary>
@adminCan('admin.providers.index')<a class="{{ request()->routeIs('admin.providers.index') ? 'active' : '' }}" href="{{ route('admin.providers.index') }}"><i class="fa-solid fa-building-shield me-2" aria-hidden="true"></i>{{ __('Registered providers') }}</a>@endadminCan
@adminCan('admin.providers.create')<a class="{{ request()->routeIs('admin.providers.create') ? 'active' : '' }}" href="{{ route('admin.providers.create') }}"><i class="fa-solid fa-plus me-2" aria-hidden="true"></i>{{ __('Add provider') }}</a>@endadminCan
</details>
@endif
@if(app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(),'admin.realestate.index','GET') || app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(),'admin.realestate.create','GET'))
<details class="menu-group mb-2" open><summary class="fw-bold px-3 py-2">{{ __('Homes') }}</summary>
@adminCan('admin.realestate.index')<a class="{{ request()->routeIs('admin.realestate.index') ? 'active' : '' }}" href="{{ route('admin.realestate.index') }}"><i class="fa-solid fa-city me-2" aria-hidden="true"></i>{{ __('Homes') }}</a>@endadminCan
@adminCan('admin.realestate.create')<a class="{{ request()->routeIs('admin.realestate.create') ? 'active' : '' }}" href="{{ route('admin.realestate.create') }}"><i class="fa-solid fa-plus me-2" aria-hidden="true"></i>{{ __('Add home') }}</a>@endadminCan
</details>
@endif
@if(app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(),'admin.services.index','GET') || app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(),'admin.services.create','GET') || app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(),'admin.packages.index','GET') || app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(),'admin.packages.create','GET') || app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(),'admin.package-features.index','GET') || app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(),'admin.package-features.create','GET') || app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(),'admin.subscriptions.index','GET'))
<details class="menu-group mb-2" open><summary class="fw-bold px-3 py-2">{{ __('Catalog management') }}</summary>
@adminCan('admin.services.index')<a class="{{ request()->routeIs('admin.services.index') ? 'active' : '' }}" href="{{ route('admin.services.index') }}"><i class="fa-solid fa-screwdriver-wrench me-2" aria-hidden="true"></i>{{ __('Available services') }}</a>@endadminCan
@adminCan('admin.services.create')<a class="{{ request()->routeIs('admin.services.create') ? 'active' : '' }}" href="{{ route('admin.services.create') }}"><i class="fa-solid fa-plus me-2" aria-hidden="true"></i>{{ __('Create service') }}</a>@endadminCan
@adminCan('admin.packages.index')<a class="{{ request()->routeIs('admin.packages.index') ? 'active' : '' }}" href="{{ route('admin.packages.index') }}"><i class="fa-solid fa-box-open me-2" aria-hidden="true"></i>{{ __('Customer packages') }}</a>@endadminCan
@adminCan('admin.packages.create')<a class="{{ request()->routeIs('admin.packages.create') ? 'active' : '' }}" href="{{ route('admin.packages.create') }}"><i class="fa-solid fa-plus me-2" aria-hidden="true"></i>{{ __('Create customer package') }}</a>@endadminCan
@adminCan('admin.package-features.index')<a class="{{ request()->routeIs('admin.package-features.index') ? 'active' : '' }}" href="{{ route('admin.package-features.index') }}"><i class="fa-solid fa-list-check me-2" aria-hidden="true"></i>{{ __('Package comparison features') }}</a>@endadminCan
@adminCan('admin.package-features.create')<a class="{{ request()->routeIs('admin.package-features.create') ? 'active' : '' }}" href="{{ route('admin.package-features.create') }}"><i class="fa-solid fa-plus me-2" aria-hidden="true"></i>{{ __('Add comparison feature') }}</a>@endadminCan
@adminCan('admin.subscriptions.index')<a class="{{ request()->routeIs('admin.subscriptions.index') ? 'active' : '' }}" href="{{ route('admin.subscriptions.index') }}"><i class="fa-solid fa-repeat me-2" aria-hidden="true"></i>{{ __('Customer subscriptions') }}</a>@endadminCan
</details>
@endif
@if(app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(),'admin.service-orders.index','GET') || app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(),'admin.service-orders.create','GET') || app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(),'admin.emergencies.index','GET') || app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(),'admin.emergencies.create','GET'))
<details class="menu-group mb-2" open><summary class="fw-bold px-3 py-2">{{ __('Requests') }}</summary>
@adminCan('admin.service-orders.index')<a class="{{ request()->routeIs('admin.service-orders.index') ? 'active' : '' }}" href="{{ route('admin.service-orders.index') }}"><i class="fa-solid fa-clipboard-list me-2" aria-hidden="true"></i>{{ __('Service orders') }}</a>@endadminCan
@adminCan('admin.service-orders.create')<a class="{{ request()->routeIs('admin.service-orders.create') ? 'active' : '' }}" href="{{ route('admin.service-orders.create') }}"><i class="fa-solid fa-plus me-2" aria-hidden="true"></i>{{ __('Create service order') }}</a>@endadminCan
@adminCan('admin.emergencies.index')<a class="{{ request()->routeIs('admin.emergencies.index') ? 'active' : '' }}" href="{{ route('admin.emergencies.index') }}"><i class="fa-solid fa-truck-medical me-2" aria-hidden="true"></i>{{ __('Emergency requests') }}</a>@endadminCan
@adminCan('admin.emergencies.create')<a class="{{ request()->routeIs('admin.emergencies.create') ? 'active' : '' }}" href="{{ route('admin.emergencies.create') }}"><i class="fa-solid fa-plus me-2" aria-hidden="true"></i>{{ __('Create emergency request') }}</a>@endadminCan
</details>
@endif
@if(app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(),'admin.content.index','GET'))
<details class="menu-group mb-2" open><summary class="fw-bold px-3 py-2">{{ __('Resident operations') }}</summary>
@adminCan('admin.content.index')<a class="{{ request()->routeIs('admin.content.index') ? 'active' : '' }}" href="{{ route('admin.content.index') }}"><i class="fa-solid fa-people-roof me-2" aria-hidden="true"></i>{{ __('Resident operations') }}</a>@endadminCan
</details>
@endif
@if(app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(),'admin.permissions.index','GET') || app(\App\Modules\Access\Services\AdminPermissionService::class)->allowsRoute(auth()->user(),'admin.permissions.create','GET'))
<details class="menu-group mb-2" open><summary class="fw-bold px-3 py-2">{{ __('Permissions') }}</summary>
@adminCan('admin.permissions.index')<a class="{{ request()->routeIs('admin.permissions.index') ? 'active' : '' }}" href="{{ route('admin.permissions.index') }}"><i class="fa-solid fa-shield-halved me-2" aria-hidden="true"></i>{{ __('Permissions') }}</a>@endadminCan
@adminCan('admin.permissions.create')<a class="{{ request()->routeIs('admin.permissions.create') ? 'active' : '' }}" href="{{ route('admin.permissions.create') }}"><i class="fa-solid fa-plus me-2" aria-hidden="true"></i>{{ __('Add permission') }}</a>@endadminCan
</details>
@endif
</nav></div>
</aside>
@endif
<div class="{{ auth()->user() instanceof \App\Modules\Identity\Models\Admin ? 'admin-main' : '' }}">
@yield('body')
</div>
<div class="modal fade" id="delete-confirmation" tabindex="-1" aria-labelledby="delete-confirmation-title" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered"><div class="modal-content">
<div class="modal-header"><h2 class="modal-title fs-5" id="delete-confirmation-title">{{ __('Confirm deletion') }}</h2></div>
<div class="modal-body">{{ __('Are you sure you want to delete this record?') }}</div>
<div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('No') }}</button>
<button type="button" class="btn btn-danger" id="confirm-delete">{{ __('Yes') }}</button></div>
</div></div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script><script>
(() => {
    let pendingForm = null;
    const node = document.getElementById('delete-confirmation');
    const modal = bootstrap.Modal.getOrCreateInstance(node);
    document.addEventListener('click', event => {
        const button = event.target.closest('[data-confirm-delete]');
        if (!button) return;
        pendingForm = button.closest('.delete-record-form');
        if (pendingForm) modal.show();
    });
    document.getElementById('confirm-delete').addEventListener('click', function () {
        if (!pendingForm) return;
        this.disabled = true;
        HTMLFormElement.prototype.submit.call(pendingForm);
    });
    node.addEventListener('hidden.bs.modal', () => {
        pendingForm = null; document.getElementById('confirm-delete').disabled = false;
    });
})();
</script>
@stack('scripts')</body></html>
