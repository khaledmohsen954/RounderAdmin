@extends('layouts.admin')
@section('title',__('Sign in').' · Rounder')
@section('body')
<div class="container min-vh-100 d-flex align-items-center justify-content-center py-5"><div class="panel p-4 p-md-5 w-100" style="max-width:460px">
<div class="brand fs-2 mb-2">rounder</div><h1 class="h4 mb-1">{{ __('Welcome back') }}</h1><p class="text-secondary mb-4">{{ __('Authentication is required to open the admin dashboard.') }}</p>
<form method="post" action="{{ route('admin.login.submit') }}">@csrf
<div class="mb-3"><label class="form-label" for="email">{{ __('Email address') }}</label><input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="username">@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="mb-3"><label class="form-label" for="password">{{ __('Password') }}</label><input id="password" name="password" type="password" class="form-control" required autocomplete="current-password"></div>
<div class="form-check mb-4"><input class="form-check-input" id="remember" name="remember" value="1" type="checkbox"><label class="form-check-label" for="remember">{{ __('Remember me') }}</label></div>
<button class="btn btn-primary w-100 py-2">{{ __('Sign in') }}</button></form></div></div>
@endsection
