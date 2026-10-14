@extends('layouts.admin')
@section('title',__('Dashboard').' · Rounder')
@section('body')
<div class="container-fluid"><div class="row g-3 p-3 p-lg-4">
<aside class="col-md-3 col-xl-2"><div class="sidebar p-3"><div class="text-white-50 small px-3 py-2">{{ __('Overview') }}</div><a class="active" href="{{ route('admin.dashboard') }}">▦ &nbsp;{{ __('Dashboard') }}</a><a href="{{ route('admin.users.index') }}">♙ &nbsp;{{ __('Users') }}</a><a href="{{ route('admin.homes.index') }}">⌂ &nbsp;{{ __('Homes') }}</a><a href="{{ route('admin.providers.index') }}">⚒ &nbsp;{{ __('Providers and contracts') }}</a><a href="{{ route('admin.packages.index') }}">◫ &nbsp;{{ __('Packages and services') }}</a><a href="{{ route('admin.content.index') }}">◈ &nbsp;{{ __('Resident operations') }}</a><a href="{{ route('admin.about') }}">ⓘ &nbsp;{{ __('About Rounder') }}</a></div></aside>
<main class="col-md-9 col-xl-10 content">
<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4"><div><div class="small text-secondary">{{ __('Live metrics') }}</div><h1 class="h3 fw-bold mb-1">{{ __('Dashboard') }}</h1><p class="text-secondary mb-0">{{ __('Metrics are queried from MySQL records.') }}</p></div><span class="pill">{{ now()->translatedFormat('d F Y') }}</span></div>
<div class="row g-3 mb-4">
@foreach([['Residents',$stats['residents'],'people'],['Approved providers',$stats['providers'],'verified_user'],['All bookings',$stats['bookings'],'event_note'],['Pending bookings',$stats['pending_bookings'],'pending_actions'],['Collected payments',number_format($stats['revenue'],2),'payments'],['Linked homes',$stats['linked_homes'],'home'],['Open facility reports',$stats['open_reports'],'build'],['Community posts',$stats['community_posts'],'forum'],['Active initiatives',$stats['active_initiatives'],'campaign']] as [$label,$value,$icon])
<div class="col-sm-6 col-xl"><div class="metric"><div class="d-flex justify-content-between"><span class="text-secondary">{{ __($label) }}</span><span class="mark">●</span></div><div class="value mt-2">{{ $value }}</div></div></div>
@endforeach</div>
<div class="panel p-3 p-lg-4 mb-4"><h2 class="h5 fw-bold mb-3">{{ __('Recent bookings') }}</h2><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>#</th><th>{{ __('Resident') }}</th><th>{{ __('Service') }}</th><th>{{ __('Status') }}</th><th>{{ __('Date') }}</th></tr></thead><tbody>
@forelse($recent as $booking)<tr><td>{{ $booking->id }}</td><td>{{ $booking->resident->name }}</td><td>{{ $booking->service->translations->firstWhere('locale',app()->getLocale())?->name ?? $booking->service->code }}</td><td><span class="pill">{{ $booking->status->translations->firstWhere('locale',app()->getLocale())?->name ?? $booking->status->code }}</span></td><td>{{ $booking->created_at->format('Y-m-d') }}</td></tr>
@empty<tr><td colspan="5" class="text-center text-secondary py-4">{{ __('No bookings yet') }}</td></tr>@endforelse</tbody></table></div></div>
</main></div></div>
@endsection
