@extends('layouts.admin')
@section('title', __('Package comparison features').' · Rounder')
@section('body')
<main class="container py-4"><h1 class="h3 mb-4">{{ __('Package comparison features') }}</h1>@include('admin.partials.feedback')
@adminCan('admin.package-features.create')<a class="btn btn-primary mb-3" href="{{ route('admin.package-features.create') }}">{{ __('Add comparison feature') }}</a>@endadminCan
@foreach($featureDefinitions as $feature)
<details class="border rounded p-3 mb-2"><summary>{{ $feature->{'name_'.app()->getLocale()} }} <span class="text-secondary">#{{ $feature->id }} · {{ $feature->is_active ? __('Active') : __('Inactive') }}</span></summary>
@adminCan('admin.package-features.update','PUT')<form class="mt-3" method="post" action="{{ route('admin.package-features.update',$feature->id) }}">@csrf @method('PUT')
@include('admin.packages.partials.feature-definition-fields',['feature'=>$feature])
</form>@endadminCan@adminCan('admin.package-features.destroy','DELETE')@include('admin.partials.delete-button',['action'=>route('admin.package-features.destroy',$feature->id)])@endadminCan</details>
@endforeach


</main>@endsection
