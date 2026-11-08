@extends('layouts.provider')
@section('title', __('Order').' #'.$booking->id)
@section('body')
@php
    $currentStatus = $booking->status->code;
    $nextStatuses = $availableNextStatuses ?? collect();
    $nextStatus = $nextStatuses->first()?->code;
    $statusLabels = [
        'confirmed' => __('Accept order'),
        'in_progress' => __('Start work'),
        'completed' => __('Complete order'),
    ];
@endphp
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><a href="{{ route('provider.orders.index') }}" class="text-decoration-none">{{ __('Service orders') }}</a><h1 class="h3 fw-bold mt-2">{{ __('Order') }} #{{ $booking->id }}</h1></div>
    <span class="badge text-bg-light fs-6">{{ $booking->status->translations->firstWhere('locale', app()->getLocale())?->name ?? $currentStatus }}</span>
</div>
<div class="row g-3">
    <div class="col-lg-7">
        <section class="panel p-3 p-lg-4 mb-3">
            <h2 class="h5 fw-bold">{{ __('Order details') }}</h2>
            <dl class="row mb-0">
                <dt class="col-sm-4">{{ __('Resident') }}</dt><dd class="col-sm-8">{{ $booking->resident->name }}</dd>
                <dt class="col-sm-4">{{ __('Property') }}</dt><dd class="col-sm-8">{{ $booking->home->name }} · {{ $booking->residentHome?->unit_label }}</dd>
                <dt class="col-sm-4">{{ __('Service') }}</dt><dd class="col-sm-8">{{ $booking->service->translations->firstWhere('locale', app()->getLocale())?->name ?? $booking->service->code }}</dd>
                <dt class="col-sm-4">{{ __('Scheduled at') }}</dt><dd class="col-sm-8">{{ $booking->scheduled_at?->translatedFormat('Y-m-d H:i') }}</dd>
                <dt class="col-sm-4">{{ __('Total') }}</dt><dd class="col-sm-8">{{ number_format((float) $booking->total_amount, 2) }}</dd>
                <dt class="col-sm-4">{{ __('Notes') }}</dt><dd class="col-sm-8">{{ $booking->notes ?: '—' }}</dd>
            </dl>
        </section>
        @if($booking->bookingMedia->isNotEmpty())
            <section class="panel p-3 p-lg-4">
                <h2 class="h5 fw-bold">{{ __('Photos') }}</h2>
                <div class="d-flex flex-wrap gap-2">@foreach($booking->bookingMedia as $media)<a href="{{ $media->url }}" target="_blank" rel="noopener"><img src="{{ $media->url }}" alt="" style="width:100px;height:100px;object-fit:cover;border-radius:10px"></a>@endforeach</div>
            </section>
        @endif
    </div>
    <div class="col-lg-5">
        @if($nextStatus && app(\App\Modules\Providers\Services\ProviderPortalService::class)->allows(auth('provider')->user(), app(\App\Modules\Providers\Services\ProviderPortalService::class)->profileFor(auth('provider')->user()), 'orders.manage'))
            <section class="panel p-3 p-lg-4 mb-3">
                <h2 class="h5 fw-bold">{{ $statusLabels[$nextStatus] ?? __('Update status') }}</h2>
                <form method="post" action="{{ route('provider.orders.update', $booking->id) }}" id="providerOrderTransitionForm">
                    @csrf @method('put')
                    @if($nextStatuses->count() > 1)
                        <label class="form-label" for="provider_next_status">{{ __('Status') }}</label>
                        <select class="form-select mb-3" id="provider_next_status" name="status" onchange="document.getElementById('grossAmountWrap').style.display = (this.value === 'completed' ? 'block' : 'none');">
                            @foreach($nextStatuses as $st)
                                <option value="{{ $st->code }}" @selected(old('status', $nextStatus) === $st->code)>
                                    {{ $st->translations->firstWhere('locale', app()->getLocale())?->name ?? ($statusLabels[$st->code] ?? $st->code) }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input type="hidden" name="status" value="{{ $nextStatus }}">
                    @endif
                    <div id="grossAmountWrap" style="display: {{ old('status', $nextStatus) === 'completed' ? 'block' : 'none' }};">
                        <label class="form-label" for="gross_amount">{{ __('Final amount') }}</label>
                        <input class="form-control mb-3" id="gross_amount" name="gross_amount" type="number" min="0" step="0.01" value="{{ old('gross_amount', $booking->total_amount) }}">
                    </div>
                    <label class="form-label" for="note">{{ __('Note') }}</label>
                    <textarea class="form-control mb-3" id="note" name="note" maxlength="2000" rows="3">{{ old('note') }}</textarea>
                    <button class="btn btn-primary" type="submit">{{ $statusLabels[$nextStatus] ?? __('Update status') }}</button>
                </form>
            </section>
        @endif
        <section class="panel p-3 p-lg-4">
            <h2 class="h5 fw-bold">{{ __('Order history') }}</h2>
            @forelse($booking->bookingStatusHistory as $history)
                <div class="border-bottom py-2">
                    <strong>{{ $history->status->translations->firstWhere('locale', app()->getLocale())?->name ?? $history->status->code }}</strong>
                    <div class="small text-secondary">{{ $history->created_at?->translatedFormat('Y-m-d H:i') }}</div>
                    @if($history->note)<div>{{ $history->note }}</div>@endif
                </div>
            @empty
                <p class="text-secondary mb-0">{{ __('No history available.') }}</p>
            @endforelse
        </section>
    </div>
</div>
@endsection
