@extends('rjcms::layouts.admin')

@section('title', $permission->name . ' · ' . config('app.name'))
@section('page-title', 'Permission details')

@section('page-actions')
    @can('edit_permissions')
        <x-admin.button :href="route('admin.permissions.edit', $permission)">Edit</x-admin.button>
    @endcan
@endsection

@section('content')
<div class="max-w-2xl">
    <x-admin.postbox title="Permission details">
        <dl class="divide-y divide-wp-border-light text-sm">
            <div class="grid grid-cols-3 gap-4 py-3">
                <dt class="font-medium text-wp-muted">Name</dt>
                <dd class="col-span-2 text-wp-ink">{{ $permission->name }}</dd>
            </div>
            <div class="grid grid-cols-3 gap-4 py-3">
                <dt class="font-medium text-wp-muted">Assigned to roles</dt>
                <dd class="col-span-2">
                    <div class="flex flex-wrap gap-1">
                        @forelse ($permission->roles as $role)
                            <span class="rounded bg-blue-50 px-2 py-0.5 text-xs font-medium text-wp-blue">{{ $role->name }}</span>
                        @empty
                            <span class="text-wp-muted">&mdash;</span>
                        @endforelse
                    </div>
                </dd>
            </div>
        </dl>

        <div class="mt-6">
            <x-admin.button variant="secondary" :href="route('admin.permissions.index')">Back to permissions</x-admin.button>
        </div>
    </x-admin.postbox>
</div>
@endsection
