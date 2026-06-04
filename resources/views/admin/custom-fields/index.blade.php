@extends('rjcms::layouts.admin')

@section('title', $groupLabel . ' fields · ' . config('app.name'))
@section('page-title', $groupLabel . ' custom fields')

@section('page-actions')
    <x-admin.wp-button variant="secondary" :href="route('admin.'.$group.'.index')">Back to {{ Str::plural($groupLabel) }}</x-admin.wp-button>
    <x-admin.wp-button :href="route('admin.content-fields.create', $group)">+ New Field</x-admin.wp-button>
@endsection

@section('content')
<p class="mb-4 text-sm text-wp-muted">
    Custom fields appear on the {{ Str::lower($groupLabel) }} editor and are stored in each record's <span class="font-mono">meta</span>.
    Read them in Blade with <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs">$record->field('key')</code>.
</p>

<div class="overflow-hidden rounded-md border border-wp-border bg-white shadow-sm">
    <table class="min-w-full divide-y divide-wp-border text-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold tracking-wide text-wp-muted uppercase">
            <tr>
                <th class="px-4 py-3">Label</th>
                <th class="px-4 py-3">Key</th>
                <th class="px-4 py-3">Type</th>
                <th class="px-4 py-3">Required</th>
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-wp-border-light">
            @forelse ($fields as $field)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-wp-ink">{{ $field->label }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-wp-muted">{{ $field->key }}</td>
                    <td class="px-4 py-3 text-wp-muted">{{ $field->asField()->type->label() }}</td>
                    <td class="px-4 py-3 text-wp-muted">{{ $field->required ? 'Yes' : 'No' }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.content-fields.edit', [$group, $field]) }}" class="text-sm font-medium text-wp-blue hover:text-wp-blue-hover">Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-wp-muted">No custom fields yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
