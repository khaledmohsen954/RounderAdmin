@extends('layouts.admin')
@section('title', ($user->exists ? __('Edit user') : __('Create user')) . ' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header & Navigation --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                @adminCan('admin.users.index')
                <a class="btn btn-sm btn-outline-secondary rounded-pill px-3" href="{{ route('admin.users.index') }}">
                    <i class="fa-solid fa-arrow-right me-1"></i> {{ __('Users') }}
                </a>
                <span class="text-muted">/</span>
                @endadminCan
                <span class="text-secondary small fw-semibold">{{ $user->exists ? __('Edit user') : __('Create user') }}</span>
            </div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-users-gear text-rounder-orange"></i>
                <span>{{ $user->exists ? __('Edit user') . ': ' . $user->name : __('Create user') }}</span>
                @if($user->exists)
                    <span class="badge bg-light text-dark border px-2 py-1 fs-6 font-monospace">#{{ $user->id }}</span>
                @endif
            </h1>
            <p class="text-secondary mb-0 small">
                {{ $user->exists ? __('Update administrative account details, permissions, and language.') : __('Add a new administrative user with specific role and permissions.') }}
            </p>
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Main Panel Form --}}
    <div class="panel p-3 p-lg-4 shadow-sm mb-4" style="border-top: 3px solid var(--orange, #fb822f); border-radius: 14px;">
        <form method="post" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
            @csrf
            @if($user->exists)
                @method('PUT')
            @endif

            {{-- 1. Personal & Login Details --}}
            <div class="mb-4">
                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-id-card text-rounder-orange"></i>
                    <span>{{ __('Personal details') }}</span>
                </h6>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-secondary" for="name">
                            <i class="fa-solid fa-user text-rounder-orange me-1"></i> {{ __('Name') }} <span class="text-danger">*</span>
                        </label>
                        <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required maxlength="120" placeholder="{{ __('Name') }}" style="border-radius: 10px;">
                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-secondary" for="phone">
                            <i class="fa-solid fa-phone text-rounder-orange me-1"></i> {{ __('Phone (05xxxxxxxx)') }} <span class="text-danger">*</span>
                        </label>
                        <input id="phone" name="phone" type="tel" class="form-control font-monospace @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" required pattern="05[0-9]{8}" placeholder="05XXXXXXXX" dir="ltr" maxlength="10" style="border-radius: 10px;">
                        @error('phone')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-secondary" for="email">
                            <i class="fa-solid fa-envelope text-rounder-orange me-1"></i> {{ __('Email address') }}
                        </label>
                        <input id="email" name="email" type="email" class="form-control font-monospace @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" maxlength="254" placeholder="admin@domain.com" style="border-radius: 10px;">
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="my-4 text-muted opacity-25">

            {{-- 2. Permissions & Preferences --}}
            <div class="mb-4">
                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-rounder-orange"></i>
                    <span>{{ __('Permissions and preferences') }}</span>
                </h6>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label for="permission-set" class="form-label small fw-semibold text-secondary">
                            <i class="fa-solid fa-shield-halved text-rounder-orange me-1"></i> {{ __('Permissions') }} <span class="text-danger">*</span>
                        </label>
                        <select id="permission-set" name="permission_set_id" class="form-select @error('permission_set_id') is-invalid @enderror" required style="border-radius: 10px;">
                            <option value="">{{ __('Choose permission') }}</option>
                            @foreach($sets as $set)
                                <option value="{{ $set->id }}" @selected(old('permission_set_id', $user->permission_set_id) == $set->id)>
                                    {{ app()->getLocale() === 'ar' ? $set->name_ar : $set->name_en }}
                                </option>
                            @endforeach
                        </select>
                        @error('permission_set_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="locale" class="form-label small fw-semibold text-secondary">
                            <i class="fa-solid fa-globe text-rounder-orange me-1"></i> {{ __('Language') }}
                        </label>
                        <select id="locale" name="locale" class="form-select @error('locale') is-invalid @enderror" style="border-radius: 10px;">
                            @foreach(['ar' => '🇸🇦 العربية (Arabic)', 'en' => '🇬🇧 English'] as $loc => $label)
                                <option value="{{ $loc }}" @selected(old('locale', $user->locale ?? 'ar') === $loc)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('locale')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="active" class="form-label small fw-semibold text-secondary">
                            <i class="fa-solid fa-toggle-on text-rounder-orange me-1"></i> {{ __('Status') }}
                        </label>
                        <select id="active" name="is_active" class="form-select @error('is_active') is-invalid @enderror" style="border-radius: 10px;">
                            <option value="1" @selected(old('is_active', $user->is_active ?? 1) == 1)>{{ __('Active') }}</option>
                            <option value="0" @selected(old('is_active', $user->is_active ?? 1) == 0)>{{ __('Inactive') }}</option>
                        </select>
                        @error('is_active')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            @if(!$user->exists)
                <hr class="my-4 text-muted opacity-25">
                {{-- 3. Initial Password for New User --}}
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-lock text-rounder-orange"></i>
                        <span>{{ __('Password') }}</span>
                    </h6>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="password" class="form-label small fw-semibold text-secondary">
                                {{ __('Password') }} <span class="text-danger">*</span>
                            </label>
                            <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" required minlength="12" maxlength="72" autocomplete="new-password" style="border-radius: 10px;">
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="password_confirmation" class="form-label small fw-semibold text-secondary">
                                {{ __('Confirm password') }} <span class="text-danger">*</span>
                            </label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required minlength="12" maxlength="72" autocomplete="new-password" style="border-radius: 10px;">
                        </div>

                        <div class="col-12">
                            <small class="text-muted">
                                <i class="fa-solid fa-circle-info text-primary me-1"></i>
                                {{ __('Use at least 12 characters, including uppercase, lowercase and a number.') }}
                            </small>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Form Actions --}}
            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary px-4" style="border-radius: 10px;">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="btn btn-brand px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm" style="border-radius: 10px;">
                    <i class="fa-solid fa-check"></i>
                    <span>{{ __('Save') }}</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Reset Password Panel for Existing User --}}
    @if($user->exists)
        @adminCan('admin.users.password', 'PUT')
        <div class="panel p-3 p-lg-4 shadow-sm" style="border-top: 3px solid var(--petrol, #0e3c36); border-radius: 14px;">
            <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                <i class="fa-solid fa-key text-rounder-orange"></i>
                <span>{{ __('Reset password') }}</span>
            </h5>
            <form method="post" action="{{ route('admin.users.password', $user) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-secondary">
                            {{ __('New password') }} <span class="text-danger">*</span>
                        </label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="12" maxlength="72" autocomplete="new-password" style="border-radius: 10px;">
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-secondary">
                            {{ __('Confirm password') }} <span class="text-danger">*</span>
                        </label>
                        <input type="password" name="password_confirmation" class="form-control" required minlength="12" maxlength="72" autocomplete="new-password" style="border-radius: 10px;">
                    </div>
                    <div class="col-12">
                        <small class="text-muted">
                            <i class="fa-solid fa-circle-info text-primary me-1"></i>
                            {{ __('Use at least 12 characters, including uppercase, lowercase and a number.') }}
                        </small>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 pt-3 mt-3 border-top">
                    <button type="submit" class="btn btn-outline-primary px-4 py-2 d-inline-flex align-items-center gap-2" style="border-radius: 10px;">
                        <i class="fa-solid fa-lock"></i>
                        <span>{{ __('Save password') }}</span>
                    </button>
                </div>
            </form>
        </div>
        @endadminCan
    @endif
</main>
@endsection
