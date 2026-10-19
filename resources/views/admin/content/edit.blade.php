@extends('layouts.admin')
@section('title',__('Edit').' · Rounder')
@section('body')
<main class="container py-4"><h1 class="h3">{{ __('Edit') }} · #{{ $record->id }}</h1>
@include('admin.partials.feedback')
<form class="panel p-4" method="post" action="{{ route($initiative ? 'admin.content.initiatives.update' : 'admin.content.announcements.update',$record->id) }}">@csrf @method('PUT')
<div class="row g-3">
<div class="col-12"><label class="form-label">{{ __('Home') }}</label><select name="home_id" class="form-select" @required($initiative)>
@if(!$initiative)<option value="">{{ __('All homes') }}</option>@endif
@foreach($homes as $home)<option value="{{ $home->id }}" @selected(old('home_id',$record->home_id)==$home->id)>{{ $home->name }}</option>@endforeach</select></div>
@foreach(['ar'=>'العربية','en'=>'English'] as $locale=>$label)
@php($translation=$record->translations->firstWhere('locale',$locale))
<div class="col-md-6"><label class="form-label">{{ __('Title') }} · {{ $label }}</label><input class="form-control" name="title_{{ $locale }}" value="{{ old('title_'.$locale,$translation?->title) }}" required minlength="3" maxlength="180">
<label class="form-label mt-3">{{ __('Description') }} · {{ $label }}</label><textarea class="form-control" rows="6" name="{{ ($initiative ? 'description_' : 'body_').$locale }}" required minlength="5" maxlength="{{ $initiative ? 5000 : 20000 }}">{{ old(($initiative ? 'description_' : 'body_').$locale,$initiative ? $translation?->description : $translation?->body) }}</textarea></div>
@endforeach
@if($initiative)
@foreach(['starts_at'=>'Starts at','ends_at'=>'Ends at'] as $field=>$label)
<div class="col-md-4"><label class="form-label">{{ __($label) }}</label><input type="datetime-local" class="form-control" name="{{ $field }}" value="{{ old($field,$record->$field?->format('Y-m-d\TH:i')) }}" required></div>
@endforeach
<div class="col-md-4"><label class="form-label">{{ __('Target amount') }}</label><input type="number" step=".01" min="0" name="target_amount" class="form-control" value="{{ old('target_amount',$record->target_amount) }}"></div>
@else
<div class="col-md-6"><label class="form-label">{{ __('Expires at') }}</label><input type="datetime-local" class="form-control" name="expires_at" value="{{ old('expires_at',$record->expires_at?->format('Y-m-d\TH:i')) }}"></div>
@endif
<div class="col-md-6"><label class="form-label">{{ __('Publish') }}</label><select class="form-select" name="{{ $initiative ? 'is_published' : 'published' }}">
<option value="1" @selected(old($initiative ? 'is_published' : 'published',$record->is_published)==1)>{{ __('Yes') }}</option>
<option value="0" @selected(old($initiative ? 'is_published' : 'published',$record->is_published)==0)>{{ __('No') }}</option></select></div>
</div><button class="btn btn-primary mt-3">{{ __('Save') }}</button></form></main>
@endsection
