@extends('layouts.admin')
@section('title',__('About Rounder'))
@section('body')
<div class="container-fluid"><div class="row g-3 p-3 p-lg-4">
<main class="col-12 content"><section class="panel p-4 p-lg-5" style="background:linear-gradient(125deg,#073d43,#0b6265);color:white"><div class="text-uppercase small mb-3" style="color:#f4dfbf">ROUND. LIVE. BETTER.</div><h1 class="display-5 fw-bold">{{ __('Property services, made simple') }}</h1><p class="lead col-lg-8">{{ __('Rounder connects residents, communities and trusted service providers in one place.') }}</p></section>
<div class="row g-3 mt-1"><div class="col-lg-4"><div class="panel p-4 h-100"><div class="pill mb-3">{{ __('Resident mobile app') }}</div><h2 class="h5">{{ __('Resident services') }}</h2><p class="text-secondary">{{ __('Resident app description') }}</p></div></div><div class="col-lg-4"><div class="panel p-4 h-100"><div class="pill mb-3">{{ __('API platform') }}</div><h2 class="h5">Laravel REST API</h2><p class="text-secondary">{{ __('API description') }}</p></div></div><div class="col-lg-4"><div class="panel p-4 h-100"><div class="pill mb-3">{{ __('Live metrics') }}</div><h2 class="h5">Rounder Admin</h2><p class="text-secondary">{{ __('Admin description') }}</p></div></div></div></main></div></div>
@endsection
