@extends('layouts.admin')
@section('title', __('Residents').' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-users text-rounder-orange" style="font-size: 1.4rem;"></i>
                <span>{{ __('Residents') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Resident accounts registered in the mobile application.') }}</p>
        </div>
        <div>
            <span class="pill px-3 py-2 fw-semibold" style="font-size: 0.88rem;">
                <i class="fa-solid fa-user-group me-1 text-primary"></i>
                {{ __('Total residents') }}: <strong class="text-dark">{{ $residents->total() }}</strong>
            </span>
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Main Panel --}}
    <div class="panel p-3 p-lg-4">
        {{-- Luxury Search & Filters Bar --}}
        <form method="get" action="{{ route('admin.residents.index') }}" class="mb-4">
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
                            <a href="{{ route('admin.residents.index') }}" class="btn btn-white border-0 text-muted d-flex align-items-center px-2" title="{{ __('Clear search') }}" style="background: #fff;">
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
                            <a href="{{ route('admin.residents.index') }}" class="text-danger ms-1 text-decoration-none" title="{{ __('Clear') }}">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        </span>
                    </div>
                @endif
            </div>
        </form>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table align-middle" style="min-width: 680px;">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Email address') }}</th>
                        <th>{{ __('Phone') }}</th>
                        <th>{{ __('Linked home') }}</th>
                        <th class="text-center">{{ __('Bookings') }}</th>
                        <th>{{ __('Registration date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($residents as $resident)
                        <tr>
                            <td>{{ $resident->id }}</td>
                            <td class="fw-bold text-dark">
                                {{ $resident->name }}
                            </td>
                            <td>{{ $resident->email ?? '—' }}</td>
                            <td><span dir="ltr">{{ $resident->phone ?? '—' }}</span></td>
                            <td>
                                @if($resident->residentHomes->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($resident->residentHomes as $home)
                                            <span class="pill" style="font-size:0.78rem;">
                                                <i class="fa-solid fa-building me-1 text-rounder-orange"></i>{{ $home->name }}
                                                @if($home->pivot->unit_label)
                                                    <span class="text-secondary">({{ __('Unit') }}: <strong>{{ $home->pivot->unit_label }}</strong>)</span>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border px-2 py-1">
                                    {{ $resident->bookings_count }}
                                </span>
                            </td>
                            <td>{{ $resident->created_at?->format('Y-m-d') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-users-slash fa-2x mb-2 text-secondary opacity-50"></i>
                                    <div class="fw-bold fs-6 text-dark">{{ __('No residents found') }}</div>
                                    <div class="small text-secondary mt-1">{{ __('Try adjusting your search criteria') }}</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($residents->hasPages())
            <div class="mt-3 pt-3 border-top">
                {{ $residents->links() }}
            </div>
        @endif
    </div>
</main>
@endsection
