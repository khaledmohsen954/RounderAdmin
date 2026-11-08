<div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead><tr><th>{{ __('Order') }}</th><th>{{ __('Resident and home') }}</th><th>{{ __('Service') }}</th><th>{{ __('Scheduled at') }}</th><th>{{ __('Status') }}</th></tr></thead>
        <tbody>
        @forelse($orders as $order)
            <tr>
                <td><a href="{{ route('provider.orders.show', $order->id) }}">#{{ $order->id }}</a></td>
                <td>{{ $order->resident_name ?? $order->resident?->name }}<div class="small text-secondary">{{ $order->home_name ?? $order->home?->name }} @if($order->unit_label) · {{ $order->unit_label }} @endif</div></td>
                <td>{{ $order->service_name ?? $order->service_code ?? $order->service?->translations?->firstWhere('locale', app()->getLocale())?->name }}</td>
                <td>{{ $order->scheduled_at?->translatedFormat('Y-m-d H:i') }}</td>
                <td><span class="badge text-bg-light">{{ $order->status_name ?? $order->status?->translations?->firstWhere('locale', app()->getLocale())?->name ?? $order->status?->code }}</span></td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-secondary py-4">{{ __('No service orders found.') }}</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
