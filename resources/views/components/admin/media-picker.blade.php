<?php

use Rjcodes\Rjcms\Models\Media;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * WordPress-style media picker. Browses/searches the image library, uploads
 * new files inline, and submits the selected media id(s) via hidden inputs so
 * the surrounding form persists only the id(s) — never the file itself.
 */
new class extends Component
{
    use WithPagination;

    /**
     * The form input name the selected id(s) are submitted under. Reactive so a
     * parent (e.g. a repeater row) can update it when row indices shift.
     */
    #[Reactive]
    public string $name = '';

    /** Allow selecting more than one image (submitted as name[]). */
    public bool $multiple = false;

    /** @var array<int, int> Ordered list of selected media ids. */
    public array $selected = [];

    public bool $showModal = false;

    public string $search = '';

    /**
     * @param  int|array<int, int>|null  $value
     */
    public function mount(bool $multiple = false, mixed $value = null): void
    {
        $this->multiple = $multiple;
        $this->selected = collect(is_array($value) ? $value : (filled($value) ? [$value] : []))
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->values()
            ->all();
    }

    /**
     * The paginated, filtered image library.
     */
    #[Computed]
    public function media()
    {
        return Media::query()
            ->where('mime_type', 'like', 'image/%')
            ->when($this->search !== '', function ($query): void {
                $term = '%'.$this->search.'%';
                $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('alt_text', 'like', $term));
            })
            ->latest()
            ->paginate(18);
    }

    /**
     * The currently selected media records, in selection order.
     */
    #[Computed]
    public function selectedMedia()
    {
        if (empty($this->selected)) {
            return collect();
        }

        return Media::whereIn('id', $this->selected)
            ->get()
            ->sortBy(fn (Media $m): int => array_search($m->id, $this->selected))
            ->values();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Auto-select media that was just uploaded via the inline upload endpoint
     * (see MediaController::upload), so an upload immediately becomes the
     * field's value — respecting the single vs. multiple selection mode.
     *
     * @param  array<int, int|string>  $ids
     */
    public function selectUploaded(array $ids): void
    {
        foreach ($ids as $id) {
            $this->select((int) $id);
        }

        $this->resetPage();
    }

    /**
     * Toggle an image's selection (respecting single vs. multiple mode).
     */
    public function toggle(int $id): void
    {
        if (in_array($id, $this->selected, true)) {
            $this->remove($id);

            return;
        }

        $this->select($id);
    }

    public function select(int $id): void
    {
        if (in_array($id, $this->selected, true)) {
            return;
        }

        $this->selected = $this->multiple ? [...$this->selected, $id] : [$id];
    }

    public function remove(int $id): void
    {
        $this->selected = array_values(array_filter($this->selected, fn (int $existing): bool => $existing !== $id));
    }
};
?>

