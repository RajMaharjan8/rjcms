<?php

use Rjcodes\Rjcms\Models\Media;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    /**
     * Reset to the first page whenever the search term changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * The paginated, filtered media items.
     */
    #[Computed]
    public function media()
    {
        return Media::query()
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('alt_text', 'like', $term));
            })
            ->latest()
            ->paginate(24);
    }
};
?>

<div>
    <div class="mb-4 flex items-center gap-3">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search media…"
               class="w-full rounded border border-wp-border px-3 py-2 text-sm shadow-sm focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none sm:max-w-xs">
        <span wire:loading wire:target="search" class="text-sm text-wp-muted">Searching…</span>
    </div>

    @if ($this->media->isEmpty())
        <div class="rounded-md border border-wp-border bg-white p-10 text-center text-wp-muted">
            {{ $search !== '' ? 'No media match your search.' : 'No media uploaded yet.' }}
        </div>
    @else
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($this->media as $item)
                <div wire:key="media-{{ $item->id }}" class="overflow-hidden rounded-md border border-wp-border bg-white shadow-sm">
                    <a href="{{ route('admin.media.show', $item) }}" class="block aspect-square bg-gray-50">
                        @if ($item->isImage())
                            <img src="{{ $item->thumbnail_url }}" alt="{{ $item->alt_text }}" loading="lazy" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full items-center justify-center text-xs font-semibold text-wp-muted">
                                {{ strtoupper($item->extension) }}
                            </div>
                        @endif
                    </a>
                    <div class="p-3">
                        <p class="truncate text-sm font-medium text-wp-ink" title="{{ $item->name }}">{{ $item->name }}</p>
                        <p class="text-xs text-wp-muted">{{ \Illuminate\Support\Number::fileSize($item->size) }}</p>
                        <div class="mt-2 flex gap-3 text-xs font-medium">
                            @can('edit_medias')
                                <a href="{{ route('admin.media.edit', $item) }}" class="text-wp-blue hover:text-wp-blue-hover">Edit</a>
                            @endcan
                            @can('delete_medias')
                                <form method="POST" action="{{ route('admin.media.destroy', $item) }}"
                                      onsubmit="return confirm('Delete this file?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-500">Delete</button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $this->media->links() }}</div>
    @endif
</div>
