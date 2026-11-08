@extends('layouts.admin')
@section('title', __('Permissions') . ' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-shield-halved text-rounder-orange" style="font-size: 1.4rem;"></i>
                <span>{{ __('Permissions') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Manage permission sets and administrator roles in the control panel.') }}</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="pill px-3 py-2 fw-semibold" style="font-size: 0.88rem;">
                <i class="fa-solid fa-shield-halved me-1 text-primary"></i>
                {{ __('Total permission sets') }}: <strong class="text-dark">{{ $sets->total() }}</strong>
            </span>
            @adminCan('admin.permissions.create')
            <a class="btn btn-brand d-inline-flex align-items-center gap-2 shadow-sm" href="{{ route('admin.permissions.create') }}" style="border-radius: 10px;">
                <i class="fa-solid fa-plus"></i>
                <span>{{ __('Add permission') }}</span>
            </a>
            @endadminCan
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Main Panel --}}
    <div class="panel p-3 p-lg-4 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 620px;">
                <thead class="bg-light-subtle">
                    <tr class="text-secondary small fw-bold">
                        <th style="width: 70px;">#</th>
                        <th>{{ __('Name') }}</th>
                        <th style="width: 220px;">{{ __('Users') }}</th>
                        <th style="width: 220px;">{{ __('Permissions count') }}</th>
                        <th class="text-end" style="width: 160px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sets as $set)
                        <tr>
                            <td class="fw-semibold text-secondary">#{{ $set->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 36px; height: 36px; background: #fff3eb; color: var(--orange, #fb822f);">
                                        <i class="fa-solid fa-shield-halved" style="font-size: 0.95rem;"></i>
                                    </span>
                                    <div>
                                        <div class="fw-bold text-dark">{{ app()->getLocale() === 'ar' ? $set->name_ar : $set->name_en }}</div>
                                        <div class="small text-muted">{{ app()->getLocale() === 'ar' ? $set->name_en : $set->name_ar }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill">
                                    <i class="fa-solid fa-users me-1 text-primary"></i>
                                    {{ $set->admins_count }} {{ __('Users') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill">
                                    <i class="fa-solid fa-key me-1 text-warning"></i>
                                    {{ count($set->permissions ?? []) }} {{ __('Permissions') }}
                                </span>
                            </td>
                            <td class="text-end text-nowrap">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    @adminCan('admin.permissions.edit')
                                    <a class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" href="{{ route('admin.permissions.edit', $set->id) }}" style="border-radius: 8px;">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                        <span>{{ __('Edit') }}</span>
                                    </a>
                                    @endadminCan
                                    @adminCan('admin.permissions.destroy', 'DELETE')
                                    @include('admin.partials.delete-button', ['action' => route('admin.permissions.destroy', $set->id)])
                                    @endadminCan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-secondary">
                                <div class="mb-2"><i class="fa-solid fa-shield-halved fa-2x text-muted opacity-50"></i></div>
                                <div class="fw-semibold">{{ __('No records') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sets->hasPages())
            <div class="mt-4 pt-3 border-top">
                {{ $sets->links() }}
            </div>
        @endif
    </div>
</main>
@endsection
