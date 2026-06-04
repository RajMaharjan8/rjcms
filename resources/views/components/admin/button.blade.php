@props(['variant' => 'primary', 'href' => null])

@php
    $variants = [
        'primary' => 'border border-wp-blue bg-wp-blue text-white shadow-sm hover:bg-wp-blue-hover hover:border-wp-blue-hover',
        'secondary' => 'border border-wp-blue bg-white text-wp-blue shadow-sm hover:bg-blue-50',
        'danger' => 'border border-red-600 bg-red-600 text-white shadow-sm hover:bg-red-500',
    ];
    $classes = 'inline-flex items-center justify-center rounded px-3.5 py-2 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-wp-blue/40 '
        . ($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>{{ $slot }}</button>
@endif
