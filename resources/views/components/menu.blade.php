@props(['location'])

{{-- Renders a menu (by slug) as a nested unordered list. Style via the
     `.menu`, `.sub-menu`, `.has-children` and `.is-active` classes. --}}
@php($items = menu($location))

@if ($items->isNotEmpty())
    <ul {{ $attributes->merge(['class' => 'menu menu-' . $location]) }}>
        @foreach ($items as $item)
            <x-menu-item :item="$item" />
        @endforeach
    </ul>
@endif
