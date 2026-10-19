<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale()==='ar'?'rtl':'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>@yield('title',__('Rounder admin'))</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap{{ app()->getLocale()==='ar'?'.rtl':'' }}.min.css" rel="stylesheet">
<style>
:root{--petrol:#073B43;--orange:#FF7A1A;--mint:#DDF3EE;--sand:#FFF9EF}body{background:#FFF9EF;color:#17343a;font-family:system-ui,-apple-system,"Segoe UI",sans-serif}.brand{font-weight:800;letter-spacing:.02em;color:var(--petrol)!important}.topbar{background:#fff;border-bottom:1px solid #e7eeeb}.sidebar{background:var(--petrol);min-height:calc(100vh - 66px);border-radius:20px}.sidebar a{color:#DDF3EE;text-decoration:none;display:block;padding:.8rem 1rem;border-radius:12px}.sidebar a:hover,.sidebar a.active{background:#ffffff1c;color:white}.panel,.metric{background:#fff;border:1px solid #e8efed;border-radius:18px;box-shadow:0 8px 26px #073B430a}.metric{padding:1.15rem;height:100%}.metric .value{font-size:1.8rem;font-weight:800;color:var(--petrol)}.metric .mark{color:var(--orange)}.content{padding:1.5rem}.btn-primary{--bs-btn-bg:var(--petrol);--bs-btn-border-color:var(--petrol);--bs-btn-hover-bg:#0a555d}.pill{background:var(--mint);color:var(--petrol);border-radius:50rem;padding:.3rem .7rem;font-size:.85rem}.table>:not(caption)>*>*{padding:.85rem .7rem}@media(max-width:767px){.sidebar{min-height:auto}.content{padding:1rem}}

.admin-sidebar{--bs-offcanvas-width:240px;background:var(--petrol)!important;color:white}
.sidebar-links a{display:block;padding:.8rem 1rem;margin-bottom:.25rem;border-radius:10px;text-decoration:none;color:var(--mint)}
.sidebar-links a:hover,.sidebar-links a.active{background:#ffffff20;color:white}
.admin-main{min-width:0}.admin-main .container{max-width:1400px}
@media(min-width:992px){.admin-sidebar{position:fixed;top:66px;bottom:0;inset-inline-start:0;width:240px;overflow-y:auto}.admin-main{margin-inline-start:240px}.topbar{min-height:66px}}
@media(max-width:991.98px){.admin-main{margin:0}.admin-main .container{padding:1rem}.content{padding:0!important}.panel{padding:1rem!important}.table-responsive{max-width:100%}.navbar{gap:.5rem}.navbar-brand{font-size:1rem}input,select,textarea{max-width:100%}}
</style></head>
<body>
<nav class="navbar topbar px-3 px-lg-4">@if(auth()->user() instanceof \App\Modules\Identity\Models\Admin)<button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#admin-menu" aria-controls="admin-menu" aria-label="{{ __('Open menu') }}">☰</button>@endif
<a class="navbar-brand brand" href="{{ route('admin.dashboard') }}">rounder <span class="small fw-normal text-secondary">{{ __('Rounder admin') }}</span></a>
<div class="d-flex align-items-center gap-2">
<form method="post" action="{{ route('locale.update') }}">@csrf<select name="locale" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="{{ __('Language') }}"><option value="ar" @selected(app()->getLocale()==='ar')>{{ __('Arabic') }}</option><option value="en" @selected(app()->getLocale()==='en')>{{ __('English') }}</option></select></form>
@if(auth()->check())<span class="small d-none d-md-inline">{{ auth()->user()->name }}</span><form method="post" action="{{ route('admin.logout') }}">@csrf<button class="btn btn-sm btn-outline-secondary">{{ __('Sign out') }}</button></form>@endif
</div></nav>
@if(auth()->user() instanceof \App\Modules\Identity\Models\Admin)
<aside class="offcanvas-lg offcanvas-start admin-sidebar" tabindex="-1" id="admin-menu" aria-labelledby="admin-menu-title">
<div class="offcanvas-header"><h2 class="h5 text-white" id="admin-menu-title">Rounder</h2><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#admin-menu" aria-label="{{ __('Close menu') }}"></button></div>
<div class="offcanvas-body d-block p-3"><nav aria-label="{{ __('Administration navigation') }}" class="sidebar-links">
@foreach(['admin.dashboard'=>'Dashboard','admin.users.index'=>'Users','admin.providers.index'=>'Providers and contracts','admin.realestate.index'=>'Homes','admin.packages.index'=>'Packages and services','admin.content.index'=>'Resident operations','admin.service-orders.index'=>'Service orders','admin.emergencies.index'=>'Emergency requests','admin.permissions.index'=>'Permissions','admin.about'=>'About Rounder'] as $route=>$label)
@adminCan($route)
<a class="{{ request()->routeIs(str_replace('.index','.*',$route)) ? 'active' : '' }}" href="{{ route($route) }}">{{ __($label) }}</a>
@endadminCan
@endforeach
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
    document.addEventListener('submit', event => {
        if (!event.target.matches('.delete-record-form')) return;
        event.preventDefault(); pendingForm = event.target; modal.show();
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
