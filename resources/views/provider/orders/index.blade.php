@extends('layouts.provider')
@section('title', __('Service orders'))
@section('body')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <h1 class="h3 fw-bold mb-0">{{ __('Service orders') }}</h1>
    @if(!empty($visibleStatuses) && $visibleStatuses->isNotEmpty())
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('provider.orders.index') }}" class="btn btn-sm {{ empty($selectedStatus) ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ __('All') }}
            </a>
            @foreach($visibleStatuses as $st)
                <a href="{{ route('provider.orders.index', ['status' => $st->code]) }}" class="btn btn-sm {{ ($selectedStatus ?? null) === $st->code ? 'btn-primary' : 'btn-outline-secondary' }}">
                    {{ $st->translations->firstWhere('locale', app()->getLocale())?->name ?? $st->code }}
                </a>
            @endforeach
        </div>
    @endif
</div>
<section class="panel p-3 p-lg-4">
    @include('provider.orders.table', ['orders' => $orders])
    <div class="mt-3">{{ $orders->links() }}</div>
</section>
@endsection
