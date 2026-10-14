@extends('layouts.admin')
@section('title', __('Edit package').' · Rounder')
@section('body')
<main class="container py-4">
<a class="btn btn-outline-secondary mb-3" href="{{ route('admin.packages.index') }}">{{ __('Packages and services') }}</a>
<div class="panel p-4"><h1 class="h3">{{ __('Edit package') }}</h1>
@if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="post" enctype="multipart/form-data" action="{{ route('admin.packages.update', $package->id) }}">
@csrf @method('PUT')
<div class="row g-3">
<div class="col-md-4"><label class="form-label">{{ __('Package code') }}</label><input name="code" class="form-control" value="{{ old('code',$package->code) }}" required maxlength="60"></div>
<div class="col-md-4"><label class="form-label">{{ __('Billing cycle') }}</label><select name="billing_cycle_id" class="form-select" required>@foreach($cycles as $cycle)<option value="{{ $cycle->id }}" @selected(old('billing_cycle_id',$package->billing_cycle_id)==$cycle->id)>{{ __($cycle->code === 'semiannual' ? 'Semiannual' : ucfirst($cycle->code)) }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label">{{ __('Customer price before discount') }}</label><input name="price" class="form-control" type="number" min="0" max="9999999999.99" step=".01" value="{{ old('price',$package->price_before_discount ?? $package->price) }}" required></div>
<div class="col-md-4"><label class="form-label">{{ __('Currency') }}</label><input name="currency_code" class="form-control" value="{{ old('currency_code',$package->currency_code) }}" pattern="[A-Z]{3}" maxlength="3" required></div>
<div class="col-md-4"><label class="form-label">{{ __('Duration months') }}</label><input name="duration_months" class="form-control" type="number" min="1" max="120" value="{{ old('duration_months',$package->duration_months) }}" required></div>
<div class="col-md-4"><label class="form-label">{{ __('Status') }}</label><select name="is_active" class="form-select"><option value="1" @selected(old('is_active',$package->is_active)==1)>{{ __('Active') }}</option><option value="0" @selected(old('is_active',$package->is_active)==0)>{{ __('Inactive') }}</option></select></div>
@include('admin.packages.partials.pricing', ['item' => $package, 'priceField' => 'price'])
@foreach(['ar'=>'العربية','en'=>'English','ur'=>'اردو'] as $locale=>$label)
<div class="col-md-4"><label class="form-label">{{ __('Package name') }} · {{ $label }}</label><input name="name_{{ $locale }}" class="form-control" dir="{{ $locale === 'en' ? 'ltr' : 'rtl' }}" value="{{ old('name_'.$locale,$translations->get($locale)?->name) }}" required maxlength="140">
<label class="form-label mt-2">{{ __('Package details') }} · {{ $label }}</label><textarea name="description_{{ $locale }}" rows="4" maxlength="5000" class="form-control" dir="{{ $locale === 'en' ? 'ltr' : 'rtl' }}">{{ old('description_'.$locale,$translations->get($locale)?->description) }}</textarea></div>
@endforeach
</div>
@include('admin.packages.partials.comparison-values')
@include('admin.packages.partials.included-services')
<div class="mt-4"><button class="btn btn-primary">{{ __('Save package') }}</button></div>
</form></div></main>
@endsection
