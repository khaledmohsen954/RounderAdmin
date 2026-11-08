@extends('layouts.provider')
@section('title', __('Settings / Profile'))
@section('body')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-1">{{ __('Settings / Profile') }}</h1>
    <p class="text-secondary mb-0">{{ __('Update your company profile and linked services.') }}</p>
</div>
<section class="panel p-3 p-lg-4 mb-4">
    <h2 class="h5 fw-bold mb-3">{{ __('Provider profile') }}</h2>
    <form method="post" action="{{ route('provider.settings.update') }}">
        @csrf @method('put')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="company_name">{{ __('Company name') }}</label>
                <input class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name', $profile->company_name) }}" maxlength="160" required>
                @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label" for="trade_name">{{ __('Trade name') }}</label>
                <input class="form-control" id="trade_name" name="trade_name" value="{{ old('trade_name', $profile->trade_name) }}" maxlength="160">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="provider_type">{{ __('Provider type') }}</label>
                <input class="form-control" id="provider_type" name="provider_type" value="{{ old('provider_type', $profile->provider_type) }}" maxlength="80">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="years_of_experience">{{ __('Years of experience') }}</label>
                <input class="form-control" type="number" id="years_of_experience" name="years_of_experience" min="0" max="100" value="{{ old('years_of_experience', $profile->years_of_experience) }}">
            </div>
            <div class="col-12"><button class="btn btn-primary" type="submit">{{ __('Save changes') }}</button></div>
        </div>
    </form>
</section>
<section class="panel p-3 p-lg-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h2 class="h5 fw-bold mb-1">{{ __('Linked services') }}</h2>
            <p class="small text-secondary mb-0">{{ __('Services with an active order cannot be removed.') }}</p>
        </div>
        @if($availableServices->isNotEmpty())
            <form method="post" action="{{ route('provider.settings.services.store') }}" class="d-flex gap-2">
                @csrf
                <select class="form-select" name="service_id" required>
                    <option value="">{{ __('Choose a service') }}</option>
                    @foreach($availableServices as $service)
                        <option value="{{ $service->id }}">{{ $service->translations->firstWhere('locale', app()->getLocale())?->name ?? $service->code }}</option>
                    @endforeach
                </select>
                <button class="btn btn-orange text-nowrap" type="submit">{{ __('Add service') }}</button>
            </form>
        @endif
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>{{ __('Service') }}</th><th>{{ __('Status') }}</th><th>{{ __('Actions') }}</th></tr></thead>
            <tbody>
            @forelse($services as $service)
                <tr>
                    <td>{{ $service->translations->firstWhere('locale', app()->getLocale())?->name ?? $service->code }}</td>
                    <td>{{ isset($ordersByService[$service->id]) ? __('Linked to orders') : __('No linked orders') }}</td>
                    <td>
                        @if(isset($ordersByService[$service->id]))
                            <span class="text-secondary">{{ __('Cannot remove') }}</span>
                        @else
                            <form method="post" action="{{ route('provider.settings.services.destroy', $service->id) }}" onsubmit="return confirm('{{ __('Remove this service from your profile?') }}')">
                                @csrf @method('delete')
                                <button class="btn btn-sm btn-outline-danger" type="submit">{{ __('Remove service') }}</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center text-secondary py-4">{{ __('No services are linked to your provider profile.') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
