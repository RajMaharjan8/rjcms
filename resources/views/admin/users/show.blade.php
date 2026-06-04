@extends('rjcms::layouts.admin')

@section('title', $user->name . ' · ' . config('app.name'))
@section('page-title', 'User details')

@section('page-actions')
    @can('edit_users')
        <x-admin.button :href="route('admin.users.edit', $user)">Edit</x-admin.button>
    @endcan
@endsection

@section('content')
<div class="max-w-2xl rounded-md border border-wp-border bg-white p-6 shadow-sm">
    <dl class="divide-y divide-wp-border-light text-sm">
        <div class="grid grid-cols-3 gap-4 py-3">
            <dt class="font-medium text-wp-muted">Name</dt>
            <dd class="col-span-2 text-wp-ink">{{ $user->name }}</dd>
        </div>
        <div class="grid grid-cols-3 gap-4 py-3">
            <dt class="font-medium text-wp-muted">Email</dt>
            <dd class="col-span-2 text-wp-ink">{{ $user->email }}</dd>
        </div>
        <div class="grid grid-cols-3 gap-4 py-3">
            <dt class="font-medium text-wp-muted">Roles</dt>
            <dd class="col-span-2">
                <div class="flex flex-wrap gap-1">
                    @forelse ($user->roles as $role)
                        <span class="rounded-md bg-blue-50 px-2 py-0.5 text-xs font-medium text-wp-blue">{{ $role->name }}</span>
                    @empty
                        <span class="text-wp-muted">&mdash;</span>
                    @endforelse
                </div>
            </dd>
        </div>
        <div class="grid grid-cols-3 gap-4 py-3">
            <dt class="font-medium text-wp-muted">Created</dt>
            <dd class="col-span-2 text-wp-ink">{{ $user->created_at->format('M j, Y g:i A') }}</dd>
        </div>
    </dl>

    <div class="mt-6">
        <x-admin.button variant="secondary" :href="route('admin.users.index')">Back to users</x-admin.button>
    </div>
</div>
@endsection
