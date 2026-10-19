@extends('layouts.admin')
@section('title', __('Add provider').' · Rounder')
@section('body')
<main class="container py-4">
<a class="btn btn-outline-secondary mb-3" href="{{ route('admin.providers.index') }}">{{ __('Providers and contracts') }}</a>
@if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="panel p-3 p-lg-4 mb-4"><h2 class="h5 fw-bold">{{ __('Register service provider') }}</h2>
<form method="post" action="{{ route('admin.providers.store') }}">@csrf
<div class="row g-3">
<div class="col-md-4"><label class="form-label">{{ __('Contact name') }}</label><input class="form-control" name="name" required minlength="2" maxlength="120" value="{{ old('name') }}"></div>
<div class="col-md-4"><label class="form-label">{{ __('Email address') }}</label><input type="email" class="form-control" name="email" required maxlength="254" value="{{ old('email') }}"></div>
<div class="col-md-4"><label class="form-label">{{ __('Phone') }} (+966…)</label><input type="tel" class="form-control" name="phone" required pattern="\+[1-9][0-9]{7,14}" value="{{ old('phone') }}"></div>
<div class="col-md-6"><label class="form-label">{{ __('Company name') }}</label><input class="form-control" name="company_name" required minlength="2" maxlength="160" value="{{ old('company_name') }}"></div>
<div class="col-md-3"><label class="form-label">{{ __('Account language') }}</label><select class="form-select" name="locale" required><option value="ar" @selected(old('locale','ar')==='ar')>العربية</option><option value="en" @selected(old('locale','ar')==='en')>English</option><option value="ur" @selected(old('locale','ar')==='ur')>اردو</option></select></div>
<div class="col-md-3"><label class="form-label">{{ __('Approval') }}</label><select class="form-select" name="is_approved" required><option value="0" @selected(old('is_approved',0)==0)>{{ __('Pending approval') }}</option><option value="1" @selected(old('is_approved',0)==1)>{{ __('Approved') }}</option></select></div>
<div class="col-md-6"><label class="form-label">{{ __('Temporary password') }}</label><input type="password" class="form-control" name="password" required minlength="12" maxlength="72" autocomplete="new-password"></div>
<div class="col-md-6"><label class="form-label">{{ __('Confirm password') }}</label><input type="password" class="form-control" name="password_confirmation" required minlength="12" maxlength="72" autocomplete="new-password"></div>
<div class="col-md-3"><label class="form-label">{{ __('Service commission (%)') }}</label><input type="number" step="0.01" min="0" max="100" class="form-control" name="service_commission_percent" required value="{{ old('service_commission_percent',10) }}"></div>
<div class="col-md-3"><label class="form-label">{{ __('Emergency commission (%)') }}</label><input type="number" step="0.01" min="0" max="100" class="form-control" name="emergency_commission_percent" required value="{{ old('emergency_commission_percent',15) }}"></div>
<div class="col-md-3"><label class="form-label">{{ __('Contract starts on') }}</label><input type="date" class="form-control" name="starts_on" required value="{{ old('starts_on',now()->toDateString()) }}"></div>
<div class="col-md-3"><label class="form-label">{{ __('Contract ends on') }}</label><input type="date" class="form-control" name="ends_on" value="{{ old('ends_on') }}"></div>
<div class="col-12">@include('admin.providers.partials.terms-editor')</div>
<div class="col-12 d-flex justify-content-end"><button class="btn btn-primary px-4">{{ __('Register provider and contract') }}</button></div>
</div></form></div>

</main>
@endsection
