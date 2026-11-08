@extends('layouts.admin')
@section('title',__('About Rounder'))
@section('body')
<div class="container-fluid"><div class="row g-3 p-3 p-lg-4">
<main class="col-12 content">
<section class="panel p-4 p-lg-5" style="background:linear-gradient(135deg, #02333E 0%, #024350 50%, #01242C 100%); color:white; border:none; box-shadow:0 8px 30px rgba(2,51,62,0.15);">
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="badge px-3 py-2 fw-bold" style="background:rgba(251,130,47,0.18); color:#FB822F; border:1px solid rgba(251,130,47,0.3); font-size:0.75rem; letter-spacing:0.08em;">ROUND. LIVE. BETTER.</span>
    </div>
    <h1 class="display-6 fw-bold mb-3">{{ __('Property services, made simple') }}</h1>
    <p class="lead col-lg-8 text-light opacity-90 mb-0" style="font-size:1.1rem; line-height:1.7;">{{ __('Rounder connects residents, communities and trusted service providers in one place.') }}</p>
</section>
<div class="row g-3 mt-1">
    <div class="col-lg-4">
        <div class="panel p-4 h-100">
            <div class="pill mb-3"><i class="fa-solid fa-mobile-screen me-1 text-rounder-orange"></i>{{ __('Resident mobile app') }}</div>
            <h2 class="h5 fw-bold mb-2">{{ __('Resident services') }}</h2>
            <p class="text-secondary mb-0">{{ __('Resident app description') }}</p>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="panel p-4 h-100">
            <div class="pill mb-3"><i class="fa-solid fa-server me-1 text-rounder-orange"></i>{{ __('API platform') }}</div>
            <h2 class="h5 fw-bold mb-2">Laravel REST API</h2>
            <p class="text-secondary mb-0">{{ __('API description') }}</p>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="panel p-4 h-100">
            <div class="pill mb-3"><i class="fa-solid fa-chart-line me-1 text-rounder-orange"></i>{{ __('Live metrics') }}</div>
            <h2 class="h5 fw-bold mb-2">Rounder Admin</h2>
            <p class="text-secondary mb-0">{{ __('Admin description') }}</p>
        </div>
    </div>
</div>
</main></div></div>
@endsection
