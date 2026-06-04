@props(['title' => null, 'bodyClass' => 'p-5'])

{{-- WordPress-style metabox: titled panel with optional header actions and footer slot. --}}
<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-md border border-wp-border bg-white shadow-sm']) }}>
    @if ($title || isset($actions))
        <div class="flex min-h-12 items-center justify-between gap-3 border-b border-wp-border-light bg-gray-50 px-4 py-2.5">
            <h2 class="text-sm font-semibold text-wp-ink">{{ $title }}</h2>
            @isset($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div class="{{ $bodyClass }}">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="border-t border-wp-border-light bg-gray-50 px-4 py-3">
            {{ $footer }}
        </div>
    @endisset
</div>
