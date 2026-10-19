@extends('layouts.admin')
@section('title', __('Users').' · Rounder')
@section('body')
<main class="container py-4"><h1 class="h3 mb-4">{{ __('Users') }}</h1>
@include('admin.partials.feedback')
@adminCan('admin.users.index')<a class="btn btn-outline-secondary mb-3" href="{{ route('admin.users.index') }}">{{ __('Back') }}</a>@endadminCan
<form class="panel p-4" method="post" action="{{ $user->exists ? route('admin.users.update',$user) : route('admin.users.store') }}">@csrf
@if($user->exists) @method('PUT') @endif
<h2 class="h5">{{ $user->exists ? __('Edit') : __('Create user') }}</h2>
<div class="row g-3">
@foreach(['name'=>'Name','email'=>'Email address','phone'=>'Phone'] as $field=>$label)
<div class="col-md-4"><label class="form-label" for="{{ $field }}">{{ __($label) }}</label><input id="{{ $field }}" name="{{ $field }}" type="{{ $field==='email' ? 'email' : ($field==='phone' ? 'tel' : 'text') }}" class="form-control"
value="{{ old($field,$user->$field) }}" @required($field!=='phone') maxlength="{{ $field==='name' ? 120 : ($field==='phone' ? 20 : 254) }}"></div>
@endforeach
<div class="col-md-4"><label for="permission-set" class="form-label">{{ __('Permissions') }}</label>
<select id="permission-set" name="permission_set_id" class="form-select" required><option value="">{{ __('Choose permission') }}</option>
@foreach($sets as $set)<option value="{{ $set->id }}" @selected(old('permission_set_id',$user->permission_set_id)==$set->id)>{{ app()->getLocale()==='ar' ? $set->name_ar : $set->name_en }}</option>@endforeach
</select></div>
<div class="col-md-4"><label for="locale" class="form-label">{{ __('Language') }}</label><select id="locale" name="locale" class="form-select">
@foreach(['ar'=>'Arabic','en'=>'English'] as $locale=>$label)<option value="{{ $locale }}" @selected(old('locale',$user->locale ?? 'ar')===$locale)>{{ __($label) }}</option>@endforeach
</select></div>
<div class="col-md-4"><label for="active" class="form-label">{{ __('Status') }}</label><select id="active" name="is_active" class="form-select">
<option value="1" @selected(old('is_active',$user->is_active ?? 1)==1)>{{ __('Active') }}</option><option value="0" @selected(old('is_active',$user->is_active ?? 1)==0)>{{ __('Inactive') }}</option></select></div>
@if(!$user->exists)
@foreach(['password'=>'Password','password_confirmation'=>'Confirm password'] as $field=>$label)
<div class="col-md-6"><label for="{{ $field }}" class="form-label">{{ __($label) }}</label><input id="{{ $field }}" name="{{ $field }}" type="password" class="form-control" required minlength="12" maxlength="72" autocomplete="new-password"></div>
@endforeach
<p class="text-secondary">{{ __('Use at least 12 characters, including uppercase, lowercase and a number.') }}</p>
@endif
</div><button class="btn btn-primary mt-3">{{ __('Save') }}</button></form>
@if($user->exists)
@adminCan('admin.users.password','PUT')<form method="post" action="{{ route('admin.users.password',$user) }}" class="panel p-4 mt-4">@csrf @method('PUT')
<h2 class="h5">{{ __('Reset password') }}</h2><div class="row g-3">
@foreach(['password'=>'New password','password_confirmation'=>'Confirm password'] as $field=>$label)
<div class="col-md-6"><label class="form-label">{{ __($label) }}</label><input type="password" name="{{ $field }}" class="form-control" required minlength="12" maxlength="72" autocomplete="new-password"></div>
@endforeach</div><button class="btn btn-outline-primary mt-3">{{ __('Save password') }}</button></form>@endadminCan
@endif
</main>
@endsection
