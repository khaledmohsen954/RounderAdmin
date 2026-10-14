<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale()==='ar'?'rtl':'ltr' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>@yield('title',__('Rounder admin'))</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap{{ app()->getLocale()==='ar'?'.rtl':'' }}.min.css" rel="stylesheet">
<style>
:root{--petrol:#073B43;--orange:#FF7A1A;--mint:#DDF3EE;--sand:#FFF9EF}body{background:#FFF9EF;color:#17343a;font-family:system-ui,-apple-system,"Segoe UI",sans-serif}.brand{font-weight:800;letter-spacing:.02em;color:var(--petrol)!important}.topbar{background:#fff;border-bottom:1px solid #e7eeeb}.sidebar{background:var(--petrol);min-height:calc(100vh - 66px);border-radius:20px}.sidebar a{color:#DDF3EE;text-decoration:none;display:block;padding:.8rem 1rem;border-radius:12px}.sidebar a:hover,.sidebar a.active{background:#ffffff1c;color:white}.panel,.metric{background:#fff;border:1px solid #e8efed;border-radius:18px;box-shadow:0 8px 26px #073B430a}.metric{padding:1.15rem;height:100%}.metric .value{font-size:1.8rem;font-weight:800;color:var(--petrol)}.metric .mark{color:var(--orange)}.content{padding:1.5rem}.btn-primary{--bs-btn-bg:var(--petrol);--bs-btn-border-color:var(--petrol);--bs-btn-hover-bg:#0a555d}.pill{background:var(--mint);color:var(--petrol);border-radius:50rem;padding:.3rem .7rem;font-size:.85rem}.table>:not(caption)>*>*{padding:.85rem .7rem}@media(max-width:767px){.sidebar{min-height:auto}.content{padding:1rem}}
</style></head>
<body>
<nav class="navbar topbar px-3 px-lg-4"><a class="navbar-brand brand" href="{{ route('admin.dashboard') }}">rounder <span class="small fw-normal text-secondary">{{ __('Rounder admin') }}</span></a>
<div class="d-flex align-items-center gap-2">
<form method="post" action="{{ route('locale.update') }}">@csrf<select name="locale" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="{{ __('Language') }}"><option value="ar" @selected(app()->getLocale()==='ar')>{{ __('Arabic') }}</option><option value="en" @selected(app()->getLocale()==='en')>{{ __('English') }}</option></select></form>
@if(auth()->check())<span class="small d-none d-md-inline">{{ auth()->user()->name }}</span><form method="post" action="{{ route('admin.logout') }}">@csrf<button class="btn btn-sm btn-outline-secondary">{{ __('Sign out') }}</button></form>@endif
</div></nav>
@yield('body')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>@stack('scripts')</body></html>
