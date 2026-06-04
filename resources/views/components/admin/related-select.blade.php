@props([
    'name',
    'options' => [],      // id => label
    'selected' => null,   // array of ids (multiple) or a single id
    'multiple' => false,
    'required' => false,
])

@php
    $optionList = collect($options)->map(fn ($label, $id) => ['id' => (string) $id, 'label' => (string) $label])->values();
    $selectedIds = $multiple
        ? collect((array) $selected)->map(fn ($id) => (string) $id)->values()
        : (filled($selected) ? [(string) $selected] : []);
@endphp

<div
    x-data="{
        name: @js($name),
        multiple: @js((bool) $multiple),
        options: @js($optionList),
        selected: @js($selectedIds),
        query: '',
        open: false,
        panelStyle: '',
        get available() {
            const picked = this.selected.map(String);
            const q = this.query.toLowerCase().trim();
            return this.options.filter(o => !picked.includes(String(o.id)) && o.label.toLowerCase().includes(q));
        },
        labelFor(id) { const o = this.options.find(o => String(o.id) === String(id)); return o ? o.label : id; },
        openPanel() { this.open = true; this.$nextTick(() => this.position()); },
        position() {
            if (! this.open || ! this.$refs.control) return;
            const r = this.$refs.control.getBoundingClientRect();
            const below = window.innerHeight - r.bottom;
            const maxH = Math.min(240, Math.max(below, r.top) - 12);
            const top = below < 180 && r.top > below ? '' : `top:${r.bottom + 4}px;`;
            const bottom = top ? '' : `bottom:${window.innerHeight - r.top + 4}px;`;
            this.panelStyle = `left:${r.left}px; width:${r.width}px; max-height:${maxH}px; ${top}${bottom}`;
        },
        choose(id) {
            if (this.multiple) { this.selected.push(String(id)); }
            else { this.selected = [String(id)]; }
            this.query = '';
            this.open = this.multiple;
            if (this.open) this.$nextTick(() => this.position());
        },
        remove(id) { this.selected = this.selected.filter(s => String(s) !== String(id)); },
    }"
    @click.outside="open = false"
    @keydown.escape="open = false"
    @scroll.window="position()"
    @resize.window="position()"
    class="relative"
>
    {{-- Submitted values --}}
    <template x-if="multiple">
        <span>
            <template x-for="id in selected" :key="id">
                <input type="hidden" :name="name + '[]'" :value="id">
            </template>
        </span>
    </template>
    <template x-if="! multiple">
        <input type="hidden" :name="name" :value="selected[0] ?? ''">
    </template>

    {{-- Control --}}
    <div x-ref="control" @click="openPanel()"
         class="flex min-h-10 flex-wrap items-center gap-1.5 rounded border border-wp-border bg-white px-2 py-1.5 text-sm shadow-sm focus-within:border-wp-blue focus-within:ring-1 focus-within:ring-wp-blue">
        <template x-for="id in selected" :key="id">
            <span class="inline-flex items-center gap-1 rounded bg-blue-50 px-2 py-0.5 text-xs font-medium text-wp-blue">
                <span x-text="labelFor(id)"></span>
                <button type="button" @click.stop="remove(id)" class="text-wp-blue/70 hover:text-wp-blue">&times;</button>
            </span>
        </template>

        <input type="text" x-model="query" @focus="openPanel()"
               x-show="multiple || selected.length === 0"
               placeholder="Search…"
               class="min-w-24 flex-1 border-0 p-0 text-sm focus:ring-0 focus:outline-none">
    </div>

    {{-- Dropdown — fixed so it is never clipped by an overflow-hidden panel --}}
    <div x-show="open" x-transition.opacity x-cloak :style="panelStyle"
         class="fixed z-50 overflow-auto rounded border border-wp-border bg-white py-1 shadow-lg">
        <template x-for="o in available" :key="o.id">
            <button type="button" @click="choose(o.id)"
                    class="block w-full px-3 py-1.5 text-left text-sm text-wp-ink hover:bg-blue-50" x-text="o.label"></button>
        </template>
        <p x-show="available.length === 0" class="px-3 py-2 text-sm text-wp-muted">No matches.</p>
    </div>
</div>
