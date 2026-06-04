@extends('rjcms::layouts.admin')

@section('title', $bread->name . ' · ' . config('app.name'))
@section('page-title', $bread->name . ' details')

@section('page-actions')
    @can($bread->permission('edit'))
        <x-admin.button :href="route('admin.bread.edit', [$bread, $record->getKey()])">Edit</x-admin.button>
    @endcan
@endsection

@section('content')
<div class="max-w-2xl rounded-md border border-wp-border bg-white p-6 shadow-sm">
    <dl class="divide-y divide-wp-border-light text-sm">
        @foreach ($bread->fieldsFor('read') as $field)
            <div class="grid grid-cols-3 gap-4 py-3">
                <dt class="font-medium text-wp-muted">{{ $field->label }}</dt>
                <dd class="col-span-2 text-wp-ink">
                    @include('rjcms::admin.bread._value', ['field' => $field, 'value' => $field->handler()->formValue($field, $record)])
                </dd>
            </div>
        @endforeach
    </dl>

    <div class="mt-6">
        <x-admin.button variant="secondary" :href="route('admin.bread.index', $bread)">Back to {{ Str::lower($bread->name_plural) }}</x-admin.button>
    </div>
</div>
@endsection
