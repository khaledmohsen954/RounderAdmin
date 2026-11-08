@extends('layouts.provider')
@section('title', __('Emergency requests'))
@section('body')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <h1 class="h3 fw-bold mb-0">{{ __('Emergency requests') }}</h1>
    @if(!empty($visibleStatuses) && $visibleStatuses->isNotEmpty())
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('provider.emergencies.index') }}" class="btn btn-sm {{ empty($selectedStatus) ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ __('All') }}
            </a>
            @foreach($visibleStatuses as $st)
                <a href="{{ route('provider.emergencies.index', ['status' => $st->code]) }}" class="btn btn-sm {{ ($selectedStatus ?? null) === $st->code ? 'btn-primary' : 'btn-outline-secondary' }}">
                    {{ $st->translations->firstWhere('locale', app()->getLocale())?->name ?? $st->code }}
                </a>
            @endforeach
        </div>
    @endif
</div>
<section class="panel p-3 p-lg-4">
    @include('provider.emergencies.table', ['emergencies' => $emergencies])
    <div class="mt-3">{{ $emergencies->links() }}</div>
</section>
@endsection
