@extends('layouts.provider')
@section('title', $member ? __('Edit team member') : __('Add team member'))
@section('body')
<div class="mb-4"><a href="{{ route('provider.team.index') }}" class="text-decoration-none">{{ __('Team and permissions') }}</a><h1 class="h3 fw-bold mt-2">{{ $member ? __('Edit team member') : __('Add team member') }}</h1></div>
<form method="post" action="{{ $member ? route('provider.team.update', $member->id) : route('provider.team.store') }}" class="panel p-3 p-lg-4">
    @csrf
    @if($member) @method('put') @endif
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="name">{{ __('Name') }}</label><input class="form-control" id="name" name="name" value="{{ old('name', $member?->name) }}" maxlength="120" required></div>
        <div class="col-md-6"><label class="form-label" for="email">{{ __('Email') }}</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email', $member?->email) }}"></div>
        <div class="col-md-6"><label class="form-label" for="phone">{{ __('Phone') }}</label><input class="form-control" id="phone" name="phone" value="{{ old('phone', $member?->phone) }}" dir="ltr" required></div>
        <div class="col-md-6"><label class="form-label" for="locale">{{ __('Language') }}</label><select class="form-select" name="locale" id="locale"><option value="ar" @selected(old('locale', $member?->locale ?? 'ar') === 'ar')>{{ __('Arabic') }}</option><option value="en" @selected(old('locale', $member?->locale) === 'en')>{{ __('English') }}</option></select></div>
        <div class="col-md-6"><label class="form-label" for="password">{{ $member ? __('New password (leave blank to keep current)') : __('Password') }}</label><input class="form-control" id="password" name="password" type="password" {{ $member ? '' : 'required' }} minlength="8" autocomplete="new-password"></div>
        <div class="col-md-6"><label class="form-label" for="password_confirmation">{{ __('Confirm password') }}</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" {{ $member ? '' : 'required' }} minlength="8" autocomplete="new-password"></div>
        <div class="col-12">
            <h2 class="h6 fw-bold">{{ __('Permissions') }}</h2>
            <div class="row g-2">
                @foreach($permissions as $permission)
                    @php
                        $label = match($permission) {
                            'dashboard.view' => __('View dashboard'),
                            'orders.view' => __('View service orders'),
                            'orders.create' => __('Create service orders'),
                            'orders.manage' => __('Accept and update service orders'),
                            'prices.view' => __('View service prices'),
                            'prices.manage' => __('Submit and resubmit prices for approval'),
                            'emergencies.view' => __('View assigned emergency requests'),
                            'users.manage' => __('Manage team users and permissions'),
                        };
                    @endphp
                    <div class="col-md-6"><label class="form-check panel p-3 h-100"><input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission }}" @checked(in_array($permission, old('permissions', $member?->permissions ?? []), true))> <span class="form-check-label">{{ $label }}</span></label></div>
                @endforeach
            </div>
        </div>
        <div class="col-12 d-flex gap-2"><button class="btn btn-primary" type="submit">{{ __('Save') }}</button><a class="btn btn-outline-secondary" href="{{ route('provider.team.index') }}">{{ __('Cancel') }}</a></div>
    </div>
</form>
@endsection
