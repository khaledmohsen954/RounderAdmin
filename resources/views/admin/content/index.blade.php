@extends('layouts.admin')
@section('title', __('Resident operations').' · Rounder')
@section('body')
<div class="container-fluid"><div class="row g-3 p-3 p-lg-4">

<main class="col-12 content">
<div class="mb-4"><div class="small text-secondary">{{ __('Resident app management') }}</div><h1 class="h3 fw-bold">{{ __('Resident operations') }}</h1></div>
@include('admin.partials.feedback')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="row g-4">
<div class="col-xl-6"><div class="panel p-3 p-lg-4"><h2 class="h5 fw-bold">{{ __('Create announcement') }}</h2>
@adminCan('admin.content.announcements.store','POST')<form method="post" action="{{ route('admin.content.announcements.store') }}">@csrf
<div class="mb-3"><label class="form-label">{{ __('Home') }} ({{ __('All homes') }})</label><select name="home_id" class="form-select"><option value="">{{ __('All homes') }}</option>@foreach($homes as $home)<option value="{{ $home->id }}">{{ $home->name }} · #{{ $home->id }}</option>@endforeach</select></div>
<div class="row g-2"><div class="col-md-6"><label class="form-label">{{ __('Title') }} · العربية</label><input class="form-control" name="title_ar" required maxlength="180"></div><div class="col-md-6"><label class="form-label">{{ __('Title') }} · English</label><input class="form-control" name="title_en" required maxlength="180"></div>
<div class="col-12"><label class="form-label">{{ __('Body') }} · العربية</label><textarea class="form-control" name="body_ar" rows="3" required maxlength="20000"></textarea></div><div class="col-12"><label class="form-label">{{ __('Body') }} · English</label><textarea class="form-control" name="body_en" rows="3" required maxlength="20000"></textarea></div></div>
<div class="row g-2 align-items-end mt-1"><div class="col-md-6"><label class="form-label">{{ __('Expires at') }}</label><input type="datetime-local" class="form-control" name="expires_at"></div><div class="col-md-3"><label class="form-label">{{ __('Publish now') }}</label><select class="form-select" name="published" required><option value="1">{{ __('Yes') }}</option><option value="0">{{ __('No') }}</option></select></div><div class="col-md-3"><button class="btn btn-primary w-100">{{ __('Save') }}</button></div></div></form>@endadminCan
<div class="table-responsive mt-4"><table class="table table-sm align-middle"><thead><tr><th>#</th><th>{{ __('Title') }}</th><th>{{ __('Status') }}</th><th>{{ __('Date') }}</th></tr></thead><tbody>@forelse($announcements as $row)<tr><td>{{ $row->id }}</td><td>{{ $row->title ?? $row->reference }}</td><td>{{ $row->is_published ? __('Published') : __('Draft') }}</td><td>{{ $row->created_at }}
@adminCan('admin.content.announcements.edit')<a class="btn btn-sm btn-outline-primary" href="{{ route('admin.content.announcements.edit',$row->id) }}">{{ __('Edit') }}</a>@endadminCan
@adminCan('admin.content.announcements.destroy','DELETE')@include('admin.partials.delete-button',['action'=>route('admin.content.announcements.destroy',$row->id)])@endadminCan
</td></tr>@empty<tr><td colspan="4">{{ __('No records') }}</td></tr>@endforelse</tbody></table>{{ $announcements->links() }}</div>
</div></div>

