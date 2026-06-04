@extends('rjcms::layouts.admin')

@section('title', $medium->name . ' · ' . config('app.name'))
@section('page-title', 'Media details')

@section('page-actions')
    @can('edit_medias')
        <x-admin.button :href="route('admin.media.edit', $medium)">Edit</x-admin.button>
    @endcan
@endsection

@section('content')
<div class="grid gap-6 lg:grid-cols-2">
    <div class="rounded-md border border-wp-border bg-white p-4 shadow-sm">
        @if ($medium->isImage())
            <img src="{{ $medium->url }}" alt="{{ $medium->alt_text }}" class="mx-auto max-h-96 rounded-lg">
        @else
            <div class="flex h-48 items-center justify-center text-sm font-semibold text-wp-muted">
                {{ strtoupper($medium->extension) }} file
            </div>
        @endif
    </div>

    <div class="rounded-md border border-wp-border bg-white p-6 shadow-sm">
        <dl class="divide-y divide-wp-border-light text-sm">
            @foreach ([
                'Name' => $medium->name,
                'File name' => $medium->file_name,
                'Alt text' => $medium->alt_text ?: '—',
                'Type' => $medium->mime_type,
                'Size' => \Illuminate\Support\Number::fileSize($medium->size),
                'Dimensions' => $medium->width ? $medium->width . ' × ' . $medium->height . ' px' : '—',
                'Uploaded by' => $medium->uploader?->name ?? '—',
                'Uploaded' => $medium->created_at->format('M j, Y g:i A'),
            ] as $label => $value)
                <div class="grid grid-cols-3 gap-4 py-3">
                    <dt class="font-medium text-wp-muted">{{ $label }}</dt>
                    <dd class="col-span-2 break-words text-wp-ink">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>

        <div class="mt-6 flex gap-3">
            <x-admin.button variant="secondary" :href="route('admin.media.index')">Back to library</x-admin.button>
            <x-admin.button variant="secondary" :href="$medium->url" target="_blank" rel="noopener">Open file</x-admin.button>
        </div>
    </div>
</div>

@if ($medium->isImage())
    <div class="mt-6 rounded border-l-4 border-wp-blue bg-white px-4 py-3 text-sm text-wp-ink shadow-sm">
        <p class="mb-1 font-semibold">Using this image in Blade</p>
        <p class="text-wp-muted">
            A compressed thumbnail{{ $medium->thumbnail_path ? '' : ' (none generated for this file)' }} is stored alongside the original.
            Resolve the media by id, then use
            <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs">$media->thumbnail_url</code> in lists/grids for optimized loading,
            or <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs">$media->url</code> for the full-size original. e.g.
            <code class="mt-1 block rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs">@verbatim{{ \Rjcodes\Rjcms\Models\Media::find($id)?->thumbnail_url }}@endverbatim</code>
        </p>
    </div>
@endif
@endsection
