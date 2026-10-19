@extends('layouts.admin')
@section('title', __('Create service').' · Rounder')
@section('body')
<main class="container py-4">
<a class="btn btn-outline-secondary mb-3" href="{{ route('admin.packages.index') }}">{{ __('Packages and services') }}</a>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="panel p-3 p-lg-4 mb-4"><h2 class="h5 fw-bold">{{ __('Create service') }}</h2>
<form method="post" enctype="multipart/form-data" action="{{ route('admin.packages.services.store') }}">@csrf
<div class="row g-3">
<div class="col-md-3"><label class="form-label">{{ __('Category') }}</label><select name="service_category_id" class="form-select" required><option value="">{{ __('Choose category') }}</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('service_category_id')==$category->id)>{{ $category->translations->firstWhere('locale',app()->getLocale())?->name ?? $category->code }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">{{ __('Service code') }}</label><input name="code" value="{{ old('code') }}" class="form-control" required maxlength="60" pattern="[A-Za-z0-9_-]+"></div>
<div class="col-md-3"><label class="form-label">{{ __('Customer price before discount') }}</label><input name="base_price" value="{{ old('base_price') }}" class="form-control" type="number" step=".01" min="0" required></div>
<div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary w-100">{{ __('Save service') }}</button></div>
@include('admin.packages.partials.pricing', ['item' => null, 'priceField' => 'base_price'])
@foreach(['ar'=>'العربية','en'=>'English','ur'=>'اردو'] as $locale=>$label)
<div class="col-md-4"><label class="form-label">{{ __('Service name') }} · {{ $label }}</label><input name="name_{{ $locale }}" value="{{ old('name_'.$locale) }}" class="form-control" required maxlength="160"></div>
<div class="col-md-4"><label class="form-label">{{ __('Description') }} · {{ $label }}</label><textarea name="description_{{ $locale }}" class="form-control" rows="2" maxlength="5000">{{ old('description_'.$locale) }}</textarea></div>
@endforeach
<div class="col-md-4"><label class="form-label">{{ __('Status') }}</label><select name="is_active" class="form-select"><option value="1" @selected(old('is_active',1)==1)>{{ __('Active') }}</option><option value="0" @selected(old('is_active',1)==0)>{{ __('Inactive') }}</option></select></div>
</div></form>
</div>


</main>
@endsection
