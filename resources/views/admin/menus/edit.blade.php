@extends('rjcms::layouts.admin')

@section('title', 'Edit ' . $menu->name . ' · ' . config('app.name'))
@section('page-title', 'Edit menu: ' . $menu->name)

@section('page-actions')
    <x-admin.wp-button variant="secondary" :href="route('admin.menus.index')">All menus</x-admin.wp-button>
@endsection

@section('content')
@php($state = json_decode(old('payload', ''), true) ?: $state)
@php($inputClass = 'w-full rounded border border-wp-border bg-white px-3 py-1.5 text-sm text-wp-ink shadow-sm transition focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none')

@if ($errors->any())
    <div class="mb-6 rounded border-l-4 border-red-500 bg-red-50 px-4 py-3 text-sm text-red-700">
        <p class="font-semibold">Please fix the following:</p>
        <ul class="mt-1 list-inside list-disc">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.menus.update', $menu) }}"
      @submit="$refs.payload.value = JSON.stringify(form); saving = true"
      x-data="{
          saving: false,
          form: @js($state),
          blankItem() { return { uid: crypto.randomUUID(), title: '', url: '', target: '_self', parent: '' }; },
          addItem() { this.form.items.push(this.blankItem()); },
          removeItem(i) {
              const uid = this.form.items[i].uid;
              this.form.items.splice(i, 1);
              this.form.items.forEach(it => { if (it.parent === uid) it.parent = ''; });
          },
          moveItem(i, dir) {
              const j = i + dir;
              if (j < 0 || j >= this.form.items.length) return;
              [this.form.items[i], this.form.items[j]] = [this.form.items[j], this.form.items[i]];
          },
          reorder(uid, position) {
              const from = this.form.items.findIndex(it => it.uid === uid);
              if (from === -1 || from === position) return;
              const [moved] = this.form.items.splice(from, 1);
              this.form.items.splice(position, 0, moved);
          },
          parentOptions(item) { return this.form.items.filter(it => it.uid !== item.uid && it.title); },
          parentTitle(uid) { const p = this.form.items.find(it => it.uid === uid); return p ? p.title : ''; },
      }"
      class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
    @csrf
    @method('PUT')
    <input type="hidden" name="payload" x-ref="payload">

    {{-- Main column --}}
    <div class="flex flex-col gap-6">
        <x-admin.postbox title="Menu settings" body-class="px-5 py-1">
            <div class="divide-y divide-wp-border-light">
                <div class="grid gap-2 py-4 sm:grid-cols-[140px_minmax(0,1fr)] sm:items-center sm:gap-4">
                    <label class="text-sm font-semibold text-wp-ink">Name</label>
                    <input type="text" x-model="form.name" class="{{ $inputClass }}">
                </div>
                <div class="grid gap-2 py-4 sm:grid-cols-[140px_minmax(0,1fr)] sm:items-center sm:gap-4">
                    <label class="text-sm font-semibold text-wp-ink">Slug</label>
                    <div>
                        <input type="text" x-model="form.slug" class="{{ $inputClass }} font-mono">
                        <p class="mt-1 text-xs text-wp-muted">Fetch on the front end with <span class="font-mono">menu('<span x-text="form.slug || 'slug'"></span>')</span></p>
                    </div>
                </div>
            </div>
        </x-admin.postbox>

        <x-admin.postbox title="Links" body-class="p-4">
            <x-slot:actions>
                <span class="text-xs text-wp-muted" x-text="`${form.items.length} item${form.items.length === 1 ? '' : 's'}`"></span>
                <x-admin.wp-button type="button" size="sm" @click="addItem()">+ Add Link</x-admin.wp-button>
            </x-slot:actions>

            <div class="flex flex-col gap-2"
                 x-sort="reorder($item, $position)"
                 x-sort:config="{ ghostClass: 'opacity-40' }">
                <template x-for="(item, index) in form.items" :key="item.uid">
                    <div class="rounded border border-wp-border bg-white"
                         :class="item.parent ? 'ml-6 border-l-2 border-l-wp-blue' : ''"
                         x-sort:item="item.uid">
                        <div class="flex flex-wrap items-center gap-3 px-3 py-2.5">
                            <span x-sort:handle class="cursor-grab text-wp-border select-none active:cursor-grabbing" title="Drag to reorder">⠿</span>

                            <div class="flex min-w-[160px] flex-1 flex-col gap-1">
                                <label class="text-[11px] font-semibold tracking-wide text-wp-muted uppercase">Title</label>
                                <input type="text" x-model="item.title" placeholder="Home" class="{{ $inputClass }}">
                            </div>

                            <div class="flex min-w-[200px] flex-1 flex-col gap-1">
                                <label class="text-[11px] font-semibold tracking-wide text-wp-muted uppercase">URL</label>
                                <input type="text" x-model="item.url" placeholder="/ or https://…" class="{{ $inputClass }} font-mono">
                            </div>

                            <div class="flex min-w-[150px] flex-col gap-1">
                                <label class="text-[11px] font-semibold tracking-wide text-wp-muted uppercase">Sub-menu of</label>
                                <select x-model="item.parent" class="{{ $inputClass }}">
                                    <option value="">— Top level —</option>
                                    <template x-for="opt in parentOptions(item)" :key="opt.uid">
                                        <option :value="opt.uid" x-text="opt.title"></option>
                                    </template>
                                </select>
                            </div>

                            <label class="mt-4 flex items-center gap-1.5 text-sm text-wp-ink">
                                <input type="checkbox" x-model="item.target" true-value="_blank" false-value="_self"
                                       class="rounded border-wp-border text-wp-blue focus:ring-wp-blue"> New tab
                            </label>

                            <div class="mt-4 flex items-center gap-1 text-wp-muted">
                                <button type="button" @click="moveItem(index, -1)" :disabled="index === 0"
                                        class="rounded px-1.5 py-0.5 hover:bg-gray-100 disabled:opacity-30" title="Move up">↑</button>
                                <button type="button" @click="moveItem(index, 1)" :disabled="index === form.items.length - 1"
                                        class="rounded px-1.5 py-0.5 hover:bg-gray-100 disabled:opacity-30" title="Move down">↓</button>
                                <button type="button" @click="removeItem(index)"
                                        class="rounded px-1.5 py-0.5 text-red-600 hover:bg-red-50" title="Remove link">&times;</button>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="form.items.length === 0"
                     class="rounded border border-dashed border-wp-border bg-gray-50 px-4 py-10 text-center">
                    <p class="text-sm font-medium text-wp-ink">No links yet</p>
                    <p class="mt-1 text-xs text-wp-muted">Add your first link to start building this menu.</p>
                    <x-admin.wp-button type="button" size="sm" class="mt-3" @click="addItem()">+ Add Link</x-admin.wp-button>
                </div>
            </div>
        </x-admin.postbox>
    </div>

    {{-- Sidebar --}}
    <div class="flex flex-col gap-6 lg:sticky lg:top-6">
        <x-admin.postbox title="Save">
            <p class="text-sm text-wp-muted">Drag <span class="font-mono">⠿</span> to reorder. Pick a “Sub-menu of” parent to nest a link.</p>
            <x-slot:footer>
                <div class="flex items-center justify-between gap-3">
                    <a href="{{ route('admin.menus.index') }}" class="text-sm font-medium text-wp-muted hover:text-wp-ink">Cancel</a>
                    <x-admin.button type="submit" x-bind:disabled="saving"
                                    class="disabled:cursor-not-allowed disabled:opacity-60"
                                    x-text="saving ? 'Saving…' : 'Save Menu'">Save Menu</x-admin.button>
                </div>
            </x-slot:footer>
        </x-admin.postbox>

        @include('rjcms::admin.menus._usage-help', ['menu' => $menu])
    </div>
</form>
@endsection
