@extends('rjcms::layouts.admin')

@section('title', Str::plural($label) . ' · ' . config('app.name'))
@section('page-title', Str::plural($label))

@section('page-actions')
    @if ($key === 'blogs')
        @can('edit_blogs')
            <x-admin.wp-button variant="secondary" :href="route('admin.blogs.template.edit')">Template</x-admin.wp-button>
        @endcan
    @endif
    @can('edit_'.$key)
        <x-admin.wp-button variant="secondary" :href="route('admin.content-fields.index', $key)">Custom fields</x-admin.wp-button>
    @endcan
    @can('create_'.$key)
        <x-admin.wp-button :href="route('admin.'.$key.'.create')">+ New {{ $label }}</x-admin.wp-button>
    @endcan
@endsection

@section('content')
<div class="overflow-hidden rounded-md border border-wp-border bg-white shadow-sm">
    <table class="min-w-full divide-y divide-wp-border text-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold tracking-wide text-wp-muted uppercase">
            <tr>
                <th class="px-4 py-3">Title</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Slug</th>
                <th class="px-4 py-3">Updated</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-wp-border-light">
            @forelse ($records as $record)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-wp-ink">{{ $record->title }}</td>
                    <td class="px-4 py-3">
                        <span @class([
                            'rounded-sm px-2 py-0.5 text-xs font-medium',
                            'bg-green-50 text-green-700' => $record->status === 'published',
                            'bg-gray-100 text-wp-muted' => $record->status !== 'published',
                        ])>{{ ucfirst($record->status ?? 'draft') }}</span>
                    </td>
                    <td class="px-4 py-3 font-mono text-xs text-wp-muted">{{ $record->slug }}</td>
                    <td class="px-4 py-3 text-wp-muted">{{ $record->updated_at?->format('M j, Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-3 text-sm font-medium">
                            @if ($record->status === 'published')
                                <a href="{{ route($publicRoute, $record->slug) }}" target="_blank" rel="noopener" class="text-wp-muted hover:text-wp-ink">View</a>
                            @endif
                            @can('edit_'.$key)
                                <a href="{{ route('admin.'.$key.'.edit', $record->getKey()) }}" class="text-wp-blue hover:text-wp-blue-hover">Edit</a>
                            @endcan
                            @can('delete_'.$key)
                                <form method="POST" action="{{ route('admin.'.$key.'.destroy', $record->getKey()) }}"
                                      onsubmit="return confirm('Delete this {{ Str::lower($label) }}?')">
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
                    <td colspan="5" class="px-4 py-10 text-center text-wp-muted">No {{ Str::lower(Str::plural($label)) }} yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $records->links() }}</div>
@endsection
