@extends('layouts.admin')
@section('title', __('Pages and policies') . ' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-shield text-rounder-orange" style="font-size: 1.4rem;"></i>
                <span>{{ __('Pages and policies') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Manage terms and conditions, privacy policy, and public content.') }}</p>
        </div>
        <div>
            <span class="pill px-3 py-2 fw-semibold" style="font-size: 0.88rem;">
                <i class="fa-solid fa-file-lines me-1 text-primary"></i>
                {{ __('Total pages') }}: <strong class="text-dark">{{ count($pages) }}</strong>
            </span>
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Main Panel --}}
    <div class="panel p-3 p-lg-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 700px;">
                <thead class="bg-light-subtle">
                    <tr class="text-secondary small fw-bold">
                        <th style="width: 70px;">#</th>
                        <th>{{ __('Page') }}</th>
                        <th style="width: 180px;">{{ __('Slug') }}</th>
                        <th style="width: 140px;">{{ __('Status') }}</th>
                        <th style="width: 180px;">{{ __('Last updated') }}</th>
                        <th class="text-end" style="width: 130px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pages as $page)
                        @php
                            $trans = $page->translations->firstWhere('locale', app()->getLocale()) 
                                ?? $page->translations->firstWhere('locale', 'ar') 
                                ?? $page->translations->first();
                        @endphp
                        <tr>
                            <td class="fw-semibold text-secondary">#{{ $page->id }}</td>
                            <td>
                                <div class="d-flex align-items-start gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0 mt-1" style="width: 32px; height: 32px; background: #fff3eb; color: var(--orange, #fb822f);">
                                        <i class="fa-solid fa-file-lines" style="font-size: 0.88rem;"></i>
                                    </span>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $trans?->title ?? $page->slug }}</div>
                                        <div class="small text-secondary text-truncate" style="max-width: 480px;">
                                            {{ Str::limit(strip_tags($trans?->content ?? ''), 120) }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace px-2 py-1">
                                    {{ $page->slug }}
                                </span>
                            </td>
                            <td>
                                @if($page->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                        <i class="fa-solid fa-circle-check me-1"></i> {{ __('Active') }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill">
                                        <i class="fa-solid fa-circle-xmark me-1"></i> {{ __('Inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-secondary small text-nowrap">
                                <i class="fa-regular fa-clock me-1"></i>
                                <span dir="ltr">{{ $page->updated_at?->format('Y-m-d H:i') ?? '—' }}</span>
                            </td>
                            <td class="text-end text-nowrap">
                                @adminCan('admin.pages.edit')
                                <a href="{{ route('admin.pages.edit', $page->id) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" style="border-radius: 8px;">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                    <span>{{ __('Edit') }}</span>
                                </a>
                                @endadminCan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-secondary">
                                <div class="mb-2"><i class="fa-solid fa-file-lines fa-2x text-muted opacity-50"></i></div>
                                <div class="fw-semibold">{{ __('No records') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</main>
@endsection
