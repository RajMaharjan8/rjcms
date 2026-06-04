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

<form method="POST" action="{{ $action }}" class="max-w-3xl"
      @submit="$refs.payload.value = JSON.stringify(form)"
      x-data="{
          form: @js($state),
          addChoice(owner) { owner.choices.push({ key: '', label: '' }); },
          removeChoice(owner, i) { owner.choices.splice(i, 1); },
          addSubfield(parent) { parent.subfields.push({ column_name: '', label: '', type: 'text', required: false, choices: [], subfields: [] }); },
          removeSubfield(parent, i) { parent.subfields.splice(i, 1); },
      }">
    @csrf
    @if (($method ?? 'POST') !== 'POST')
        @method($method)
    @endif
    <input type="hidden" name="payload" x-ref="payload">

    <x-admin.postbox title="Custom Field" body-class="px-5 py-1">
        <div class="divide-y divide-wp-border-light">
            <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-center sm:gap-4">
                <label class="text-sm font-semibold text-wp-ink">Key</label>
                <div>
                    <input type="text" x-model="form.key" placeholder="subtitle" class="{{ $inputClass }}">
                    <p class="mt-1 text-xs text-wp-muted">Letters, numbers, underscores. Read in Blade with <code class="rounded bg-gray-100 px-1 py-0.5 font-mono">$record->field('<span x-text="form.key || 'subtitle'"></span>')</code></p>
                </div>
            </div>
            <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-center sm:gap-4">
                <label class="text-sm font-semibold text-wp-ink">Label</label>
                <input type="text" x-model="form.label" placeholder="Subtitle" class="{{ $inputClass }}">
            </div>
            <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-center sm:gap-4">
                <label class="text-sm font-semibold text-wp-ink">Type</label>
                <select x-model="form.type" class="{{ $inputClass }}">
                    @foreach ($fieldTypes as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-center sm:gap-4">
                <label class="text-sm font-semibold text-wp-ink">Order</label>
                <input type="number" x-model.number="form.order" class="{{ $inputClass }} sm:max-w-32">
            </div>
            <div class="grid gap-2 py-4 sm:grid-cols-[180px_minmax(0,1fr)] sm:items-center sm:gap-4">
                <label class="text-sm font-semibold text-wp-ink">Required</label>
                <label class="flex items-center gap-2 text-sm text-wp-ink">
                    <input type="checkbox" x-model="form.required" class="rounded border-wp-border text-wp-blue focus:ring-wp-blue">
                    Value must be filled in
                </label>
            </div>

            {{-- Choices for dropdown / multi-select --}}
            <template x-if="['select', 'multiselect'].includes(form.type)">
                <div class="py-4">
                    <p class="mb-2 text-xs font-semibold tracking-wide text-wp-muted uppercase">Choices</p>
                    <div class="flex flex-col gap-2">
                        <template x-for="(choice, ci) in form.choices" :key="ci">
                            <div class="flex gap-2">
                                <input type="text" x-model="choice.key" placeholder="value" class="{{ $inputClass }} w-1/3">
                                <input type="text" x-model="choice.label" placeholder="label" class="{{ $inputClass }} flex-1">
                                <button type="button" @click="removeChoice(form, ci)" class="px-2 text-sm text-red-600 hover:text-red-500">&times;</button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="addChoice(form)" class="mt-2 text-xs font-semibold text-wp-blue hover:text-wp-blue-hover">+ Add choice</button>
                </div>
            </template>

            {{-- Sub-fields for repeater --}}
            <template x-if="form.type === 'repeater'">
                <div class="py-4">
                    @include('rjcms::admin.breads._subfield-editor', [
                        'parent' => 'form',
                        'var' => 'sub',
                        'depth' => 0,
                        'maxDepth' => $maxNestDepth,
                        'fieldTypes' => $fieldTypes,
                    ])
                </div>
            </template>
        </div>

        <x-slot:footer>
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.content-fields.index', $group) }}" class="text-sm font-medium text-wp-muted hover:text-wp-ink">Cancel</a>
                <x-admin.wp-button type="submit">{{ $submitLabel }}</x-admin.wp-button>
            </div>
        </x-slot:footer>
    </x-admin.postbox>
</form>