<div>
    {{-- Hidden inputs submitted with the parent form --}}
    @if ($multiple)
        @foreach ($selected as $id)
            <input type="hidden" name="{{ $name }}[]" value="{{ $id }}" wire:key="hidden-{{ $id }}">
        @endforeach
    @else
        <input type="hidden" name="{{ $name }}" value="{{ $selected[0] ?? '' }}">
    @endif

    {{-- Selected previews + trigger --}}
    <div class="flex flex-wrap items-center gap-3">
        @foreach ($this->selectedMedia as $item)
            <div wire:key="preview-{{ $item->id }}" class="group relative h-20 w-20 overflow-hidden rounded border border-wp-border bg-gray-50">
                <img src="{{ $item->thumbnail_url }}" alt="{{ $item->alt_text }}" loading="lazy" class="h-full w-full object-cover">
                <button type="button" wire:click="remove({{ $item->id }})"
                        class="absolute top-0.5 right-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-gray-900/70 text-xs text-white opacity-0 transition group-hover:opacity-100"
                        title="Remove">&times;</button>
            </div>
        @endforeach

        <button type="button" wire:click="$set('showModal', true)"
                class="flex h-20 w-20 flex-col items-center justify-center gap-1 rounded border border-dashed border-wp-border bg-white text-wp-muted transition hover:border-wp-blue hover:text-wp-blue">
            <span class="text-xl leading-none">+</span>
            <span class="text-[10px] font-medium">{{ $multiple ? 'Add' : ($selected ? 'Change' : 'Select') }}</span>
        </button>
    </div>

    {{-- Modal --}}
    <div x-data x-show="$wire.showModal" x-cloak x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4" style="display: none;">
        <div @click.outside="$wire.showModal = false"
             class="flex max-h-[85vh] w-full max-w-4xl flex-col overflow-hidden rounded-lg bg-white shadow-xl">
            {{-- Header --}}
            <div class="flex items-center justify-between gap-3 border-b border-wp-border-light px-5 py-3">
                <h2 class="text-base font-semibold text-wp-ink">{{ $multiple ? 'Select images' : 'Select image' }}</h2>
                <button type="button" wire:click="$set('showModal', false)" class="rounded p-1 text-wp-muted hover:bg-gray-100" aria-label="Close">&times;</button>
            </div>

            {{-- Toolbar --}}
            <div class="flex flex-wrap items-center gap-3 border-b border-wp-border-light bg-gray-50 px-5 py-3">
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search media…"
                       class="w-full rounded border border-wp-border px-3 py-1.5 text-sm shadow-sm focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none sm:max-w-xs">
                <div x-data="{
                         uploading: false,
                         error: '',
                         async upload(event) {
                             const files = [...event.target.files];
                             if (! files.length) return;
                             this.uploading = true;
                             this.error = '';
                             const ids = [];
                             for (const file of files) {
                                 const body = new FormData();
                                 body.append('file', file);
                                 const response = await fetch(@js(route('admin.media.upload')), {
                                     method: 'POST',
                                     headers: { 'X-CSRF-TOKEN': @js(csrf_token()), 'Accept': 'application/json' },
                                     body,
                                 });
                                 if (response.ok) {
                                     const data = await response.json();
                                     if (data.id) ids.push(data.id);
                                 } else {
                                     const data = await response.json().catch(() => ({}));
                                     this.error = data.message || 'Upload failed.';
                                 }
                             }
                             event.target.value = '';
                             this.uploading = false;
                             if (ids.length) $wire.selectUploaded(ids);
                         },
                     }"
                     class="flex items-center gap-3">
                    <label class="inline-flex cursor-pointer items-center gap-1.5 rounded border border-wp-blue bg-white px-3 py-1.5 text-sm font-semibold text-wp-blue transition hover:bg-blue-50">
                        <span x-show="! uploading">Upload files</span>
                        <span x-show="uploading" x-cloak>Uploading…</span>
                        <input type="file" multiple accept="image/*" class="hidden" @change="upload($event)">
                    </label>
                    <span x-show="error" x-text="error" x-cloak class="text-sm text-red-600"></span>
                </div>
            </div>

            {{-- Grid --}}
            <div class="flex-1 overflow-y-auto p-5">
                @if ($this->media->isEmpty())
                    <div class="py-16 text-center text-sm text-wp-muted">
                        {{ $search !== '' ? 'No images match your search.' : 'No images yet — upload one above.' }}
                    </div>
                @else
                    <div class="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-6">
                        @foreach ($this->media as $item)
                            @php($position = array_search($item->id, $selected, true))
                            <button type="button" wire:key="grid-{{ $item->id }}" wire:click="toggle({{ $item->id }})"
                                    @class([
                                        'group relative aspect-square overflow-hidden rounded border-2 bg-gray-50 transition',
                                        'border-wp-blue ring-2 ring-wp-blue/30' => $position !== false,
                                        'border-transparent hover:border-wp-border' => $position === false,
                                    ])>
                                <img src="{{ $item->thumbnail_url }}" alt="{{ $item->alt_text }}" loading="lazy" class="h-full w-full object-cover">
                                @if ($position !== false)
                                    <span class="absolute top-1 right-1 flex h-5 w-5 items-center justify-center rounded-full bg-wp-blue text-xs font-semibold text-white">
                                        {{ $multiple ? $position + 1 : '✓' }}
                                    </span>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    <div class="mt-4">{{ $this->media->links() }}</div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between gap-3 border-t border-wp-border-light bg-gray-50 px-5 py-3">
                <span class="text-sm text-wp-muted">{{ count($selected) }} selected</span>
                <button type="button" wire:click="$set('showModal', false)"
                        class="rounded border border-wp-blue bg-wp-blue px-4 py-2 text-sm font-semibold text-white transition hover:bg-wp-blue-hover">
                    Done
                </button>
            </div>
        </div>
    </div>
</div>
