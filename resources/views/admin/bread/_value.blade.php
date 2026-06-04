{{-- Renders a record's field value for browse / read views. --}}
@switch($field->type->value)
    @case('toggle')
    @case('boolean')
        <span @class([
            'rounded-md px-2 py-0.5 text-xs font-medium',
            'bg-green-50 text-green-700' => $value,
            'bg-gray-100 text-gray-600' => ! $value,
        ])>{{ $value ? 'Yes' : 'No' }}</span>
        @break

    @case('date')
        {{ filled($value) ? \Illuminate\Support\Carbon::parse($value)->format('M j, Y') : '—' }}
        @break

    @case('textarea')
        {{ filled($value) ? Str::limit(trim(strip_tags((string) $value)), 90) : '—' }}
        @break

    @case('library_image')
    @case('image')
        @php($media = filled($value) ? \Rjcodes\Rjcms\Models\Media::find($value) : null)
        @if ($media && $media->isImage())
            <img src="{{ $media->thumbnail_url }}" alt="{{ $media->alt_text }}" loading="lazy"
                 class="h-16 w-16 rounded-lg object-cover ring-1 ring-gray-200">
        @else
            —
        @endif
        @break

    @case('library_gallery')
    @case('images')
        @php($mediaItems = filled($value) ? \Rjcodes\Rjcms\Models\Media::whereIn('id', (array) $value)->get() : collect())
        @if ($mediaItems->isNotEmpty())
            <div class="flex flex-wrap gap-1">
                @foreach ($mediaItems->take(4) as $media)
                    <img src="{{ $media->thumbnail_url }}" alt="{{ $media->alt_text }}" loading="lazy"
                         class="h-10 w-10 rounded object-cover ring-1 ring-gray-200">
                @endforeach
                @if ($mediaItems->count() > 4)
                    <span class="flex h-10 w-10 items-center justify-center rounded bg-gray-100 text-xs font-medium text-gray-600">+{{ $mediaItems->count() - 4 }}</span>
                @endif
            </div>
        @else
            —
        @endif
        @break

    @case('select')
        {{ filled($value) ? data_get($field->options, "choices.{$value}", $value) : '—' }}
        @break

    @case('multiselect')
        @php($labels = collect((array) $value)->map(fn ($item) => data_get($field->options, "choices.{$item}", $item)))
        {{ $labels->isNotEmpty() ? $labels->implode(', ') : '—' }}
        @break

    @case('relationship')
        @php($handler = $field->handler())
        @php($map = $handler->options($field))
        @if ($handler->isMultiple($field))
            @php($labels = collect((array) $value)->map(fn ($id) => $map->get($id, '#' . $id)))
            {{ $labels->isNotEmpty() ? $labels->implode(', ') : '—' }}
        @else
            {{ filled($value) ? $map->get($value, '#' . $value) : '—' }}
        @endif
        @break

    @case('repeater')
        {{ is_array($value) && count($value) ? count($value) . ' item(s)' : '—' }}
        @break

    @default
        {{ filled($value) ? $value : '—' }}
@endswitch
