@extends('rjcms::layouts.admin')

@section('title', 'BREAD builder · ' . config('app.name'))
@section('page-title', 'BREAD builder')

@section('page-actions')
    @can('create_breads')
        <x-admin.button :href="route('admin.breads.create')">New BREAD</x-admin.button>
    @endcan
@endsection

@section('content')
<p class="mb-4 text-sm text-wp-muted">
    A BREAD turns an existing model into a configurable content type with its own CRUD UI.
</p>

<div class="overflow-hidden rounded-md border border-wp-border bg-white shadow-sm">
    <table class="min-w-full divide-y divide-wp-border text-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold tracking-wide text-wp-muted uppercase">
            <tr>
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">Slug</th>
                <th class="px-4 py-3">Model</th>
                <th class="px-4 py-3">Fields</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-wp-border-light">
            @forelse ($breads as $bread)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-wp-ink">{{ $bread->name_plural }}</td>
                    <td class="px-4 py-3 text-wp-muted">{{ $bread->slug }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-wp-muted">{{ $bread->model }}</td>
                    <td class="px-4 py-3 text-wp-muted">{{ $bread->fields_count }}</td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-3 text-sm font-medium">
                            <a href="{{ route('admin.bread.index', $bread) }}" class="text-wp-muted hover:text-wp-ink">Open</a>
                            @can('edit_breads')
                                <a href="{{ route('admin.breads.edit', $bread) }}" class="text-wp-blue hover:text-wp-blue-hover">Configure</a>
                            @endcan
                            @can('delete_breads')
                                <form method="POST" action="{{ route('admin.breads.destroy', $bread) }}"
                                      onsubmit="return confirm('Delete this BREAD definition? The underlying table is not affected.')">
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
                    <td colspan="5" class="px-4 py-10 text-center text-wp-muted">No BREADs configured yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
