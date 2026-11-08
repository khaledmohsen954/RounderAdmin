@extends('layouts.provider')
@section('title', __('Provider contract'))
@section('body')
<div class="row justify-content-center py-4">
    <div class="col-12 col-lg-9">
        <section class="panel p-4 p-lg-5">
            <div class="mb-4">
                <div class="text-secondary">{{ __('Contract reference') }}: {{ $contract->reference }}</div>
                <h1 class="h3 fw-bold mt-2">{{ __('Provider contract') }}</h1>
                <p class="text-secondary mb-0">{{ $profile->contract_accepted_at ? __('Your contract has been accepted.') : __('Review the contract and accept it to access the provider dashboard.') }}</p>
            </div>
            @if($terms)
                <div class="border rounded-3 p-3 p-lg-4 mb-4">{!! $terms !!}</div>
                <div class="d-flex flex-wrap gap-2 d-print-none mb-4">
                    <a class="btn btn-primary" href="{{ route('provider.contract.pdf') }}"><i class="fa-solid fa-file-pdf me-1"></i>{{ __('Download contract PDF') }}</a>
                    <button class="btn btn-outline-secondary" type="button" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>{{ __('Print contract') }}</button>
                </div>
                @unless($profile->contract_accepted_at)
                    <form method="post" action="{{ route('provider.contract.accept') }}" class="d-print-none">
                        @csrf
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="accept-terms" name="accept_contract" value="1" required>
                            <label class="form-check-label" for="accept-terms">{{ __('I confirm that I have read and agree to the contract terms.') }}</label>
                        </div>
                        <button class="btn btn-orange" type="submit">{{ __('Accept contract and continue') }}</button>
                    </form>
                @endunless
            @else
                <div class="alert alert-warning mb-0">{{ __('The contract terms are not available yet.') }}</div>
            @endif
        </section>
    </div>
</div>
@endsection
