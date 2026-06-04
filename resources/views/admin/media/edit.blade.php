@extends('rjcms::layouts.admin')

@section('title', 'Edit media · ' . config('app.name'))
@section('page-title', 'Edit media')

@section('content')
<div class="max-w-2xl rounded-md border border-wp-border bg-white p-6 shadow-sm">
    @if ($medium->isImage())
        <img src="{{ $medium->url }}" alt="{{ $medium->alt_text }}"
             class="mb-5 max-h-56 rounded-lg ring-1 ring-wp-border">
    @endif

    <form method="POST" action="{{ route('admin.media.update', $medium) }}">
        @csrf
        @method('PUT')

        <div class="flex flex-col gap-5">
            <x-admin.field label="Name" name="name">
                <x-admin.input name="name" :value="$medium->name" required autofocus />
            </x-admin.field>

            <x-admin.field label="Alt text" name="alt_text">
                <x-admin.input name="alt_text" :value="$medium->alt_text" />
            </x-admin.field>

            <div class="flex gap-3 pt-2">
                <x-admin.button type="submit">Save</x-admin.button>
                <x-admin.button variant="secondary" :href="route('admin.media.index')">Cancel</x-admin.button>
            </div>
        </div>
    </form>
</div>
@endsection
