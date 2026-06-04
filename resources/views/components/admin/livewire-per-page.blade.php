@props(['options' => [8, 15, 25, 50, 100]])

{{-- "Show N per page" control for Livewire listings. Binds to the host
     component's `perPage` property; the component should reset the page on
     change via updatedPerPage(). --}}
<div {{ $attributes->merge(['class' => 'flex items-center gap-2 text-sm text-wp-muted']) }}>
    <label class="whitespace-nowrap">Show</label>
    <select wire:model.live="perPage"
            class="rounded border border-wp-border bg-white px-2 py-1 text-sm text-wp-ink shadow-sm focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none">
        @foreach ($options as $opt)
            <option value="{{ $opt }}">{{ $opt }}</option>
        @endforeach
    </select>
    <span class="whitespace-nowrap">per page</span>
</div>
