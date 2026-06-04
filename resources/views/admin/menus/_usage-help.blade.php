<x-admin.postbox title="Use on the front end">
    <div class="space-y-3 text-xs leading-relaxed text-wp-muted">
        <p>This menu's slug is <span class="font-mono font-semibold text-wp-ink">{{ $menu->slug }}</span>.</p>

        <p>Easiest — the ready-made component renders nested <span class="font-mono">&lt;ul&gt;</span>s:</p>
        <pre class="overflow-x-auto rounded border border-wp-border bg-gray-50 p-2.5 font-mono text-[11px] text-wp-ink">&lt;x-menu location="{{ $menu->slug }}" /&gt;</pre>

        <p>Full control — loop it yourself. <span class="font-mono">menu()</span> returns the top-level items; each has a <span class="font-mono">children</span> collection for its sub-menu:</p>
        @verbatim
            <pre class="overflow-x-auto rounded border border-wp-border bg-gray-50 p-2.5 font-mono text-[11px] text-wp-ink">&lt;ul&gt;
@foreach (menu('@endverbatim{{ $menu->slug }}@verbatim') as $item)
    &lt;li&gt;
        &lt;a href="{{ $item->url ?: '#' }}"
           @if ($item->opensInNewTab()) target="_blank" rel="noopener" @endif&gt;
            {{ $item->title }}
        &lt;/a&gt;

        {{-- sub-menu --}}
        @if ($item->children->isNotEmpty())
            &lt;ul&gt;
                @foreach ($item->children as $child)
                    &lt;li&gt;&lt;a href="{{ $child->url ?: '#' }}"&gt;{{ $child->title }}&lt;/a&gt;&lt;/li&gt;
                @endforeach
            &lt;/ul&gt;
        @endif
    &lt;/li&gt;
@endforeach
&lt;/ul&gt;</pre>
        @endverbatim

        <ul class="list-inside list-disc space-y-1">
            <li>Each item exposes: <span class="font-mono">title</span>, <span class="font-mono">url</span>, <span class="font-mono">children</span>, <span class="font-mono">opensInNewTab()</span>, <span class="font-mono">isActive()</span>.</li>
            <li><span class="font-mono">menu()</span> is always safe — it returns an empty collection if the menu is missing.</li>
        </ul>
    </div>
</x-admin.postbox>
