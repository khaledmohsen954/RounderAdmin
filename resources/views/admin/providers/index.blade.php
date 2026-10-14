@extends('layouts.admin')
@section('title', __('Providers and contracts').' · Rounder')
@section('body')
<div class="container-fluid"><div class="row g-3 p-3 p-lg-4">
<aside class="col-md-3 col-xl-2"><div class="sidebar p-3">
<div class="text-white-50 small px-3 py-2">{{ __('Overview') }}</div>
<a href="{{ route('admin.dashboard') }}">▦ &nbsp;{{ __('Dashboard') }}</a>
<a href="{{ route('admin.users.index') }}">♙ &nbsp;{{ __('Users') }}</a>
<a href="{{ route('admin.homes.index') }}">⌂ &nbsp;{{ __('Homes') }}</a>
<a class="active" href="{{ route('admin.providers.index') }}">⚒ &nbsp;{{ __('Providers and contracts') }}</a>
<a href="{{ route('admin.packages.index') }}">◫ &nbsp;{{ __('Packages and services') }}</a>
<a href="{{ route('admin.content.index') }}">◈ &nbsp;{{ __('Resident operations') }}</a>
</div></aside>
<main class="col-md-9 col-xl-10 content">
<div class="mb-4"><div class="small text-secondary">{{ __('Provider operations') }}</div><h1 class="h3 fw-bold">{{ __('Providers and contracts') }}</h1></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="panel p-3 p-lg-4 mb-4"><h2 class="h5 fw-bold">{{ __('Register service provider') }}</h2>
<form method="post" action="{{ route('admin.providers.store') }}">@csrf
<div class="row g-3">
<div class="col-md-4"><label class="form-label">{{ __('Contact name') }}</label><input class="form-control" name="name" required minlength="2" maxlength="120" value="{{ old('name') }}"></div>
<div class="col-md-4"><label class="form-label">{{ __('Email address') }}</label><input type="email" class="form-control" name="email" required maxlength="254" value="{{ old('email') }}"></div>
<div class="col-md-4"><label class="form-label">{{ __('Phone') }} (+966…)</label><input type="tel" class="form-control" name="phone" required pattern="\+[1-9][0-9]{7,14}" value="{{ old('phone') }}"></div>
<div class="col-md-6"><label class="form-label">{{ __('Company name') }}</label><input class="form-control" name="company_name" required minlength="2" maxlength="160" value="{{ old('company_name') }}"></div>
<div class="col-md-3"><label class="form-label">{{ __('Account language') }}</label><select class="form-select" name="locale" required><option value="ar">العربية</option><option value="en">English</option><option value="ur">اردو</option></select></div>
<div class="col-md-3"><label class="form-label">{{ __('Approval') }}</label><select class="form-select" name="is_approved" required><option value="0">{{ __('Pending approval') }}</option><option value="1">{{ __('Approved') }}</option></select></div>
<div class="col-md-6"><label class="form-label">{{ __('Temporary password') }}</label><input type="password" class="form-control" name="password" required minlength="12" maxlength="72" autocomplete="new-password"></div>
<div class="col-md-6"><label class="form-label">{{ __('Confirm password') }}</label><input type="password" class="form-control" name="password_confirmation" required minlength="12" maxlength="72" autocomplete="new-password"></div>
<div class="col-md-3"><label class="form-label">{{ __('Service commission (%)') }}</label><input type="number" step="0.01" min="0" max="100" class="form-control" name="service_commission_percent" required value="{{ old('service_commission_percent',10) }}"></div>
<div class="col-md-3"><label class="form-label">{{ __('Emergency commission (%)') }}</label><input type="number" step="0.01" min="0" max="100" class="form-control" name="emergency_commission_percent" required value="{{ old('emergency_commission_percent',15) }}"></div>
<div class="col-md-3"><label class="form-label">{{ __('Contract starts on') }}</label><input type="date" class="form-control" name="starts_on" required value="{{ old('starts_on',now()->toDateString()) }}"></div>
<div class="col-md-3"><label class="form-label">{{ __('Contract ends on') }}</label><input type="date" class="form-control" name="ends_on" value="{{ old('ends_on') }}"></div>
<div class="col-12"><label class="form-label">{{ __('Contract terms') }}</label><textarea class="form-control" name="terms" maxlength="10000" rows="3">{{ old('terms') }}</textarea></div>
<div class="col-12 d-flex justify-content-end"><button class="btn btn-primary px-4">{{ __('Register provider and contract') }}</button></div>
</div></form></div>
<div class="panel p-3 p-lg-4"><h2 class="h5 fw-bold">{{ __('Registered providers') }}</h2>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>{{ __('Company name') }}</th><th>{{ __('Contact name') }}</th><th>{{ __('Email address') }}</th><th>{{ __('Approval') }}</th><th>{{ __('Service commission (%)') }}</th><th>{{ __('Emergency commission (%)') }}</th><th>{{ __('Contract') }}</th></tr></thead><tbody>
@forelse($providers as $provider)<tr><td>{{ $provider->provider_id }}</td><td>{{ $provider->company_name }}</td><td>{{ $provider->name }}</td><td>{{ $provider->email }}</td><td><span class="pill">{{ $provider->is_approved ? __('Approved') : __('Pending approval') }}</span></td><td>{{ $provider->service_commission_percent ?? '—' }}%</td><td>{{ $provider->emergency_commission_percent ?? '—' }}%</td><td>{{ $provider->contract_reference ?? '—' }}<div class="small text-secondary">{{ $provider->contract_status ?? '' }}</div></td></tr>
@empty<tr><td colspan="8" class="text-center text-secondary">{{ __('No providers found') }}</td></tr>@endforelse
</tbody></table>{{ $providers->links() }}</div>
</div>
</main></div></div>
@endsection
