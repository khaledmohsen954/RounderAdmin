@extends('layouts.provider')
@section('title', __('Ratings'))
@section('body')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">{{ __('Ratings') }}</h1>
        <p class="text-secondary mb-0">{{ __('Resident ratings for your completed service orders.') }}</p>
    </div>
    <div class="panel px-4 py-3 text-center">
        <div class="text-secondary small">{{ __('Average rating') }}</div>
        <div class="h3 fw-bold mb-0">{{ $ratingsCount ? number_format($averageScore, 1) : '—' }} <span class="text-warning">★</span></div>
        <div class="small text-secondary">{{ __('Ratings count: :count', ['count' => $ratingsCount]) }}</div>
    </div>
</div>
<div class="row g-3">
    @forelse($ratings as $rating)
        <div class="col-12">
            <article class="panel p-3 p-lg-4">
                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <div class="fw-bold">{{ $rating->resident?->name ?? __('Resident') }}</div>
                    <div class="text-warning" aria-label="{{ __('Rating') }}: {{ $rating->score }} / 5">
                        @for($star = 1; $star <= 5; $star++){{ $star <= $rating->score ? '★' : '☆' }}@endfor
                        <span class="text-secondary small ms-1">{{ $rating->score }}/5</span>
                    </div>
                </div>
                <div class="small text-secondary mt-1">
                    {{ $rating->booking?->service?->translations?->firstWhere('locale', app()->getLocale())?->name ?? $rating->booking?->service?->code ?? __('Service order') }}
                    · #{{ $rating->booking_id }}
                    · {{ $rating->created_at?->translatedFormat('Y-m-d') }}
                </div>
                @if($rating->comment)<p class="mb-0 mt-3">{{ $rating->comment }}</p>@endif
                @if($rating->tags)
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        @foreach($rating->tags as $tag)<span class="badge rounded-pill text-bg-light">{{ is_string($tag) ? $tag : json_encode($tag) }}</span>@endforeach
                    </div>
                @endif
            </article>
        </div>
    @empty
        <div class="col-12"><div class="panel text-center text-secondary p-5">{{ __('No ratings have been submitted yet.') }}</div></div>
    @endforelse
</div>
<div class="mt-3">{{ $ratings->links() }}</div>
@endsection
