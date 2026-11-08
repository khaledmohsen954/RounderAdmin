@extends('layouts.provider')
@section('title', __('Team and permissions'))
@section('body')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div><h1 class="h3 fw-bold mb-1">{{ __('Team and permissions') }}</h1><p class="text-secondary mb-0">{{ __('Manage provider team accounts and the sections they can access.') }}</p></div>
    <a class="btn btn-orange" href="{{ route('provider.team.create') }}">+ {{ __('Add team member') }}</a>
</div>
<section class="panel p-3 p-lg-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>{{ __('Name') }}</th><th>{{ __('Email') }}</th><th>{{ __('Phone') }}</th><th>{{ __('Permissions') }}</th><th>{{ __('Actions') }}</th></tr></thead>
            <tbody>
            @forelse($members as $member)
                <tr>
                    <td>{{ $member->name }}</td>
                    <td>{{ $member->email ?: '—' }}</td>
                    <td dir="ltr">{{ $member->phone ?: '—' }}</td>
                    <td>{{ collect($member->permissions)->map(fn ($permission) => match ($permission) {
                        'dashboard.view' => __('View dashboard'),
                        'orders.view' => __('View service orders'),
                        'orders.create' => __('Create service orders'),
                        'orders.manage' => __('Accept and update service orders'),
                        'prices.view' => __('View service prices'),
                        'prices.manage' => __('Submit and resubmit prices for approval'),
                        'emergencies.view' => __('View assigned emergency requests'),
                        'users.manage' => __('Manage team users and permissions'),
                        default => $permission,
                    })->join('، ') ?: '—' }}</td>
                    <td class="text-nowrap">
                        @if((int) $member->id === $ownerId)
                            <span class="text-secondary">{{ __('Primary account') }}</span>
                        @else
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('provider.team.edit', $member->id) }}">{{ __('Edit') }}</a>
                            <form class="d-inline" method="post" action="{{ route('provider.team.destroy', $member->id) }}" onsubmit="return confirm('{{ __('Revoke this team member access?') }}')">
                                @csrf @method('delete')
                                <button class="btn btn-sm btn-outline-danger" type="submit">{{ __('Revoke access') }}</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-secondary py-4">{{ __('No team members have been added.') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $members->links() }}</div>
</section>
@endsection
