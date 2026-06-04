@props(['item'])

{{-- One menu link; recurses into its children to render sub-menus. --}}
<li @class(['has-children' => $item->children->isNotEmpty(), 'is-active' => $item->isActive()])>
    <a href="{{ $item->url ?: '#' }}" @if ($item->opensInNewTab()) target="_blank" rel="noopener" @endif>
        {{ $item->title }}
    </a>

    @if ($item->children->isNotEmpty())
        <ul class="sub-menu">
            @foreach ($item->children as $child)
                <x-menu-item :item="$child" />
            @endforeach
        </ul>
    @endif
</li>
