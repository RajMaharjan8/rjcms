@props(['variant' => 'primary', 'href' => null, 'size' => 'base'])

@php
    $variants = [
        'primary' => 'border border-wp-blue bg-wp-blue text-white shadow-sm hover:bg-wp-blue-hover hover:border-wp-blue-hover',
        'secondary' => 'border border-wp-blue bg-white text-wp-blue shadow-sm hover:bg-blue-50',
        'ghost' => 'border border-wp-border bg-white text-wp-muted shadow-sm hover:bg-gray-50',
        'danger' => 'border border-red-600 bg-white text-red-700 shadow-sm hover:bg-red-50',
    ];
    $sizes = [
        'base' => 'px-4 py-2 text-sm',
        'sm' => 'px-2.5 py-1.5 text-xs',
    ];
    $classes = 'inline-flex items-center justify-center gap-1.5 rounded font-semibold transition focus:outline-none focus:ring-2 focus:ring-wp-blue/40 disabled:cursor-not-allowed disabled:opacity-50 '
        . ($sizes[$size] ?? $sizes['base']) . ' '
        . ($variants[$variant] ?? $variants['primary']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>{{ $slot }}</button>
@endif
