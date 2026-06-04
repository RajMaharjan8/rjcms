@extends('rjcms::layouts.admin')

@section('title', 'Menus · ' . config('app.name'))
@section('page-title', 'Menus')

@section('page-actions')
    @can('create_menus')
        <x-admin.wp-button :href="route('admin.menus.create')">+ New menu</x-admin.wp-button>
    @endcan
@endsection

@section('content')
<div class="overflow-hidden rounded-md border border-wp-border bg-white shadow-sm">
    <table class="min-w-full divide-y divide-wp-border text-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold tracking-wide text-wp-muted uppercase">
            <tr>
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">Slug</th>
                <th class="px-4 py-3">Items</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-wp-border-light">
            @forelse ($menus as $menu)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-wp-ink">{{ $menu->name }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-wp-muted">{{ $menu->slug }}</td>
                    <td class="px-4 py-3 text-wp-muted">{{ $menu->items_count }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-3 text-sm font-medium">
                            @can('edit_menus')
                                <a href="{{ route('admin.menus.edit', $menu) }}" class="text-wp-blue hover:text-wp-blue-hover">Edit</a>
                            @endcan
                            @can('delete_menus')
                                <form method="POST" action="{{ route('admin.menus.destroy', $menu) }}"
                                      onsubmit="return confirm('Delete this menu and all its items?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-500">Delete</button>
                                </form>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-10 text-center text-wp-muted">No menus yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
