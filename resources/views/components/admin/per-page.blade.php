@props(['paginator', 'options' => [8, 15, 25, 50, 100]])

@php
    $current = (int) request()->integer('perPage', method_exists($paginator, 'perPage') ? $paginator->perPage() : 15);
    $hidden = collect(request()->except(['perPage', 'page']));
    // Always include the active value so a non-preset perPage still shows.
    $choices = collect($options)->push($current)->unique()->sort()->values();
@endphp

{{-- "Show N per page" control. Submits via GET, preserving the page's other
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

    <label for="perPage" class="whitespace-nowrap">Show</label>
    <select id="perPage" name="perPage" onchange="this.form.submit()"
            class="rounded border border-wp-border bg-white px-2 py-1 text-sm text-wp-ink shadow-sm focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none">
        @foreach ($choices as $choice)
            <option value="{{ $choice }}" @selected($choice === $current)>{{ $choice }}</option>
        @endforeach
    </select>
    <span class="whitespace-nowrap">per page</span>
</form>
