@extends('layouts.admin')
@section('title', __('Emergency requests') . ' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-danger" style="font-size: 1.4rem;"></i>
                <span>{{ __('Emergency requests') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Urgent maintenance and safety incidents requiring immediate dispatch.') }}</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="pill px-3 py-2 fw-semibold" style="font-size: 0.88rem;">
                <i class="fa-solid fa-triangle-exclamation me-1 text-danger"></i>
                {{ __('Total requests') }}: <strong class="text-dark">{{ $requests->total() }}</strong>
            </span>
            @adminCan('admin.emergencies.create')
            <a class="btn btn-brand d-inline-flex align-items-center gap-2 shadow-sm" href="{{ route('admin.emergencies.create') }}" style="border-radius: 10px;">
                <i class="fa-solid fa-plus"></i>
                <span>{{ __('Create emergency request') }}</span>
            </a>
            @endadminCan
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Main Panel --}}
    <div class="panel p-3 p-lg-4 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 900px;">
                <thead class="bg-light-subtle">
                    <tr class="text-secondary small fw-bold">
                        <th style="width: 140px;">{{ __('Request') }}</th>
                        <th>{{ __('Resident and home') }}</th>
                        <th style="width: 150px;">{{ __('Emergency type') }}</th>
                        <th style="width: 220px;">{{ __('Location and details') }}</th>
                        <th style="width: 140px;">{{ __('Status') }}</th>
                        <th style="min-width: 320px;">{{ __('Dispatch and commission') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $item)
                        @php
                            $statusStyle = match($item->status_code) {
                                'resolved' => ['bg' => 'bg-success-subtle text-success border border-success-subtle', 'icon' => 'fa-circle-check'],
                                'in_progress', 'dispatched' => ['bg' => 'bg-info-subtle text-info-emphasis border border-info-subtle', 'icon' => 'fa-truck-fast'],
                                'cancelled' => ['bg' => 'bg-secondary-subtle text-secondary border border-secondary-subtle', 'icon' => 'fa-ban'],
                                default => ['bg' => 'bg-danger-subtle text-danger border border-danger-subtle', 'icon' => 'fa-clock'],
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold text-dark font-monospace">#{{ $item->reference }}</div>
                                <div class="small text-muted font-monospace" style="font-size: 0.78rem;">
                                    <i class="fa-regular fa-clock me-1"></i>{{ $item->requested_at }}
                                </div>
                                @if($item->resident_phone)
                                    <div class="small text-secondary font-monospace mt-1" dir="ltr">
                                        <i class="fa-solid fa-phone text-muted me-1"></i>{{ $item->resident_phone }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark d-flex align-items-center gap-1">
                                    <i class="fa-solid fa-user-circle text-muted"></i>
                                    <span>{{ $item->resident_name }}</span>
                                </div>
                                <div class="small text-secondary mt-1">
                                    <i class="fa-solid fa-building text-muted me-1"></i>
                                    {{ $item->home_name }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill fw-semibold">
                                    <i class="fa-solid fa-bolt me-1"></i>{{ $item->type_name }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark small">
                                    <i class="fa-solid fa-location-dot text-danger me-1"></i>
                                    {{ $item->location_details }}
                                </div>
                                @if($item->description)
                                    <div class="small text-muted text-truncate mt-1" style="max-width: 220px;" title="{{ $item->description }}">
                                        {{ $item->description }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $statusStyle['bg'] }} px-2 py-1 rounded-pill mb-1">
                                    <i class="fa-solid {{ $statusStyle['icon'] }} me-1"></i>{{ $item->status_name }}
                                </span>
                                @if($item->provider_name)
                                    <div class="small text-secondary fw-semibold mt-1">
                                        <i class="fa-solid fa-user-tie text-muted me-1"></i>
                                        {{ $item->provider_name }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="p-2 rounded-3 border bg-light-subtle">
                                    @adminCan('admin.emergencies.update', 'PUT')
                                    <form method="post" action="{{ route('admin.emergencies.update', $item->id) }}" class="d-grid gap-2">
                                        @csrf
                                        @method('PUT')
                                        <div class="row g-1">
                                            <div class="col-12 col-sm-6">
                                                <select class="form-select form-select-sm provider-select" name="provider_profile_id" style="border-radius: 8px;">
                                                    <option value="">{{ __('Choose provider') }}</option>
                                                    @foreach($providers as $provider)
                                                        <option value="{{ $provider->id }}" @selected($item->provider_name === $provider->company_name)>
                                                            {{ $provider->company_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <select class="form-select form-select-sm emergency-status" name="status_id" required style="border-radius: 8px;">
                                                    @foreach($statuses as $status)
                                                        <option value="{{ $status->id }}" data-code="{{ $status->code }}" @selected($item->status_code === $status->code)>
                                                            {{ $status->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <input type="number" step=".01" min="0" max="9999999999.99" name="gross_amount" class="form-control form-control-sm gross-amount font-monospace" placeholder="{{ __('Final amount for commission') }}" style="border-radius: 8px;">
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <input name="note" maxlength="2000" class="form-control form-control-sm" placeholder="{{ __('Internal note') }}" style="border-radius: 8px;">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center gap-1 mt-1">
                                            <button class="btn btn-sm btn-brand px-3 py-1 shadow-sm d-inline-flex align-items-center gap-1" style="border-radius: 8px;">
                                                <i class="fa-solid fa-check"></i>
                                                <span>{{ __('Save dispatch update') }}</span>
                                            </button>
                                            @adminCan('admin.emergencies.destroy', 'DELETE')
                                            @include('admin.partials.delete-button', ['action' => route('admin.emergencies.destroy', $item->id)])
                                            @endadminCan
                                        </div>
                                    </form>
                                    @endadminCan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-secondary">
                                <div class="mb-2"><i class="fa-solid fa-triangle-exclamation fa-2x text-muted opacity-50"></i></div>
                                <div class="fw-semibold">{{ __('No emergency requests found') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="mt-4 pt-3 border-top">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</main>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.emergency-status').forEach((select) => {
    const form = select.closest('form');
    const amount = form.querySelector('.gross-amount');
    const sync = () => {
        const code = select.options[select.selectedIndex]?.dataset.code;
        amount.required = code === 'resolved';
        amount.placeholder = amount.required ? @json(__('Final amount for commission')) : @json(__('Optional final amount'));
    };
    select.addEventListener('change', sync);
    sync();
});
</script>
@endpush
