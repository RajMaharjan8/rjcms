@extends('rjcms::layouts.admin')

@section('title', $bread->name_plural . ' · ' . config('app.name'))
@section('page-title', $bread->name_plural)

@section('page-actions')
    @can($bread->permission('create'))
        <x-admin.button :href="route('admin.bread.create', $bread)">New {{ Str::lower($bread->name) }}</x-admin.button>
    @endcan
@endsection

@section('content')
@php($browseFields = $bread->fieldsFor('browse'))
<div class="overflow-hidden rounded-md border border-wp-border bg-white shadow-sm">
    <table class="min-w-full divide-y divide-wp-border text-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold tracking-wide text-wp-muted uppercase">
            <tr>
                @foreach ($browseFields as $field)
                    <th class="px-4 py-3">{{ $field->label }}</th>
                @endforeach
                <th class="px-4 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-wp-border-light">
            @forelse ($records as $record)
                <tr class="hover:bg-gray-50">
                    @foreach ($browseFields as $field)
                        <td class="px-4 py-3 text-wp-ink">
                            @include('rjcms::admin.bread._value', ['field' => $field, 'value' => $field->handler()->formValue($field, $record)])
                        </td>
                    @endforeach
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-3 text-sm font-medium">
                            @can($bread->permission('view'))
                                <a href="{{ route('admin.bread.show', [$bread, $record->getKey()]) }}" class="text-wp-muted hover:text-wp-ink">View</a>
                            @endcan
                            @can($bread->permission('edit'))
                                <a href="{{ route('admin.bread.edit', [$bread, $record->getKey()]) }}" class="text-wp-blue hover:text-wp-blue-hover">Edit</a>
                            @endcan
                            @can($bread->permission('delete'))
                                <form method="POST" action="{{ route('admin.bread.destroy', [$bread, $record->getKey()]) }}"
                                      onsubmit="return confirm('Delete this {{ Str::lower($bread->name) }}?')">
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
                    <td colspan="{{ $browseFields->count() + 1 }}" class="px-4 py-10 text-center text-wp-muted">
                        No {{ Str::lower($bread->name_plural) }} yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <x-admin.per-page :paginator="$records" />
    <div>{{ $records->withQueryString()->links() }}</div>
</div>
@endsection
