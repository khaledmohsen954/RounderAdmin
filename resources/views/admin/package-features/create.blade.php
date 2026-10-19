@extends('layouts.admin')
@section('title', __('Add comparison feature').' · Rounder')
@section('body')
<main class="container py-4"><h1 class="h3 mb-4">{{ __('Add comparison feature') }}</h1>@include('admin.partials.feedback')
<form class="panel p-4" method="post" action="{{ route('admin.package-features.store') }}">@csrf
@include('admin.packages.partials.feature-definition-fields',['feature'=>null])
</form>
</main>@endsection
