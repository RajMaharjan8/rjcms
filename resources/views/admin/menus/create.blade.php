@extends('rjcms::layouts.admin')

@section('title', 'New menu · ' . config('app.name'))
@section('page-title', 'New menu')

@section('content')
<div class="max-w-2xl">
    <x-admin.postbox title="New menu" body-class="p-5">
        @if ($errors->any())
            <div class="mb-4 rounded border-l-4 border-red-500 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.menus.store') }}" class="flex flex-col gap-5">
            @csrf
            <x-admin.field label="Name" name="name" hint="A label for your reference, e.g. Header or Footer.">
                <input id="name" name="name" type="text" value="{{ old('name') }}" required
                       class="rounded border border-wp-border px-3 py-2 text-sm shadow-sm focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none">
            </x-admin.field>

            <x-admin.field label="Slug" name="slug" hint="Used to fetch the menu on the front end: menu('header'). Leave blank to derive from the name.">
                <input id="slug" name="slug" type="text" value="{{ old('slug') }}"
                       class="rounded border border-wp-border px-3 py-2 font-mono text-sm shadow-sm focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none">
            </x-admin.field>

            <div class="flex gap-3 pt-1">
                <x-admin.button type="submit">Create menu</x-admin.button>
                <x-admin.button variant="secondary" :href="route('admin.menus.index')">Cancel</x-admin.button>
            </div>
        </form>
    </x-admin.postbox>
</div>
@endsection
