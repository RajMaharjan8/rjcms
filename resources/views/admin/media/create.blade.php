@extends('rjcms::layouts.admin')

@section('title', 'Upload media · ' . config('app.name'))
@section('page-title', 'Upload media')

@section('content')
<div class="max-w-2xl rounded-md border border-wp-border bg-white p-6 shadow-sm">
    <form method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="flex flex-col gap-5">
            <x-admin.field label="File" name="file" hint="JPG, PNG, WEBP, GIF or SVG. Max 5 MB.">
                <input id="file" name="file" type="file" accept="image/*" required
                       class="text-sm text-wp-muted file:mr-3 file:rounded file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-wp-blue hover:file:bg-blue-100">
            </x-admin.field>

            <x-admin.field label="Name" name="name" hint="Optional. Defaults to the file name.">
                <x-admin.input name="name" />
            </x-admin.field>

            <x-admin.field label="Alt text" name="alt_text" hint="Optional. Describes the image for accessibility.">
                <x-admin.input name="alt_text" />
            </x-admin.field>

            <div class="flex gap-3 pt-2">
                <x-admin.button type="submit">Upload</x-admin.button>
                <x-admin.button variant="secondary" :href="route('admin.media.index')">Cancel</x-admin.button>
            </div>
        </div>
    </form>
</div>
@endsection
