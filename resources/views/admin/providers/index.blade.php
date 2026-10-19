@extends('layouts.admin')
@section('title', __('Providers and contracts').' · Rounder')
@section('body')
<div class="container-fluid"><div class="row g-3 p-3 p-lg-4">

<main class="col-12 content">
<div class="mb-4"><div class="small text-secondary">{{ __('Provider operations') }}</div><h1 class="h3 fw-bold">{{ __('Providers and contracts') }}</h1>@adminCan('admin.providers.create')<a class="btn btn-primary" href="{{ route('admin.providers.create') }}">{{ __('Add provider') }}</a>@endadminCan</div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="panel p-3 p-lg-4"><h2 class="h5 fw-bold">{{ __('Registered providers') }}</h2>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>{{ __('Company name') }}</th><th>{{ __('Contact name') }}</th><th>{{ __('Email address') }}</th><th>{{ __('Approval') }}</th><th>{{ __('Service commission (%)') }}</th><th>{{ __('Emergency commission (%)') }}</th><th>{{ __('Commission accrued') }}</th><th>{{ __('Contract') }}</th><th>{{ __('Actions') }}</th></tr></thead><tbody>
@forelse($providers as $provider)<tr><td>{{ $provider->provider_id }}</td><td>{{ $provider->company_name }}</td><td>{{ $provider->name }}</td><td>{{ $provider->email }}</td><td><span class="pill">{{ $provider->is_approved ? __('Approved') : __('Pending approval') }}</span></td><td>{{ $provider->service_commission_percent ?? '—' }}%</td><td>{{ $provider->emergency_commission_percent ?? '—' }}%</td><td>{{ number_format($provider->commission_total ?? 0,2) }} SAR</td><td>{{ $provider->contract_reference ?? '—' }}<div class="small text-secondary">{{ $provider->contract_status ? __($provider->contract_status) : '' }}</div></td><td>@adminCan('admin.providers.edit')<a class="btn btn-sm btn-outline-primary" href="{{ route('admin.providers.edit',$provider->provider_id) }}">{{ __('Edit company') }}</a>@endadminCan
@adminCan('admin.providers.destroy','DELETE')@include('admin.partials.delete-button',['action'=>route('admin.providers.destroy',$provider->provider_id)])@endadminCan</td></tr>
@empty<tr><td colspan="10" class="text-center text-secondary">{{ __('No providers found') }}</td></tr>@endforelse
</tbody></table>{{ $providers->links() }}</div>
</div>
</main></div></div>
@endsection
