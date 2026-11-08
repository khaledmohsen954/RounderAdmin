@extends('layouts.admin')
@section('title', __('Package comparison features') . ' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-sliders text-rounder-orange" style="font-size: 1.4rem;"></i>
                <span>{{ __('Package comparison features') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Define feature items used in package comparison tables.') }}</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="pill px-3 py-2 fw-semibold" style="font-size: 0.88rem;">
                <i class="fa-solid fa-sliders me-1 text-primary"></i>
                {{ __('Total features') }}: <strong class="text-dark">{{ $featureDefinitions->count() }}</strong>
            </span>
            @adminCan('admin.package-features.create')
            <a class="btn btn-brand d-inline-flex align-items-center gap-2 shadow-sm" href="{{ route('admin.package-features.create') }}" style="border-radius: 10px;">
                <i class="fa-solid fa-plus"></i>
                <span>{{ __('Add comparison feature') }}</span>
            </a>
            @endadminCan
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Nav Pills Tabs for Packages, Features & Subscriptions --}}
    <ul class="nav nav-pills mb-4 gap-2">
        @adminCan('admin.packages.index')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}" href="{{ route('admin.packages.index') }}" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-boxes-stacked me-1"></i> {{ __('Customer packages') }}
            </a>
        </li>
        @endadminCan
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.package-features.*') ? 'active' : '' }}" href="{{ route('admin.package-features.index') }}" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-sliders me-1"></i> {{ __('Package comparison features') }}
            </a>
        </li>
        @adminCan('admin.subscriptions.index')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}" href="{{ route('admin.subscriptions.index') }}" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-repeat me-1"></i> {{ __('Customer subscriptions') }}
            </a>
        </li>
        @endadminCan
    </ul>

    {{-- Main Panel --}}
    <div class="panel p-3 p-lg-4 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 680px;">
                <thead class="bg-light-subtle">
                    <tr class="text-secondary small fw-bold">
                        <th style="width: 60px;">#</th>
                        <th style="width: 160px;">{{ __('Feature code') }}</th>
                        <th>{{ __('Feature name') }}</th>
                        <th style="width: 140px;">{{ __('Display order') }}</th>
                        <th style="width: 130px;">{{ __('Status') }}</th>
                        <th class="text-end" style="width: 160px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($featureDefinitions as $feature)
                        <tr>
                            <td class="text-secondary small font-monospace">#{{ $feature->id }}</td>
                            <td>
                                <code class="bg-light px-2 py-1 rounded text-dark border font-monospace" style="font-size: 0.85rem;">{{ $feature->code }}</code>
                            </td>
                            <td>
                                <div>
                                    <span class="fw-bold text-dark d-block">
                                        🇸🇦 {{ $feature->name_ar }}
                                    </span>
                                    <div class="text-secondary small d-flex flex-wrap gap-2 mt-1">
                                        @if($feature->name_en)
                                            <span dir="ltr">🇬🇧 {{ $feature->name_en }}</span>
                                        @endif
                                        @if($feature->name_ur)
                                            <span class="text-muted">|</span>
                                            <span>🇵🇰 {{ $feature->name_ur }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace px-2 py-1">
                                    <i class="fa-solid fa-arrow-down-1-9 me-1 text-muted"></i> {{ $feature->sort_order ?? 0 }}
                                </span>
                            </td>
                            <td>
                                @if($feature->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                        <i class="fa-solid fa-circle-check me-1"></i> {{ __('Active') }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill">
                                        <i class="fa-solid fa-circle-xmark me-1"></i> {{ __('Inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    @adminCan('admin.package-features.update', 'PUT')
                                    <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#editFeatureModal-{{ $feature->id }}" style="border-radius: 8px;">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                        <span>{{ __('Edit') }}</span>
                                    </button>
                                    @endadminCan

                                    @adminCan('admin.package-features.destroy', 'DELETE')
                                    @include('admin.partials.delete-button', ['action' => route('admin.package-features.destroy', $feature->id)])
                                    @endadminCan
                                </div>
                            </td>
                        </tr>

                        {{-- Edit Modal for each feature --}}
                        @adminCan('admin.package-features.update', 'PUT')
                        <div class="modal fade" id="editFeatureModal-{{ $feature->id }}" tabindex="-1" aria-labelledby="editFeatureModalLabel-{{ $feature->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content shadow" style="border-radius: 14px; border: 1px solid rgba(0,0,0,0.08);">
                                    <form method="post" action="{{ route('admin.package-features.update', $feature->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header border-bottom pb-3" style="background-color: #fdfefe; border-radius: 14px 14px 0 0;">
                                            <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="editFeatureModalLabel-{{ $feature->id }}">
                                                <i class="fa-solid fa-sliders text-rounder-orange"></i>
                                                <span>{{ __('Edit comparison feature') }}: {{ $feature->{'name_'.app()->getLocale()} }}</span>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold text-secondary">{{ __('Feature code') }} <span class="text-danger">*</span></label>
                                                    <input name="code" class="form-control font-monospace" value="{{ $feature->code }}" required maxlength="60" pattern="[A-Za-z0-9_-]+" style="border-radius: 10px;">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-semibold text-secondary">{{ __('Display order') }} <span class="text-danger">*</span></label>
                                                    <input name="sort_order" class="form-control font-monospace" type="number" min="0" max="65535" value="{{ $feature->sort_order ?? 0 }}" required style="border-radius: 10px;">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small fw-semibold text-secondary">{{ __('Status') }}</label>
                                                    <select name="is_active" class="form-select" style="border-radius: 10px;">
                                                        <option value="1" @selected($feature->is_active)>{{ __('Active') }}</option>
                                                        <option value="0" @selected(!$feature->is_active)>{{ __('Inactive') }}</option>
                                                    </select>
                                                </div>

                                                <div class="col-12 col-md-4">
                                                    <label class="form-label small fw-semibold text-secondary">🇸🇦 {{ __('Feature name') }} (العربية) <span class="text-danger">*</span></label>
                                                    <input name="name_ar" class="form-control" value="{{ $feature->name_ar }}" required maxlength="160" dir="rtl" style="border-radius: 10px;">
                                                </div>
                                                <div class="col-12 col-md-4">
                                                    <label class="form-label small fw-semibold text-secondary">🇬🇧 {{ __('Feature name') }} (English) <span class="text-danger">*</span></label>
                                                    <input name="name_en" class="form-control" value="{{ $feature->name_en }}" required maxlength="160" dir="ltr" style="border-radius: 10px;">
                                                </div>
                                                <div class="col-12 col-md-4">
                                                    <label class="form-label small fw-semibold text-secondary">🇵🇰 {{ __('Feature name') }} (اردو) <span class="text-danger">*</span></label>
                                                    <input name="name_ur" class="form-control" value="{{ $feature->name_ur }}" required maxlength="160" dir="rtl" style="border-radius: 10px;">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top pt-3">
                                            <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal" style="border-radius: 10px;">{{ __('Cancel') }}</button>
                                            <button type="submit" class="btn btn-brand px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm" style="border-radius: 10px;">
                                                <i class="fa-solid fa-check"></i>
                                                <span>{{ __('Save') }}</span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endadminCan
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-secondary">
                                <div class="mb-2"><i class="fa-solid fa-sliders fa-2x text-muted opacity-50"></i></div>
                                <div class="fw-semibold">{{ __('No features found') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</main>
@endsection
