@extends('layouts.admin')
@section('title', ($emergency ? __('Create emergency request') : __('Create service order')).' · Rounder')
@section('body')
{{-- Shared form layout; each request type has a separate validated endpoint. --}}
<main class="container py-4" style="max-width:900px">
<a class="btn btn-outline-secondary mb-3" href="{{ route($emergency ? 'admin.emergencies.index' : 'admin.service-orders.index') }}">{{ __('Back to requests') }}</a>
<div class="panel p-4">
<h1 class="h3 fw-bold mb-4">{{ $emergency ? __('Create emergency request') : __('Create service order') }}</h1>
@if($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="post" action="{{ route($emergency ? 'admin.emergencies.store' : 'admin.service-orders.store') }}" class="row g-3">
@csrf
<div class="col-12">
<label class="form-label" for="resident_home_id">{{ __('Resident and unit') }}</label>
<select id="resident_home_id" name="resident_home_id" class="form-select" required>
<option value="">{{ __('Select a resident unit') }}</option>
@foreach($memberships as $membership)
<option value="{{ $membership->id }}" @selected(old('resident_home_id') == $membership->id)>{{ $membership->resident_name }} · {{ $membership->home_name }} · {{ $membership->unit_label }}</option>
@endforeach
</select>
<div class="form-text">{{ __('The resident and property are resolved from the selected unit.') }}</div>
</div>
<div class="col-12">
<label class="form-label" for="request_option">{{ $emergency ? __('Emergency type') : __('Service') }}</label>
<select id="request_option" name="{{ $emergency ? 'emergency_type_id' : 'service_id' }}" class="form-select" required>
<option value="">{{ __('Choose an option') }}</option>
@foreach($options as $option)
<option value="{{ $option->id }}" @selected(old($emergency ? 'emergency_type_id' : 'service_id') == $option->id)>{{ $option->name ?? $option->code }}</option>
@endforeach
</select>
</div>
@if($emergency)
<div class="col-md-6"><label for="contact_phone" class="form-label">{{ __('Contact phone') }}</label><input id="contact_phone" name="contact_phone" type="tel" dir="ltr" value="{{ old('contact_phone') }}" class="form-control" maxlength="16" placeholder="+9665XXXXXXXX" required></div>
<div class="col-md-6"><label for="location_details" class="form-label">{{ __('Location details') }}</label><input id="location_details" name="location_details" value="{{ old('location_details') }}" class="form-control" minlength="4" maxlength="255" required></div>
<div class="col-12"><label for="description" class="form-label">{{ __('Emergency description') }}</label><textarea id="description" name="description" class="form-control" rows="4" minlength="10" maxlength="3000" required>{{ old('description') }}</textarea></div>
<div class="col-12"><div class="form-check"><input id="confirm_emergency" type="checkbox" name="confirm_emergency" value="1" class="form-check-input" @checked(old('confirm_emergency')) required><label for="confirm_emergency" class="form-check-label">{{ __('I confirm this is an emergency request.') }}</label></div></div>
@else
<div class="col-md-6"><label for="scheduled_at" class="form-label">{{ __('Scheduled date and time') }}</label><input id="scheduled_at" name="scheduled_at" type="datetime-local" class="form-control" value="{{ old('scheduled_at') }}" required></div>
<div class="col-12"><label for="notes" class="form-label">{{ __('Notes') }}</label><textarea id="notes" name="notes" class="form-control" maxlength="2000" rows="3">{{ old('notes') }}</textarea></div>
<div class="col-12"><div class="alert alert-info mb-0">{{ __('The service price is taken from the current service catalogue.') }}</div></div>
@endif
<div class="col-12"><button class="btn btn-primary px-4" type="submit">{{ __('Create request') }}</button></div>
</form>
</div>
</main>
@endsection