<div class="col-xl-6"><div class="panel p-3 p-lg-4"><h2 class="h5 fw-bold">{{ __('Create initiative') }}</h2>
@adminCan('admin.content.initiatives.store','POST')<form method="post" action="{{ route('admin.content.initiatives.store') }}">@csrf
<div class="mb-3"><label class="form-label">{{ __('Home') }}</label><select name="home_id" class="form-select" required><option value="">{{ __('Choose home') }}</option>@foreach($homes as $home)<option value="{{ $home->id }}">{{ $home->name }} · #{{ $home->id }}</option>@endforeach</select></div>
<div class="row g-2"><div class="col-md-6"><label class="form-label">{{ __('Title') }} · العربية</label><input class="form-control" name="title_ar" required maxlength="180"></div><div class="col-md-6"><label class="form-label">{{ __('Title') }} · English</label><input class="form-control" name="title_en" required maxlength="180"></div>
<div class="col-12"><label class="form-label">{{ __('Description') }} · العربية</label><textarea class="form-control" name="description_ar" rows="3" required maxlength="5000"></textarea></div><div class="col-12"><label class="form-label">{{ __('Description') }} · English</label><textarea class="form-control" name="description_en" rows="3" required maxlength="5000"></textarea></div></div>
<div class="row g-2 mt-1"><div class="col-md-6"><label class="form-label">{{ __('Starts at') }}</label><input type="datetime-local" class="form-control" name="starts_at" required></div><div class="col-md-6"><label class="form-label">{{ __('Ends at') }}</label><input type="datetime-local" class="form-control" name="ends_at" required></div><div class="col-md-6"><label class="form-label">{{ __('Target amount') }}</label><input type="number" step=".01" min="0" class="form-control" name="target_amount"></div><div class="col-md-3"><label class="form-label">{{ __('Publish') }}</label><select class="form-select" name="is_published" required><option value="1">{{ __('Yes') }}</option><option value="0">{{ __('No') }}</option></select></div><div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary w-100">{{ __('Save') }}</button></div></div></form>@endadminCan
<div class="table-responsive mt-4"><table class="table table-sm align-middle"><thead><tr><th>#</th><th>{{ __('Title') }}</th><th>{{ __('Status') }}</th><th>{{ __('Ends at') }}</th></tr></thead><tbody>@forelse($initiatives as $row)<tr><td>{{ $row->id }}</td><td>{{ $row->title ?? $row->reference }}</td><td>{{ $row->is_published ? __('Published') : __('Draft') }}</td><td>{{ $row->ends_at }}
@adminCan('admin.content.initiatives.edit')<a class="btn btn-sm btn-outline-primary" href="{{ route('admin.content.initiatives.edit',$row->id) }}">{{ __('Edit') }}</a>@endadminCan
@adminCan('admin.content.initiatives.destroy','DELETE')@include('admin.partials.delete-button',['action'=>route('admin.content.initiatives.destroy',$row->id)])@endadminCan
</td></tr>@empty<tr><td colspan="4">{{ __('No records') }}</td></tr>@endforelse</tbody></table>{{ $initiatives->links() }}</div>
</div></div>

<div class="col-12"><div class="panel p-3 p-lg-4"><h2 class="h5 fw-bold">{{ __('Facility reports') }}</h2><div class="table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>{{ __('Reference') }}</th><th>{{ __('Title') }}</th><th>{{ __('Status') }}</th><th>{{ __('Update') }}</th></tr></thead><tbody>
@forelse($reports as $report)<tr><td>{{ $report->id }}</td><td>{{ $report->reference }}</td><td>{{ $report->title }}</td><td>{{ $report->status_name ?? '—' }}</td><td>@adminCan('admin.content.reports.update','PUT')<form method="post" action="{{ route('admin.content.reports.update',$report->id) }}" class="d-flex gap-2">@csrf @method('put')<select name="status_id" class="form-select form-select-sm" required>@foreach($statuses as $status)<option value="{{ $status->id }}" @selected($report->status_id===$status->id)>{{ $status->name }}</option>@endforeach</select><input class="form-control form-control-sm" name="note" maxlength="2000" placeholder="{{ __('Internal note') }}"><button class="btn btn-sm btn-primary">{{ __('Save') }}</button></form>@endadminCan</td></tr>@empty<tr><td colspan="5" class="text-center text-secondary">{{ __('No records') }}</td></tr>@endforelse
</tbody></table>{{ $reports->links() }}</div></div></div>
</div>
</main></div></div>
@endsection
