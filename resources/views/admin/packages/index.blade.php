@extends('layouts.admin')
@section('title', __('Customer packages').' · Rounder')
@section('body')
<main class="container py-4"><h1 class="h3 mb-4">{{ __('Customer packages') }}</h1>@include('admin.partials.feedback')
@adminCan('admin.packages.create')<a class="btn btn-primary mb-3" href="{{ route('admin.packages.create') }}">{{ __('Create customer package') }}</a>@endadminCan
<div class="panel p-3 p-lg-4"><h2 class="h5 fw-bold">{{ __('Customer packages') }}</h2><div class="table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>{{ __('Package') }}</th><th>{{ __('Cycle') }}</th><th>{{ __('Price') }}</th><th>{{ __('Services included') }}</th><th>{{ __('Duration') }}</th><th>{{ __('Status') }}</th></tr></thead><tbody>
@forelse($packages as $package)<tr><td>{{ $package->id }}</td><td>{{ $package->name ?? $package->code }}<div class="small text-secondary">{{ $package->code }}</div></td><td>{{ __($package->cycle_code === 'semiannual' ? 'Semiannual' : ucfirst($package->cycle_code)) }}</td><td>@if($package->image_path)<img class="rounded me-2" width="48" height="48" style="object-fit:cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($package->image_path) }}" alt="{{ __('Product image') }}">@endif
@if($package->price_before_discount > $package->price)<del class="small text-secondary">{{ number_format($package->price_before_discount,2) }}</del> @endif{{ number_format($package->price,2) }} {{ $package->currency_code }}</td><td><strong>{{ $package->services_count }}</strong><div class="small text-secondary">@foreach($packageServices->get($package->id,collect()) as $included){{ $included->service_name ?? $included->service_code }} ({{ $included->is_unlimited ? __('Unlimited') : $included->included_quantity }})@if(!$loop->last), @endif @endforeach</div></td><td>{{ $package->duration_months }} {{ __('months') }}</td><td>{{ $package->is_active ? __('Active') : __('Inactive') }} @adminCan('admin.packages.edit')<a class="btn btn-sm btn-outline-primary" href="{{ route('admin.packages.edit', $package->id) }}">{{ __('Edit') }}</a>@endadminCan
@adminCan('admin.packages.destroy','DELETE')@include('admin.partials.delete-button',['action'=>route('admin.packages.destroy',$package->id)])@endadminCan</td></tr>
@empty<tr><td colspan="7">{{ __('No packages found') }}</td>@endforelse
</tbody></table>{{ $packages->links() }}</div></div>

</main>@endsection
