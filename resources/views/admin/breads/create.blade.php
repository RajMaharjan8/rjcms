@extends('rjcms::layouts.admin')

@section('title', 'New BREAD · ' . config('app.name'))
@section('page-title', 'New BREAD')

@section('content')
@php($inputClass = 'w-full rounded border border-wp-border bg-white px-3 py-1.5 text-sm text-wp-ink shadow-sm transition focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none')

<form method="POST" action="{{ route('admin.breads.store') }}" class="max-w-3xl">
    @csrf

    <x-admin.postbox title="New Content Type" body-class="px-5 py-1">
        <p class="border-b border-wp-border-light py-4 text-sm text-wp-muted">
            Just name it and configure its fields next — the table and columns are created for you automatically.
            Advanced: point it at an existing model/table to manage that instead.
        </p>

        <div class="divide-y divide-wp-border-light">
            <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-start sm:gap-4">
                <label for="name" class="pt-1.5 text-sm font-semibold text-wp-ink">Name (singular)</label>
                <x-admin.field name="name">
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus placeholder="Project" class="{{ $inputClass }}">
                </x-admin.field>
            </div>
            <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-start sm:gap-4">
                <label for="name_plural" class="pt-1.5 text-sm font-semibold text-wp-ink">Name (plural)</label>
                <x-admin.field name="name_plural">
                    <input id="name_plural" name="name_plural" type="text" value="{{ old('name_plural') }}" required placeholder="Projects" class="{{ $inputClass }}">
                </x-admin.field>
            </div>
            <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-start sm:gap-4">
                <label for="slug" class="pt-1.5 text-sm font-semibold text-wp-ink">Slug</label>
                <x-admin.field name="slug" hint="Used in URLs, e.g. /admin/content/projects">
                    <input id="slug" name="slug" type="text" value="{{ old('slug') }}" required placeholder="projects" class="{{ $inputClass }}">
                </x-admin.field>
            </div>
            <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-start sm:gap-4">
                <label for="model" class="pt-1.5 text-sm font-semibold text-wp-ink">Model class <span class="font-normal text-wp-muted">(optional)</span></label>
                <x-admin.field name="model" hint="Leave blank for a no-code type. Only set this to bind an existing model.">
                    <input id="model" name="model" type="text" list="models-list" value="{{ old('model') }}" placeholder="Leave blank — auto-managed" class="{{ $inputClass }}">
                    <datalist id="models-list">
                        @foreach ($models as $model)
                            <option value="{{ $model }}"></option>
                        @endforeach
                    </datalist>
                </x-admin.field>
            </div>
            <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-start sm:gap-4">
                <label for="table_name" class="pt-1.5 text-sm font-semibold text-wp-ink">Database table <span class="font-normal text-wp-muted">(optional)</span></label>
                <x-admin.field name="table_name" hint="Leave blank to derive it from the slug. Created automatically if it doesn't exist.">
                    <input id="table_name" name="table_name" type="text" list="tables-list" value="{{ old('table_name') }}" placeholder="Auto from slug" class="{{ $inputClass }}">
                    <datalist id="tables-list">
                        @foreach ($tables as $table)
                            <option value="{{ $table }}"></option>
                        @endforeach
                    </datalist>
                </x-admin.field>
            </div>
            <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-start sm:gap-4">
                <label for="icon" class="pt-1.5 text-sm font-semibold text-wp-ink">Menu icon</label>
                <x-admin.field name="icon" hint="Optional label or icon name">
                    <input id="icon" name="icon" type="text" value="{{ old('icon') }}" placeholder="document-text" class="{{ $inputClass }}">
                </x-admin.field>
            </div>
            <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-start sm:gap-4">
                <label for="description" class="pt-1.5 text-sm font-semibold text-wp-ink">Description</label>
                <x-admin.field name="description">
                    <textarea id="description" name="description" rows="2" class="{{ $inputClass }}">{{ old('description') }}</textarea>
                </x-admin.field>
            </div>
            <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-start sm:gap-4">
                <label for="use_permissions" class="pt-0.5 text-sm font-semibold text-wp-ink">Permissions</label>
                <label class="flex items-start gap-2 text-sm text-wp-ink">
                    <input type="hidden" name="use_permissions" value="0">
                    <input id="use_permissions" type="checkbox" name="use_permissions" value="1" {{ old('use_permissions', '1') ? 'checked' : '' }}
                           class="mt-0.5 rounded border-wp-border text-wp-blue focus:ring-wp-blue">
                    <span>
                        Create permissions for this content type
                        <span class="mt-0.5 block text-xs font-normal text-wp-muted">Generates browse/view/create/edit/delete permissions so access can be granted per role. Leave unchecked to make it available to every admin.</span>
                    </span>
                </label>
            </div>
        </div>

        <x-slot:footer>
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.breads.index') }}" class="text-sm font-medium text-wp-muted hover:text-wp-ink">Cancel</a>
                <x-admin.wp-button type="submit">Create &amp; Configure Fields</x-admin.wp-button>
            </div>
        </x-slot:footer>
    </x-admin.postbox>
</form>

<div class="mt-6 max-w-3xl">
    @include('rjcms::admin.breads._column-types-help')
</div>
@endsection
