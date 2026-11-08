@extends('layouts.admin')
@section('title', __('Resident operations') . ' · Rounder')
@section('body')
@php
    $currentTab = request('tab');
    if (!$currentTab) {
        if (request()->has('initiatives')) {
            $currentTab = 'initiatives';
        } elseif (request()->has('reports')) {
            $currentTab = 'reports';
        } else {
            $currentTab = 'announcements';
        }
    }
@endphp

<style>
.custom-content-nav {
    background: #f1f5f3;
    padding: 6px;
    border-radius: 14px;
    display: inline-flex;
    max-width: 100%;
    overflow-x: auto;
    scrollbar-width: none;
    border: 1px solid #e2ece7;
}
.custom-content-nav::-webkit-scrollbar {
    display: none;
}
.custom-content-nav .nav-link {
    border-radius: 10px;
    color: #4b635d;
    font-weight: 600;
    padding: 9px 20px;
    font-size: 0.95rem;
    white-space: nowrap;
    border: none;
    transition: all 0.2s ease-in-out;
    display: inline-flex;
    align-items: center;
}
.custom-content-nav .nav-link:hover {
    color: #0e3c36;
    background: rgba(255, 255, 255, 0.7);
}
.custom-content-nav .nav-link.active {
    background: #0e3c36 !important;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(14, 60, 54, 0.15);
}
.custom-content-nav .nav-link.active .badge {
    background: var(--orange, #fb822f) !important;
    color: #ffffff !important;
}
.custom-content-nav .nav-link:not(.active) .badge {
    background: #dfe8e4 !important;
    color: #4b635d !important;
}
.bilingual-nav-pills {
    background: #f1f5f3;
    padding: 4px;
    border-radius: 10px;
    display: inline-flex;
}
.bilingual-nav-pills .nav-link {
    border-radius: 8px;
    color: #55726a;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 5px 14px;
    border: none;
}
.bilingual-nav-pills .nav-link.active {
    background: #ffffff !important;
    color: #0e3c36 !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
}
</style>

<main class="container py-4">
    {{-- Top Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small text-secondary mb-1">
                <i class="fa-solid fa-house-chimney-user me-1 text-rounder-orange"></i>
                {{ __('Resident app management') }}
            </div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-bullhorn text-rounder-orange" style="font-size: 1.4rem;"></i>
                <span>{{ __('Resident operations') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Manage announcements, initiatives, and resident facility reports.') }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <span class="pill px-3 py-2 fw-semibold" style="font-size: 0.85rem;">
                <i class="fa-solid fa-bullhorn me-1 text-primary"></i>
                {{ __('Announcements') }}: <strong class="text-dark">{{ $announcements->total() }}</strong>
            </span>
            <span class="pill px-3 py-2 fw-semibold" style="font-size: 0.85rem;">
                <i class="fa-solid fa-lightbulb me-1 text-warning"></i>
                {{ __('Initiatives') }}: <strong class="text-dark">{{ $initiatives->total() }}</strong>
            </span>
            <span class="pill px-3 py-2 fw-semibold" style="font-size: 0.85rem;">
                <i class="fa-solid fa-screwdriver-wrench me-1 text-info"></i>
                {{ __('Facility reports') }}: <strong class="text-dark">{{ $reports->total() }}</strong>
            </span>
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Tabs Navigation --}}
    <div class="mb-4">
        <ul class="nav nav-pills custom-content-nav gap-2" id="residentContentTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $currentTab === 'announcements' ? 'active' : '' }}" 
                        id="tab-announcements-btn" 
                        data-bs-toggle="pill" 
                        data-bs-target="#tab-announcements" 
                        type="button" 
                        role="tab" 
                        aria-controls="tab-announcements" 
                        aria-selected="{{ $currentTab === 'announcements' ? 'true' : 'false' }}">
                    <i class="fa-solid fa-bullhorn me-2"></i>
                    <span>{{ __('Announcements') }}</span>
                    <span class="badge rounded-pill ms-2">{{ $announcements->total() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $currentTab === 'initiatives' ? 'active' : '' }}" 
                        id="tab-initiatives-btn" 
                        data-bs-toggle="pill" 
                        data-bs-target="#tab-initiatives" 
                        type="button" 
                        role="tab" 
                        aria-controls="tab-initiatives" 
                        aria-selected="{{ $currentTab === 'initiatives' ? 'true' : 'false' }}">
                    <i class="fa-solid fa-lightbulb me-2"></i>
                    <span>{{ __('Initiatives') }}</span>
                    <span class="badge rounded-pill ms-2">{{ $initiatives->total() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $currentTab === 'reports' ? 'active' : '' }}" 
                        id="tab-reports-btn" 
                        data-bs-toggle="pill" 
                        data-bs-target="#tab-reports" 
                        type="button" 
                        role="tab" 
                        aria-controls="tab-reports" 
                        aria-selected="{{ $currentTab === 'reports' ? 'true' : 'false' }}">
                    <i class="fa-solid fa-screwdriver-wrench me-2"></i>
                    <span>{{ __('Facility reports') }}</span>
                    <span class="badge rounded-pill ms-2">{{ $reports->total() }}</span>
                </button>
            </li>
        </ul>
    </div>

    {{-- Tabs Content Panes --}}
    <div class="tab-content" id="residentContentTabsContent">

        {{-- TAB 1: ANNOUNCEMENTS --}}
        <div class="tab-pane fade {{ $currentTab === 'announcements' ? 'show active' : '' }}" id="tab-announcements" role="tabpanel" aria-labelledby="tab-announcements-btn">
            <div class="panel p-3 p-lg-4">
                {{-- Action Bar --}}
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 pb-3 mb-4 border-bottom">
                    <div>
                        <h2 class="h5 fw-bold mb-1 d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 36px; height: 36px; background: #fff3eb; color: var(--orange, #fb822f);">
                                <i class="fa-solid fa-bullhorn" style="font-size: 0.95rem;"></i>
                            </span>
                            <span>{{ __('Community announcements') }}</span>
                        </h2>
                        <p class="text-secondary small mb-0">{{ __('Manage announcements published to resident mobile app.') }}</p>
                    </div>
                    @adminCan('admin.content.announcements.store','POST')
                    <button class="btn btn-brand d-inline-flex align-items-center gap-2 shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#newAnnouncementCollapse" aria-expanded="false" aria-controls="newAnnouncementCollapse">
                        <i class="fa-solid fa-plus"></i>
                        <span>{{ __('Create announcement') }}</span>
                    </button>
                    @endadminCan
                </div>

                {{-- Collapsible Create Announcement Form --}}
                @adminCan('admin.content.announcements.store','POST')
                <div class="collapse mb-4 {{ $errors->has('title_ar') || $errors->has('title_en') || $errors->has('body_ar') || $errors->has('body_en') ? 'show' : '' }}" id="newAnnouncementCollapse">
                    <div class="card p-3 p-lg-4 border-0 shadow-sm" style="background: #fbfdfc; border-radius: 16px; border-top: 4px solid var(--orange, #fb822f) !important;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="h6 fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <i class="fa-solid fa-pen-to-square text-rounder-orange"></i>
                                <span>{{ __('Create announcement') }}</span>
                            </h3>
                            <button type="button" class="btn-close" data-bs-toggle="collapse" data-bs-target="#newAnnouncementCollapse" aria-label="Close"></button>
                        </div>

                        <form method="post" action="{{ route('admin.content.announcements.store') }}">
                            @csrf
                            <input type="hidden" name="tab" value="announcements">
                            <div class="row g-3">
                                {{-- Target Home --}}
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold small text-secondary">
                                        <i class="fa-solid fa-building me-1 text-rounder-orange"></i>
                                        {{ __('Home') }} ({{ __('All homes') }})
                                    </label>
                                    <select name="home_id" class="form-select" style="border-radius: 10px;">
                                        <option value="">{{ __('All homes') }}</option>
                                        @foreach($homes as $home)
                                            <option value="{{ $home->id }}">{{ $home->name }} · #{{ $home->id }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Expiry Date --}}
                                <div class="col-12 col-md-3">
                                    <label class="form-label fw-semibold small text-secondary">
                                        <i class="fa-solid fa-calendar-xmark me-1 text-rounder-orange"></i>
                                        {{ __('Expires at') }}
                                    </label>
                                    <input type="datetime-local" class="form-control" name="expires_at" style="border-radius: 10px;">
                                </div>

                                {{-- Publish Status --}}
                                <div class="col-12 col-md-3">
                                    <label class="form-label fw-semibold small text-secondary">
                                        <i class="fa-solid fa-satellite-dish me-1 text-rounder-orange"></i>
                                        {{ __('Publish now') }}
                                    </label>
                                    <select class="form-select" name="published" required style="border-radius: 10px;">
                                        <option value="1">{{ __('Yes') }} ({{ __('Published') }})</option>
                                        <option value="0">{{ __('No') }} ({{ __('Draft') }})</option>
                                    </select>
                                </div>

                                {{-- Bilingual Content Tabs --}}
                                <div class="col-12">
                                    <div class="bg-white p-3 rounded-3 border" style="border-color: #e5ece9 !important;">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="small fw-semibold text-secondary">
                                                <i class="fa-solid fa-language me-1 text-rounder-orange"></i>
                                                {{ __('Title') }} & {{ __('Body') }}
                                            </span>
                                            <ul class="nav nav-pills bilingual-nav-pills" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link active" id="ann-ar-tab" data-bs-toggle="pill" data-bs-target="#ann-ar" type="button" role="tab">
                                                        🇸🇦 العربية
                                                    </button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="ann-en-tab" data-bs-toggle="pill" data-bs-target="#ann-en" type="button" role="tab">
                                                        🇬🇧 English
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="tab-content">
                                            <div class="tab-pane fade show active" id="ann-ar" role="tabpanel">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold small text-secondary">{{ __('Title') }} · العربية <span class="text-danger">*</span></label>
                                                    <input class="form-control" name="title_ar" required maxlength="180" placeholder="اكتب عنوان الإعلان بالعربية..." style="border-radius: 10px;">
                                                </div>
                                                <div>
                                                    <label class="form-label fw-semibold small text-secondary">{{ __('Body') }} · العربية <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" name="body_ar" rows="3" required maxlength="20000" placeholder="اكتب تفاصيل ومحتوى الإعلان بالعربية..." style="border-radius: 10px;"></textarea>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="ann-en" role="tabpanel">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold small text-secondary">{{ __('Title') }} · English <span class="text-danger">*</span></label>
                                                    <input class="form-control" name="title_en" required maxlength="180" placeholder="Enter announcement title in English..." style="border-radius: 10px;">
                                                </div>
                                                <div>
                                                    <label class="form-label fw-semibold small text-secondary">{{ __('Body') }} · English <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" name="body_en" rows="3" required maxlength="20000" placeholder="Enter announcement content in English..." style="border-radius: 10px;"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Buttons --}}
                                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                    <button type="button" class="btn btn-light px-4" data-bs-toggle="collapse" data-bs-target="#newAnnouncementCollapse" style="border-radius: 10px;">
                                        {{ __('Cancel') }}
                                    </button>
                                    <button type="submit" class="btn btn-brand px-4" style="border-radius: 10px;">
                                        <i class="fa-solid fa-check me-1"></i>
                                        {{ __('Save') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endadminCan

                {{-- Announcements Table --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="min-width: 650px;">
                        <thead class="bg-light-subtle">
                            <tr class="text-secondary small fw-bold">
                                <th style="width: 70px;">#</th>
                                <th>{{ __('Title') }}</th>
                                <th style="width: 150px;">{{ __('Status') }}</th>
                                <th style="width: 170px;">{{ __('Date') }}</th>
                                <th style="width: 170px;">{{ __('Expires at') }}</th>
                                <th class="text-end" style="width: 150px;">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($announcements as $row)
                                <tr>
                                    <td class="fw-semibold text-secondary">#{{ $row->id }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $row->title ?? $row->reference }}</div>
                                        @if($row->home)
                                            <div class="small text-secondary"><i class="fa-solid fa-building me-1 text-muted"></i>{{ $row->home->name }}</div>
                                        @else
                                            <div class="small text-muted"><i class="fa-solid fa-earth-americas me-1"></i>{{ __('All homes') }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->is_published)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                                <i class="fa-solid fa-circle-check me-1"></i>{{ __('Published') }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill">
                                                <i class="fa-solid fa-clock me-1"></i>{{ __('Draft') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-secondary small text-nowrap">
                                        <i class="fa-regular fa-calendar me-1"></i>
                                        {{ $row->created_at ? $row->created_at->format('Y-m-d H:i') : '—' }}
                                    </td>
                                    <td class="text-secondary small text-nowrap">
                                        @if($row->expires_at)
                                            <i class="fa-regular fa-clock me-1"></i>
                                            {{ $row->expires_at->format('Y-m-d H:i') }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <div class="d-inline-flex gap-1 align-items-center">
                                            @adminCan('admin.content.announcements.edit')
                                            <a class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" href="{{ route('admin.content.announcements.edit', $row->id) }}" style="border-radius: 8px;">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                                <span>{{ __('Edit') }}</span>
                                            </a>
                                            @endadminCan
                                            @adminCan('admin.content.announcements.destroy','DELETE')
                                            @include('admin.partials.delete-button', ['action' => route('admin.content.announcements.destroy', $row->id)])
                                            @endadminCan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-secondary">
                                        <div class="mb-2"><i class="fa-solid fa-bullhorn fa-2x text-muted opacity-50"></i></div>
                                        <div class="fw-semibold">{{ __('No records') }}</div>
                                        <div class="small text-muted">{{ __('There are no announcements created yet.') }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($announcements->hasPages())
                    <div class="mt-4 pt-3 border-top">
                        {{ $announcements->appends(['tab' => 'announcements'])->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- TAB 2: INITIATIVES --}}
        <div class="tab-pane fade {{ $currentTab === 'initiatives' ? 'show active' : '' }}" id="tab-initiatives" role="tabpanel" aria-labelledby="tab-initiatives-btn">
            <div class="panel p-3 p-lg-4">
                {{-- Action Bar --}}
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 pb-3 mb-4 border-bottom">
                    <div>
                        <h2 class="h5 fw-bold mb-1 d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 36px; height: 36px; background: #fff8e6; color: #d97706;">
                                <i class="fa-solid fa-lightbulb" style="font-size: 0.95rem;"></i>
                            </span>
                            <span>{{ __('Community initiatives') }}</span>
                        </h2>
                        <p class="text-secondary small mb-0">{{ __('Manage community initiatives and resident participation.') }}</p>
                    </div>
                    @adminCan('admin.content.initiatives.store','POST')
                    <button class="btn btn-brand d-inline-flex align-items-center gap-2 shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#newInitiativeCollapse" aria-expanded="false" aria-controls="newInitiativeCollapse">
                        <i class="fa-solid fa-plus"></i>
                        <span>{{ __('Create initiative') }}</span>
                    </button>
                    @endadminCan
                </div>

                {{-- Collapsible Create Initiative Form --}}
                @adminCan('admin.content.initiatives.store','POST')
                <div class="collapse mb-4 {{ $errors->has('title_ar') || $errors->has('title_en') || $errors->has('description_ar') || $errors->has('description_en') ? 'show' : '' }}" id="newInitiativeCollapse">
                    <div class="card p-3 p-lg-4 border-0 shadow-sm" style="background: #fbfdfc; border-radius: 16px; border-top: 4px solid var(--orange, #fb822f) !important;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="h6 fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <i class="fa-solid fa-pen-to-square text-rounder-orange"></i>
                                <span>{{ __('Create initiative') }}</span>
                            </h3>
                            <button type="button" class="btn-close" data-bs-toggle="collapse" data-bs-target="#newInitiativeCollapse" aria-label="Close"></button>
                        </div>

                        <form method="post" action="{{ route('admin.content.initiatives.store') }}">
                            @csrf
                            <input type="hidden" name="tab" value="initiatives">
                            <div class="row g-3">
                                {{-- Target Home --}}
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold small text-secondary">
                                        <i class="fa-solid fa-building me-1 text-rounder-orange"></i>
                                        {{ __('Home') }} <span class="text-danger">*</span>
                                    </label>
                                    <select name="home_id" class="form-select" required style="border-radius: 10px;">
                                        <option value="">{{ __('Choose home') }}</option>
                                        @foreach($homes as $home)
                                            <option value="{{ $home->id }}">{{ $home->name }} · #{{ $home->id }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Target Amount --}}
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold small text-secondary">
                                        <i class="fa-solid fa-coins me-1 text-rounder-orange"></i>
                                        {{ __('Target amount') }} ({{ __('SAR') }})
                                    </label>
                                    <div class="input-group">
                                        <input type="number" step=".01" min="0" class="form-control" name="target_amount" placeholder="0.00" style="border-radius: 0 10px 10px 0;">
                                        <span class="input-group-text bg-light text-secondary small fw-bold" style="border-radius: 10px 0 0 10px;">ر.س</span>
                                    </div>
                                </div>

                                {{-- Publish Status --}}
                                <div class="col-12 col-md-4">
                                    <label class="form-label fw-semibold small text-secondary">
                                        <i class="fa-solid fa-satellite-dish me-1 text-rounder-orange"></i>
                                        {{ __('Publish') }} <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" name="is_published" required style="border-radius: 10px;">
                                        <option value="1">{{ __('Yes') }} ({{ __('Published') }})</option>
                                        <option value="0">{{ __('No') }} ({{ __('Draft') }})</option>
                                    </select>
                                </div>

                                {{-- Start Date & End Date --}}
                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold small text-secondary">
                                        <i class="fa-solid fa-calendar-plus me-1 text-rounder-orange"></i>
                                        {{ __('Starts at') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="datetime-local" class="form-control" name="starts_at" required style="border-radius: 10px;">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label fw-semibold small text-secondary">
                                        <i class="fa-solid fa-calendar-check me-1 text-rounder-orange"></i>
                                        {{ __('Ends at') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="datetime-local" class="form-control" name="ends_at" required style="border-radius: 10px;">
                                </div>

                                {{-- Bilingual Content Tabs --}}
                                <div class="col-12">
                                    <div class="bg-white p-3 rounded-3 border" style="border-color: #e5ece9 !important;">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="small fw-semibold text-secondary">
                                                <i class="fa-solid fa-language me-1 text-rounder-orange"></i>
                                                {{ __('Title') }} & {{ __('Description') }}
                                            </span>
                                            <ul class="nav nav-pills bilingual-nav-pills" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link active" id="init-ar-tab" data-bs-toggle="pill" data-bs-target="#init-ar" type="button" role="tab">
                                                        🇸🇦 العربية
                                                    </button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="init-en-tab" data-bs-toggle="pill" data-bs-target="#init-en" type="button" role="tab">
                                                        🇬🇧 English
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="tab-content">
                                            <div class="tab-pane fade show active" id="init-ar" role="tabpanel">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold small text-secondary">{{ __('Title') }} · العربية <span class="text-danger">*</span></label>
                                                    <input class="form-control" name="title_ar" required maxlength="180" placeholder="اكتب عنوان المبادرة بالعربية..." style="border-radius: 10px;">
                                                </div>
                                                <div>
                                                    <label class="form-label fw-semibold small text-secondary">{{ __('Description') }} · العربية <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" name="description_ar" rows="3" required maxlength="5000" placeholder="اكتب تفاصيل وأهداف المبادرة بالعربية..." style="border-radius: 10px;"></textarea>
                                                </div>
                                            </div>
                                            <div class="tab-pane fade" id="init-en" role="tabpanel">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold small text-secondary">{{ __('Title') }} · English <span class="text-danger">*</span></label>
                                                    <input class="form-control" name="title_en" required maxlength="180" placeholder="Enter initiative title in English..." style="border-radius: 10px;">
                                                </div>
                                                <div>
                                                    <label class="form-label fw-semibold small text-secondary">{{ __('Description') }} · English <span class="text-danger">*</span></label>
                                                    <textarea class="form-control" name="description_en" rows="3" required maxlength="5000" placeholder="Enter initiative details in English..." style="border-radius: 10px;"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Buttons --}}
                                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                    <button type="button" class="btn btn-light px-4" data-bs-toggle="collapse" data-bs-target="#newInitiativeCollapse" style="border-radius: 10px;">
                                        {{ __('Cancel') }}
                                    </button>
                                    <button type="submit" class="btn btn-brand px-4" style="border-radius: 10px;">
                                        <i class="fa-solid fa-check me-1"></i>
                                        {{ __('Save') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endadminCan

                {{-- Initiatives Table --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="min-width: 650px;">
                        <thead class="bg-light-subtle">
                            <tr class="text-secondary small fw-bold">
                                <th style="width: 70px;">#</th>
                                <th>{{ __('Title') }}</th>
                                <th style="width: 140px;">{{ __('Status') }}</th>
                                <th style="width: 160px;">{{ __('Target amount') }}</th>
                                <th style="width: 220px;">{{ __('Duration') }}</th>
                                <th class="text-end" style="width: 150px;">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($initiatives as $row)
                                <tr>
                                    <td class="fw-semibold text-secondary">#{{ $row->id }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $row->title ?? $row->reference }}</div>
                                        @if($row->home)
                                            <div class="small text-secondary"><i class="fa-solid fa-building me-1 text-muted"></i>{{ $row->home->name }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->is_published)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                                <i class="fa-solid fa-circle-check me-1"></i>{{ __('Published') }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill">
                                                <i class="fa-solid fa-clock me-1"></i>{{ __('Draft') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        @if($row->target_amount)
                                            <span class="fw-bold text-dark">{{ number_format($row->target_amount, 2) }}</span>
                                            <span class="small text-secondary">ر.س</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-secondary small text-nowrap">
                                        <div><i class="fa-regular fa-calendar-plus me-1 text-muted"></i>{{ $row->starts_at ? $row->starts_at->format('Y-m-d H:i') : '—' }}</div>
                                        <div><i class="fa-regular fa-calendar-check me-1 text-muted"></i>{{ $row->ends_at ? $row->ends_at->format('Y-m-d H:i') : '—' }}</div>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <div class="d-inline-flex gap-1 align-items-center">
                                            @adminCan('admin.content.initiatives.edit')
                                            <a class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" href="{{ route('admin.content.initiatives.edit', $row->id) }}" style="border-radius: 8px;">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                                <span>{{ __('Edit') }}</span>
                                            </a>
                                            @endadminCan
                                            @adminCan('admin.content.initiatives.destroy','DELETE')
                                            @include('admin.partials.delete-button', ['action' => route('admin.content.initiatives.destroy', $row->id)])
                                            @endadminCan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-secondary">
                                        <div class="mb-2"><i class="fa-solid fa-lightbulb fa-2x text-muted opacity-50"></i></div>
                                        <div class="fw-semibold">{{ __('No records') }}</div>
                                        <div class="small text-muted">{{ __('There are no initiatives created yet.') }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($initiatives->hasPages())
                    <div class="mt-4 pt-3 border-top">
                        {{ $initiatives->appends(['tab' => 'initiatives'])->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- TAB 3: FACILITY REPORTS --}}
        <div class="tab-pane fade {{ $currentTab === 'reports' ? 'show active' : '' }}" id="tab-reports" role="tabpanel" aria-labelledby="tab-reports-btn">
            <div class="panel p-3 p-lg-4">
                {{-- Header --}}
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 pb-3 mb-4 border-bottom">
                    <div>
                        <h2 class="h5 fw-bold mb-1 d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 36px; height: 36px; background: #e8f4f1; color: #0e3c36;">
                                <i class="fa-solid fa-screwdriver-wrench" style="font-size: 0.95rem;"></i>
                            </span>
                            <span>{{ __('Resident facility reports') }}</span>
                        </h2>
                        <p class="text-secondary small mb-0">{{ __('Manage facility maintenance and incident reports submitted by residents.') }}</p>
                    </div>
                </div>

                {{-- Facility Reports Table --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="min-width: 650px;">
                        <thead class="bg-light-subtle">
                            <tr class="text-secondary small fw-bold">
                                <th style="width: 70px;">#</th>
                                <th style="width: 160px;">{{ __('Reference') }}</th>
                                <th>{{ __('Title') }}</th>
                                <th style="width: 160px;">{{ __('Status') }}</th>
                                <th style="width: 170px;">{{ __('Date') }}</th>
                                <th class="text-end" style="width: 160px;">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                                <tr>
                                    <td class="fw-semibold text-secondary">#{{ $report->id }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1 font-monospace">
                                            {{ $report->reference }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $report->title }}</div>
                                        @if($report->location)
                                            <div class="small text-secondary"><i class="fa-solid fa-location-dot me-1 text-danger"></i>{{ $report->location }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                                            <i class="fa-solid fa-circle-dot me-1"></i>
                                            {{ $report->status_name ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="text-secondary small text-nowrap">
                                        <i class="fa-regular fa-calendar me-1"></i>
                                        {{ $report->reported_at ? $report->reported_at->format('Y-m-d H:i') : '—' }}
                                    </td>
                                    <td class="text-end text-nowrap">
                                        @adminCan('admin.content.reports.update','PUT')
                                        <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#reportModal_{{ $report->id }}" style="border-radius: 8px;">
                                            <i class="fa-solid fa-arrows-rotate"></i>
                                            <span>{{ __('Update status') }}</span>
                                        </button>

                                        {{-- Update Status Modal --}}
                                        <div class="modal fade" id="reportModal_{{ $report->id }}" tabindex="-1" aria-hidden="true" style="text-align: right;">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                                                            <i class="fa-solid fa-screwdriver-wrench text-rounder-orange"></i>
                                                            <span>{{ __('Update status') }} · {{ $report->reference }}</span>
                                                        </h5>
                                                        <button type="button" class="btn-close ms-0 me-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="post" action="{{ route('admin.content.reports.update', $report->id) }}">
                                                        @csrf
                                                        @method('put')
                                                        <input type="hidden" name="tab" value="reports">
                                                        <div class="modal-body pt-3">
                                                            <div class="p-3 rounded-3 mb-3 text-start" style="background: #f8faf9; text-align: right;">
                                                                <div class="small text-secondary">{{ __('Title') }}</div>
                                                                <div class="fw-semibold text-dark">{{ $report->title }}</div>
                                                                @if($report->description)
                                                                    <div class="small text-muted mt-1">{{ Str::limit($report->description, 120) }}</div>
                                                                @endif
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold small text-secondary">{{ __('Status') }}</label>
                                                                <select name="status_id" class="form-select" required style="border-radius: 10px;">
                                                                    @foreach($statuses as $status)
                                                                        <option value="{{ $status->id }}" @selected($report->status_id === $status->id)>
                                                                            {{ $status->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label fw-semibold small text-secondary">{{ __('Internal note') }}</label>
                                                                <textarea class="form-control" name="note" rows="3" maxlength="2000" placeholder="{{ __('Internal note') }}..." style="border-radius: 10px;"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-0 pt-0">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: 10px;">{{ __('Cancel') }}</button>
                                                            <button type="submit" class="btn btn-brand px-4" style="border-radius: 10px;">
                                                                <i class="fa-solid fa-check me-1"></i>
                                                                {{ __('Save') }}
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        @endadminCan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-secondary">
                                        <div class="mb-2"><i class="fa-solid fa-screwdriver-wrench fa-2x text-muted opacity-50"></i></div>
                                        <div class="fw-semibold">{{ __('No records') }}</div>
                                        <div class="small text-muted">{{ __('There are no facility reports submitted yet.') }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($reports->hasPages())
                    <div class="mt-4 pt-3 border-top">
                        {{ $reports->appends(['tab' => 'reports'])->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabButtons = document.querySelectorAll('#residentContentTabs button[data-bs-toggle="pill"]');
    tabButtons.forEach(function (btn) {
        btn.addEventListener('shown.bs.tab', function (e) {
            const targetId = e.target.getAttribute('data-bs-target');
            let tabName = 'announcements';
            if (targetId === '#tab-initiatives') tabName = 'initiatives';
            else if (targetId === '#tab-reports') tabName = 'reports';

            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.replaceState({}, '', url);
        });
    });

    const hash = window.location.hash;
    if (hash === '#initiatives') {
        const btn = document.getElementById('tab-initiatives-btn');
        if (btn) bootstrap.Tab.getOrCreateInstance(btn).show();
    } else if (hash === '#reports') {
        const btn = document.getElementById('tab-reports-btn');
        if (btn) bootstrap.Tab.getOrCreateInstance(btn).show();
    }
});
</script>
@endsection
