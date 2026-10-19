@extends('layouts.admin')
@section('title', __('Permissions').' · Rounder')
@section('body')
<main class="container py-4"><h1 class="h3 mb-4">{{ __('Permissions') }}</h1>
@include('admin.partials.feedback')
@adminCan('admin.permissions.create')<a class="btn btn-primary mb-3" href="{{ route('admin.permissions.create') }}">{{ __('Add permission') }}</a>@endadminCan
<div class="panel p-3 table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>{{ __('Name') }}</th><th>{{ __('Users') }}</th><th>{{ __('Actions') }}</th></tr></thead><tbody>
@forelse($sets as $set)<tr><td>{{ $set->id }}</td><td>{{ app()->getLocale()==='ar' ? $set->name_ar : $set->name_en }}</td><td>{{ $set->admins_count }}</td><td>
@adminCan('admin.permissions.edit')<a class="btn btn-sm btn-outline-primary" href="{{ route('admin.permissions.edit',$set->id) }}">{{ __('Edit') }}</a>@endadminCan
@include('admin.partials.delete-button',['action'=>route('admin.permissions.destroy',$set->id)])
</td></tr>@empty<tr><td colspan="4">{{ __('No records') }}</td></tr>@endforelse
</tbody></table>{{ $sets->links() }}</div>
</main>
@endsection
