@extends('layouts.admin')
@section('title', __('Available services').' · Rounder')
@section('body')
<main class="container py-4"><h1 class="h3 mb-4">{{ __('Available services') }}</h1>@include('admin.partials.feedback')
@adminCan('admin.services.create')<a class="btn btn-primary mb-3" href="{{ route('admin.services.create') }}">{{ __('Create service') }}</a>@endadminCan
<div class="panel p-3 p-lg-4 mb-4"><h2 class="h5 fw-bold">{{ __('Available services') }}</h2><div class="table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>{{ __('Service') }}</th><th>{{ __('Category') }}</th><th>{{ __('Base price') }}</th><th>{{ __('Status') }}</th></tr></thead><tbody>
@forelse($services as $service)<tr><td>{{ $service->id }}</td><td>{{ $service->name ?? $service->code }}<div class="small text-secondary">{{ $service->code }}</div></td><td>{{ $service->category_name ?? '' }}</td><td>@if($service->image_path)<img class="rounded me-2" width="48" height="48" style="object-fit:cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($service->image_path) }}" alt="{{ __('Product image') }}">@endif
@if($service->price_before_discount > $service->base_price)<del class="small text-secondary">{{ number_format($service->price_before_discount,2) }}</del> @endif{{ number_format($service->base_price,2) }} {{ __('SAR') }}</td><td>{{ $service->is_active ? __('Active') : __('Inactive') }} @adminCan('admin.packages.services.edit')<a class="btn btn-sm btn-outline-primary" href="{{ route('admin.packages.services.edit', $service->id) }}">{{ __('Edit') }}</a>@endadminCan
@adminCan('admin.packages.services.destroy','DELETE')@include('admin.partials.delete-button',['action'=>route('admin.packages.services.destroy',$service->id)])@endadminCan</td></tr>@empty<tr><td colspan="5">{{ __('No services found') }}</td></tr>@endforelse
</tbody></table></div></div>


</main>@endsection
