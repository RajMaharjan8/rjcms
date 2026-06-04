@extends('rjcms::layouts.admin')

@section('title', $role->name . ' · ' . config('app.name'))
@section('page-title', 'Role details')

@section('page-actions')
    @can('edit_roles')
        <x-admin.button :href="route('admin.roles.edit', $role)">Edit</x-admin.button>
    @endcan
@endsection

@section('content')
<div class="max-w-2xl rounded-md border border-wp-border bg-white p-6 shadow-sm">
    <dl class="divide-y divide-wp-border-light text-sm">
        <div class="grid grid-cols-3 gap-4 py-3">
            <dt class="font-medium text-wp-muted">Name</dt>
            <dd class="col-span-2 text-wp-ink">{{ $role->name }}</dd>
        </div>
        <div class="grid grid-cols-3 gap-4 py-3">
            <dt class="font-medium text-wp-muted">Permissions</dt>
            <dd class="col-span-2">
                <div class="flex flex-wrap gap-1">
                    @forelse ($role->permissions as $permission)
                        <span class="rounded-md bg-gray-100 px-2 py-0.5 text-xs font-medium text-wp-muted">{{ $permission->name }}</span>
                    @empty
                        <span class="text-wp-muted">&mdash;</span>
                    @endforelse
                </div>
            </dd>
        </div>
    </dl>

    <div class="mt-6">
        <x-admin.button variant="secondary" :href="route('admin.roles.index')">Back to roles</x-admin.button>
    </div>
</div>
@endsection
