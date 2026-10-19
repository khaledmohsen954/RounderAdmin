@extends('layouts.admin')
@section('title', __('Edit company').' · Rounder')
@section('body')
<main class="container py-4">
@adminCan('admin.providers.index')<a class="btn btn-outline-secondary mb-3" href="{{ route('admin.providers.index') }}">{{ __('Providers and contracts') }}</a>@endadminCan
@if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="panel p-4"><h1 class="h3">{{ __('Edit company') }}</h1>
@adminCan('admin.providers.update','PUT')<form method="post" action="{{ route('admin.providers.update',$provider->id) }}">@csrf @method('PUT')
<div class="row g-3">
<div class="col-md-4"><label class="form-label">{{ __('Contact name') }}</label><input class="form-control" name="name" required minlength="2" maxlength="120" value="{{ old('name',$provider->user->name) }}"></div>
<div class="col-md-4"><label class="form-label">{{ __('Email address') }}</label><input type="email" class="form-control" name="email" required maxlength="254" value="{{ old('email',$provider->user->email) }}"></div>
<div class="col-md-4"><label class="form-label">{{ __('Phone') }} (+966…)</label><input type="tel" class="form-control" name="phone" required pattern="\+[1-9][0-9]{7,14}" value="{{ old('phone',$provider->user->phone) }}"></div>
<div class="col-md-6"><label class="form-label">{{ __('Company name') }}</label><input class="form-control" name="company_name" required minlength="2" maxlength="160" value="{{ old('company_name',$provider->company_name) }}"></div>
<div class="col-md-3"><label class="form-label">{{ __('Account language') }}</label><select class="form-select" name="locale" required><option value="ar" @selected(old('locale',$provider->user->locale instanceof \BackedEnum ? $provider->user->locale->value : $provider->user->locale)==='ar')>العربية</option><option value="en" @selected(old('locale',$provider->user->locale instanceof \BackedEnum ? $provider->user->locale->value : $provider->user->locale)==='en')>English</option><option value="ur" @selected(old('locale',$provider->user->locale instanceof \BackedEnum ? $provider->user->locale->value : $provider->user->locale)==='ur')>اردو</option></select></div>
<div class="col-md-3"><label class="form-label">{{ __('Approval') }}</label><select class="form-select" name="is_approved" required><option value="0" @selected(old('is_approved',$provider->is_approved)==0)>{{ __('Pending approval') }}</option><option value="1" @selected(old('is_approved',$provider->is_approved)==1)>{{ __('Approved') }}</option></select></div>

@if($contract)
<input type="hidden" name="contract_id" value="{{ $contract->id }}">
@foreach(['service_commission_percent'=>'Service commission (%)','emergency_commission_percent'=>'Emergency commission (%)'] as $field=>$label)
<div class="col-md-6"><label class="form-label">{{ __($label) }}</label><input type="number" name="{{ $field }}" class="form-control" step=".01" min="0" max="100" value="{{ old($field,$contract->$field) }}" required></div>
@endforeach
@foreach(['starts_on'=>'Contract starts on','ends_on'=>'Contract ends on'] as $field=>$label)
<div class="col-md-6"><label class="form-label">{{ __($label) }}</label><input type="date" name="{{ $field }}" class="form-control" value="{{ old($field,$contract->$field?->format('Y-m-d')) }}" @required($field==='starts_on')></div>
@endforeach
<div class="col-12">@include('admin.providers.partials.terms-editor',['terms'=>$contract->terms])</div>
@endif
<div class="col-12"><p class="text-secondary">{{ __('Contract edits apply to future calculations. Accrued commissions stay unchanged.') }}</p><button class="btn btn-primary">{{ __('Save') }}</button></div>
</div></form>@endadminCan</div>
</main>
@endsection
