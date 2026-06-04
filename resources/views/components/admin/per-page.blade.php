@props(['paginator'])

@php
    $current = (int) request()->integer('perPage', method_exists($paginator, 'perPage') ? $paginator->perPage() : 15);
    $hidden = collect(request()->except(['perPage', 'page']));
@endphp

{{-- "Rows per page" control. Submits via GET, preserving the page's other
     query params (search, filters), and resets to page 1. --}}
<form method="GET" {{ $attributes->merge(['class' => 'flex items-center gap-2 text-sm text-wp-muted']) }}>
    @foreach ($hidden as $key => $value)
        @if (is_array($value))
            @foreach ($value as $item)
                <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
            @endforeach
        @else
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    <label for="perPage" class="whitespace-nowrap">Rows per page</label>
    <input id="perPage" name="perPage" type="number" min="1" max="100" value="{{ $current }}"
           onchange="this.form.submit()"
           class="w-20 rounded border border-wp-border bg-white px-2 py-1 text-sm text-wp-ink shadow-sm focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none">
</form>
