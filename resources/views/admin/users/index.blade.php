@extends('layouts.admin')
@section('title', __('Users').' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-users-gear text-rounder-orange" style="font-size: 1.4rem;"></i>
                <span>{{ __('Users') }}</span>
            </h1>
            <div class="small text-secondary">{{ __('Manage administrative accounts only.') }}</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="pill px-3 py-2 fw-semibold" style="font-size: 0.88rem;">
                <i class="fa-solid fa-user-shield me-1 text-primary"></i>
                {{ __('Total users') }}: <strong class="text-dark">{{ $users->total() }}</strong>
            </span>
            @adminCan('admin.users.create')
            <a class="btn btn-primary shadow-sm" href="{{ route('admin.users.create') }}">
                <i class="fa-solid fa-user-plus me-1"></i> {{ __('Create user') }}
            </a>
            @endadminCan
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Main Panel --}}
    <div class="panel p-3 p-lg-4">
        {{-- Luxury Search & Filters Bar --}}
        <form method="get" action="{{ route('admin.users.index') }}" class="mb-4">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-7 col-lg-5">
                    <div class="input-group search-input-group shadow-sm" style="border-radius: 12px; overflow: hidden; border: 1px solid #dce6e3;">
                        <span class="input-group-text bg-white border-0 text-muted ps-3">
                            <i class="fa-solid fa-magnifying-glass text-secondary"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-0 py-2"
                               value="{{ request('q') }}"
                               placeholder="{{ __('Search name, email or phone') }}..."
                               aria-label="{{ __('Search') }}">
                        @if(request('q'))
                            <a href="{{ route('admin.users.index') }}" class="btn btn-white border-0 text-muted d-flex align-items-center px-2" title="{{ __('Clear search') }}" style="background: #fff;">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </a>
                        @endif
                        <button type="submit" class="btn btn-primary px-3 fw-semibold border-0">
                            <i class="fa-solid fa-filter me-1"></i> {{ __('Filter') }}
                        </button>
                    </div>
                </div>

                @if(request('q'))
                    <div class="col-auto">
                        <span class="pill" style="background:#eaf2f4; color:var(--petrol);">
                            {{ __('Search') }}: <strong>"{{ request('q') }}"</strong>
                            <a href="{{ route('admin.users.index') }}" class="text-danger ms-1 text-decoration-none" title="{{ __('Clear') }}">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        </span>
                    </div>
                @endif
            </div>
        </form>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table align-middle" style="min-width: 700px;">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Email address') }}</th>
                        <th>{{ __('Phone') }}</th>
                        <th>{{ __('Permissions') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th style="width: 140px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>
                                <span class="fw-bold text-dark">{{ $user->name }}</span>
                                @if($user->is_super_admin)
                                    <span class="badge ms-1" style="background:#fff3ea; color:var(--orange); font-size:0.7rem; border:1px solid #ffd8bf;">
                                        <i class="fa-solid fa-crown me-1 text-rounder-orange"></i>{{ __('Super administrator') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="text-secondary font-monospace" style="font-size:0.86rem;">
                                    {{ $user->email }}
                                </span>
                            </td>
                            <td>
                                @if($user->phone)
                                    <span dir="ltr" class="text-secondary">{{ $user->phone }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($user->is_super_admin)
                                    <span class="pill fw-bold" style="background:#fff3ea; color:var(--orange); border-color:#ffd8bf;">
                                        <i class="fa-solid fa-crown me-1"></i>{{ __('Super administrator') }}
                                    </span>
                                @elseif($user->permissionSet)
                                    <span class="pill">
                                        <i class="fa-solid fa-shield-halved me-1 text-primary"></i>
                                        {{ $user->permissionSet->{'name_'.app()->getLocale()} ?? $user->permissionSet->name_ar ?? '—' }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="fa-solid fa-circle-check me-1"></i>{{ __('Active') }}
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        <i class="fa-solid fa-circle-xmark me-1"></i>{{ __('Inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if(!$user->is_super_admin && auth()->id() !== $user->id)
                                    <div class="d-flex align-items-center gap-1">
                                        @adminCan('admin.users.edit')
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.edit', $user) }}" title="{{ __('Edit') }}">
                                                <i class="fa-solid fa-pen-to-square me-1"></i>{{ __('Edit') }}
                                            </a>
                                        @endadminCan
                                        @include('admin.partials.delete-button', ['action' => route('admin.users.destroy', $user)])
                                    </div>
                                @else
                                    <span class="badge bg-light text-muted border font-monospace" style="font-size:0.72rem;">
                                        <i class="fa-solid fa-shield me-1"></i>{{ __('Protected') }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-users-slash fa-2x mb-2 text-secondary opacity-50"></i>
                                    <div class="fw-bold fs-6 text-dark">{{ __('No users found') }}</div>
                                    <div class="small text-secondary mt-1">{{ __('Try adjusting your search criteria') }}</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="mt-3 pt-3 border-top">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</main>
@endsection
