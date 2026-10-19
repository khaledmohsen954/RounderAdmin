@extends('layouts.admin')
@section('title', __('Edit service').' · Rounder')
@section('body')
<main class="container py-4">
@adminCan('admin.packages.index')<a class="btn btn-outline-secondary mb-3" href="{{ route('admin.packages.index') }}">{{ __('Packages and services') }}</a>@endadminCan
<div class="panel p-4">
<h1 class="h3">{{ __('Edit service') }}</h1>
@if($errors->any())<div class="alert alert-danger"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@adminCan('admin.packages.services.update','PUT')<form method="post" enctype="multipart/form-data" action="{{ route('admin.packages.services.update', $service->id) }}">
@csrf @method('PUT')
<div class="row g-3">
<div class="col-md-4"><label for="category" class="form-label">{{ __('Category') }}</label><select id="category" name="service_category_id" class="form-select" required>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('service_category_id',$service->service_category_id)==$category->id)>{{ $category->name ?? $category->code }}</option>@endforeach</select></div>
<div class="col-md-4"><label for="code" class="form-label">{{ __('Service code') }}</label><input id="code" name="code" class="form-control" value="{{ old('code',$service->code) }}" required maxlength="60"></div>
<div class="col-md-4"><label for="price" class="form-label">{{ __('Customer price before discount') }}</label><input id="price" name="base_price" type="number" min="0" step=".01" class="form-control" value="{{ old('base_price',$service->price_before_discount ?? $service->base_price) }}" required></div>
@include('admin.packages.partials.pricing', ['item' => $service, 'priceField' => 'base_price'])
@foreach(['ar'=>'العربية','en'=>'English','ur'=>'اردو'] as $locale=>$label)
<div class="col-md-4"><label for="name_{{ $locale }}" class="form-label">{{ __('Service name') }} · {{ $label }}</label><input id="name_{{ $locale }}" name="name_{{ $locale }}" class="form-control" dir="{{ $locale === 'en' ? 'ltr' : 'rtl' }}" value="{{ old('name_'.$locale, $translations->get($locale)?->name) }}" required maxlength="160">
<label for="description_{{ $locale }}" class="form-label mt-2">{{ __('Description') }} · {{ $label }}</label><textarea id="description_{{ $locale }}" name="description_{{ $locale }}" class="form-control" rows="4" maxlength="5000" dir="{{ $locale === 'en' ? 'ltr' : 'rtl' }}">{{ old('description_'.$locale, $translations->get($locale)?->description) }}</textarea></div>
@endforeach
<div class="col-md-4"><label for="active" class="form-label">{{ __('Status') }}</label><select id="active" name="is_active" class="form-select"><option value="1" @selected(old('is_active',$service->is_active)==1)>{{ __('Active') }}</option><option value="0" @selected(old('is_active',$service->is_active)==0)>{{ __('Inactive') }}</option></select></div>
</div>
<p class="text-secondary mt-3">{{ __('Service changes apply to new orders. Existing order prices stay unchanged.') }}</p>
<button class="btn btn-primary mt-2">{{ __('Save service') }}</button>
</form>@endadminCan</div></main>
@endsection
