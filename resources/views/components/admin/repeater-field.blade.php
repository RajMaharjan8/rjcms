<?php

use Livewire\Attributes\Reactive;
use Livewire\Component;

/**
 * A repeating group of sub-fields, managed server-side so that sub-fields which
 * are themselves Livewire components (media pickers, nested repeaters) can be
 * added and removed dynamically. Every sub-input is rendered with a bracketed
 * `name` so the values submit with the surrounding plain form.
 */
new class extends Component
{
    /**
     * The bracketed input-name prefix for this repeater's rows, e.g.
     * `sections` at the top level or `sections[0][blocks]` when nested.
     */
    #[Reactive]
    public string $name = '';

    /** @var array<int, array<string, mixed>> Sub-field definitions for this level. */
    public array $subfields = [];

    /** @var array<int, array<string, mixed>> Current row values. */
    public array $rows = [];

    /**
     * @param  array<int, array<string, mixed>>  $subfields
     */
    public function mount(array $subfields = [], mixed $rows = null): void
    {
        $this->subfields = array_values($subfields);
        $this->rows = array_values(array_map(
            fn ($row): array => $this->normalizeRow(is_array($row) ? $row : []),
            is_array($rows) ? $rows : [],
        ));
    }

    /**
     * Ensure a row has a stable key and array defaults for collection sub-fields.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function normalizeRow(array $row): array
    {
        if (! isset($row['_uid'])) {
            $row['_uid'] = 'r'.bin2hex(random_bytes(6));
        }

        foreach ($this->subfields as $sub) {
            $column = $sub['column_name'];
            $type = $sub['type'] ?? 'text';

            if (in_array($type, ['images', 'multiselect', 'repeater'], true)) {
                $row[$column] = isset($row[$column]) && is_array($row[$column]) ? $row[$column] : [];
            } elseif (! array_key_exists($column, $row)) {
                $row[$column] = $type === 'boolean' ? false : null;
            }
        }

        return $row;
    }

    public function addRow(): void
    {
        $this->rows[] = $this->normalizeRow([]);
    }

    public function removeRow(int $index): void
    {
        if (array_key_exists($index, $this->rows)) {
            unset($this->rows[$index]);
            $this->rows = array_values($this->rows);
        }
    }
};
?>

@php($inputClass = 'w-full rounded border border-wp-border bg-white px-3 py-1.5 text-sm text-wp-ink shadow-sm transition focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none')

<div class="flex flex-col gap-3">
    @forelse ($rows as $i => $row)
        <div wire:key="row-{{ $row['_uid'] }}" class="rounded border border-wp-border bg-white">
            <div class="flex items-center justify-between border-b border-wp-border-light bg-gray-50 px-3 py-2">
                <span class="text-xs font-semibold tracking-wide text-wp-muted uppercase">Item {{ $i + 1 }}</span>
                <button type="button" wire:click="removeRow({{ $i }})"
                        class="rounded px-1.5 py-0.5 text-xs font-medium text-red-600 hover:bg-red-50">Remove</button>
            </div>

            <div class="flex flex-col gap-3 p-3">
                @foreach ($subfields as $sub)
                    @php($column = $sub['column_name'])
                    @php($type = $sub['type'] ?? 'text')
                    @php($base = $name.'['.$i.']['.$column.']')
                    @php($model = 'rows.'.$i.'.'.$column)
                    @php($choices = $sub['choices'] ?? [])

                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-wp-ink">
                            {{ $sub['label'] ?: $column }}
                            @if ($sub['required'] ?? false)<span class="text-red-600">*</span>@endif
                        </label>

                        @switch($type)
                            @case('textarea')
                                <textarea rows="3" name="{{ $base }}" wire:model="{{ $model }}" class="{{ $inputClass }}"></textarea>
                                @break

                            @case('number')
                                <input type="number" name="{{ $base }}" wire:model="{{ $model }}" class="{{ $inputClass }}">
                                @break

                            @case('date')
                                <input type="date" name="{{ $base }}" wire:model="{{ $model }}" class="{{ $inputClass }}">
                                @break

                            @case('boolean')
                                <label class="flex items-center gap-2 text-sm text-wp-ink">
                                    <input type="hidden" name="{{ $base }}" value="0">
                                    <input type="checkbox" name="{{ $base }}" value="1" wire:model="{{ $model }}"
                                           class="rounded border-wp-border text-wp-blue focus:ring-wp-blue">
                                    Yes
                                </label>
                                @break

                            @case('select')
                                <select name="{{ $base }}" wire:model="{{ $model }}" class="{{ $inputClass }}">
                                    <option value="">— Select —</option>
                                    @foreach ($choices as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @break

                            @case('multiselect')
                                <div class="flex flex-wrap gap-x-4 gap-y-1.5 text-sm text-wp-ink">
                                    @foreach ($choices as $key => $label)
                                        <label class="flex items-center gap-1.5">
                                            <input type="checkbox" name="{{ $base }}[]" value="{{ $key }}" wire:model="{{ $model }}"
                                                   class="rounded border-wp-border text-wp-blue focus:ring-wp-blue">
                                            {{ $label }}
                                        </label>
                                    @endforeach
                                </div>
                                @break

                            @case('image')
                                <livewire:admin.media-picker :name="$base" :value="$row[$column] ?? null"
                                                             :key="'mp-'.$row['_uid'].'-'.$column" />
                                @break

                            @case('images')
                                <livewire:admin.media-picker :name="$base" :multiple="true" :value="$row[$column] ?? []"
                                                             :key="'mp-'.$row['_uid'].'-'.$column" />
                                @break

                            @case('repeater')
                                <div class="rounded border border-wp-border-light bg-gray-50 p-3">
                                    <livewire:admin.repeater-field :name="$base"
                                                                   :subfields="$sub['subfields'] ?? []"
                                                                   :rows="$row[$column] ?? []"
                                                                   :key="'rep-'.$row['_uid'].'-'.$column" />
                                </div>
                                @break

                            @default
                                <input type="text" name="{{ $base }}" wire:model="{{ $model }}" class="{{ $inputClass }}">
                        @endswitch
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <p class="text-sm text-wp-muted">No items yet.</p>
    @endforelse

    <button type="button" wire:click="addRow"
            class="w-fit rounded border border-dashed border-wp-border px-3 py-2 text-sm font-medium text-wp-muted transition hover:border-wp-blue hover:text-wp-blue">
        + Add item
    </button>
</div>
