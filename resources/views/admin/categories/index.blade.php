@extends('rjcms::layouts.admin')

@section('title', 'Categories · ' . config('app.name'))
@section('page-title', 'Categories')

@section('page-actions')
    @can('create_categories')
        <x-admin.wp-button :href="route('admin.categories.create')">+ New category</x-admin.wp-button>
    @endcan
@endsection

@section('content')
<div class="overflow-hidden rounded-md border border-wp-border bg-white shadow-sm">
    <table class="min-w-full divide-y divide-wp-border text-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold tracking-wide text-wp-muted uppercase">
            <tr>
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">Slug</th>
                <th class="px-4 py-3">Posts</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-wp-border-light">
            @forelse ($categories as $category)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-wp-ink">{{ $category->name }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-wp-muted">{{ $category->slug }}</td>
                    <td class="px-4 py-3 text-wp-muted">{{ $category->posts_count }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-3 text-sm font-medium">
                            <a href="{{ route('blog.category', $category) }}" target="_blank" rel="noopener" class="text-wp-muted hover:text-wp-ink">View</a>
                            @can('edit_categories')
                                <a href="{{ route('admin.categories.edit', $category) }}" class="text-wp-blue hover:text-wp-blue-hover">Edit</a>
                            @endcan
                            @can('delete_categories')
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                      onsubmit="return confirm('Delete this category? Posts keep their other categories.')">
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
                    <td colspan="4" class="px-4 py-10 text-center text-wp-muted">No categories yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $categories->links() }}</div>
@endsection
