@extends('rjcms::layouts.admin')

@section('title', 'Blog template · ' . config('app.name'))
@section('page-title', 'Blog template')

@section('content')
@php($inputClass = 'w-full rounded border border-wp-border bg-white px-3 py-2 text-sm text-wp-ink shadow-sm transition focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none')

<div class="max-w-2xl rounded-md border border-wp-border bg-white p-6 shadow-sm">
    @if ($errors->any())
        <div class="mb-6 rounded border-l-4 border-red-500 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p class="mb-5 text-sm text-wp-muted">
        The Blade view used to render <strong>every</strong> blog post on the public site.
        Set it once here and all posts share it.
    </p>

    <form method="POST" action="{{ route('admin.blogs.template.update') }}">
        @csrf
        @method('PUT')

        <div class="flex flex-col gap-1.5">
            <label for="template" class="text-sm font-semibold text-wp-ink">Template</label>
            <input id="template" name="template" type="text" value="{{ old('template', $template) }}"
                   placeholder="blog.show" class="{{ $inputClass }} font-mono">
            <p class="text-xs text-wp-muted">
                Blade view in dot notation, e.g. <span class="font-mono">blog.templates.featured</span>
                → <span class="font-mono">resources/views/blog/templates/featured.blade.php</span>.
                Leave blank to use the built-in <span class="font-mono">blog.show</span>.
            </p>
        </div>

        <div class="mt-6 flex gap-3">
            <x-admin.button type="submit">Save</x-admin.button>
            <x-admin.button variant="secondary" :href="route('admin.blogs.index')">Cancel</x-admin.button>
        </div>
    </form>
</div>
@endsection
