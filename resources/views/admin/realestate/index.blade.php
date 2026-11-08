@extends('layouts.admin')
@section('title', __('Homes') . ' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-city text-rounder-orange" style="font-size: 1.4rem;"></i>
                <span>{{ __('Homes') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Manage properties, residential buildings, and apartments.') }}</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="pill px-3 py-2 fw-semibold" style="font-size: 0.88rem;">
                <i class="fa-solid fa-building me-1 text-primary"></i>
                {{ __('Total homes') }}: <strong class="text-dark">{{ $homes->total() }}</strong>
            </span>
            @adminCan('admin.realestate.create')
            <a class="btn btn-brand d-inline-flex align-items-center gap-2 shadow-sm" href="{{ route('admin.realestate.create') }}" style="border-radius: 10px;">
                <i class="fa-solid fa-plus"></i>
                <span>{{ __('Add home') }}</span>
            </a>
            @endadminCan
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Main Panel --}}
    <div class="panel p-3 p-lg-4 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 750px;">
                <thead class="bg-light-subtle">
                    <tr class="text-secondary small fw-bold">
                        <th style="width: 70px;">#</th>
                        <th>{{ __('Home') }}</th>
                        <th style="width: 180px;">{{ __('City') }}</th>
                        <th style="width: 140px;">{{ __('Floors count') }}</th>
                        <th style="width: 150px;">{{ __('Apartments') }}</th>
                        <th style="width: 150px;">{{ __('Residents') }}</th>
                        <th class="text-end" style="width: 160px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($homes as $home)
                        <tr>
                            <td class="fw-semibold text-secondary">#{{ $home->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 36px; height: 36px; background: #fff3eb; color: var(--orange, #fb822f);">
                                        <i class="fa-solid fa-building" style="font-size: 0.95rem;"></i>
                                    </span>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $home->name }}</div>
                                        <div class="small">
                                            <span class="badge bg-light text-secondary border font-monospace">{{ $home->property_code }}</span>
                                            @if($home->district)
                                                <span class="text-muted ms-1">· {{ $home->district }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1 rounded-pill">
                                    <i class="fa-solid fa-location-dot me-1 text-danger"></i>
                                    {{ $home->city->translations->firstWhere('locale', app()->getLocale())?->name ?? $home->city->code }}
                                </span>
                            </td>
                            <td>
                                <span class="text-secondary small fw-semibold">
                                    <i class="fa-solid fa-layer-group me-1 text-muted"></i>
                                    {{ $home->floors_count ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill">
                                    <i class="fa-solid fa-door-open me-1 text-primary"></i>
                                    {{ $home->units_count }} {{ __('Apartments') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                                    <i class="fa-solid fa-users me-1"></i>
                                    {{ $home->residents_count }} {{ __('Residents') }}
                                </span>
                            </td>
                            <td class="text-end text-nowrap">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    @adminCan('admin.realestate.edit')
                                    <a class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" href="{{ route('admin.realestate.edit', $home->id) }}" style="border-radius: 8px;">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                        <span>{{ __('Edit') }}</span>
                                    </a>
                                    @endadminCan
                                    @adminCan('admin.realestate.destroy', 'DELETE')
                                    @include('admin.partials.delete-button', ['action' => route('admin.realestate.destroy', $home->id)])
                                    @endadminCan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                <div class="mb-2"><i class="fa-solid fa-building fa-2x text-muted opacity-50"></i></div>
                                <div class="fw-semibold">{{ __('No homes found') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($homes->hasPages())
            <div class="mt-4 pt-3 border-top">
                {{ $homes->links() }}
            </div>
        @endif
    </div>
</main>
@endsection
