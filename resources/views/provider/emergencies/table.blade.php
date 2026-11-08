<div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead><tr><th>{{ __('Reference') }}</th><th>{{ __('Emergency type') }}</th><th>{{ __('Property') }}</th><th>{{ __('Status') }}</th><th>{{ __('Requested at') }}</th></tr></thead>
        <tbody>
        @forelse($emergencies as $assignment)
            @php($emergency = $assignment->emergencyRequest)
            <tr>
                <td class="fw-semibold">{{ $emergency?->reference ?? '—' }}</td>
                <td>{{ $emergency?->emergencyType?->translations?->firstWhere('locale', app()->getLocale())?->name ?? $emergency?->emergencyType?->translations?->firstWhere('locale', 'ar')?->name ?? '—' }}</td>
                <td>
                    {{ $emergency?->home?->name ?? '—' }}
                    @if($emergency?->home?->city)
                        <div class="small text-secondary">{{ $emergency->home->city->translations->firstWhere('locale', app()->getLocale())?->name ?? $emergency->home->city->code }}</div>
                    @endif
                </td>
                <td>{{ $emergency?->status?->translations?->firstWhere('locale', app()->getLocale())?->name ?? $emergency?->status?->code ?? '—' }}</td>
                <td>{{ $emergency?->requested_at?->format('Y-m-d H:i') ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-secondary py-4">{{ __('No emergency requests are assigned to your account.') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
