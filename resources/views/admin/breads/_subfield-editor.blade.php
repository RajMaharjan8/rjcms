{{--
    Recursive sub-field editor for repeater fields.

    @param string $parent     Alpine expression for the object holding `.subfields` (e.g. "field").
    @param string $var        Unique loop variable name for this depth (e.g. "sub", "subc").
    @param int    $depth      Nesting depth of the sub-field list being rendered (0 = top).
    @param int    $maxDepth   Maximum nesting depth for repeaters.
    @param array  $fieldTypes BreadFieldType cases.
--}}
@php($subInput = 'rounded border border-wp-border bg-white px-2 py-1.5 text-sm shadow-sm focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none')

<div class="rounded border border-wp-border-light bg-gray-50 p-3">
    <p class="mb-2 text-xs font-semibold tracking-wide text-wp-muted uppercase">Sub-fields</p>

    <div class="flex flex-col gap-2">
        <template x-for="({{ $var }}, {{ $var }}I) in {{ $parent }}.subfields" :key="{{ $var }}I">
            <div class="rounded border border-wp-border bg-white p-2">
                <div class="flex flex-wrap items-center gap-2">
                    <input type="text" x-model="{{ $var }}.column_name" placeholder="column" class="{{ $subInput }} w-32">
                    <input type="text" x-model="{{ $var }}.label" placeholder="label" class="{{ $subInput }} flex-1">
                    <select x-model="{{ $var }}.type" class="{{ $subInput }}">
                        @foreach ($fieldTypes as $type)
                            @continue ($type === \Rjcodes\Rjcms\Enums\BreadFieldType::Relationship)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                    <label class="flex items-center gap-1 text-xs text-wp-ink">
                        <input type="checkbox" x-model="{{ $var }}.required" class="rounded border-wp-border text-wp-blue focus:ring-wp-blue"> Req
                    </label>
                    <button type="button" @click="removeSubfield({{ $parent }}, {{ $var }}I)"
                            class="px-1.5 text-sm text-red-600 hover:text-red-500" title="Remove sub-field">&times;</button>
                </div>

                {{-- Choices for select / multi-select sub-fields --}}
                <template x-if="['select', 'multiselect'].includes({{ $var }}.type)">
                    <div class="mt-2 rounded border border-wp-border-light bg-gray-50 p-2">
                        <p class="mb-1 text-[11px] font-semibold tracking-wide text-wp-muted uppercase">Choices</p>
                        <div class="flex flex-col gap-1.5">
                            <template x-for="(choice, ci) in {{ $var }}.choices" :key="ci">
                                <div class="flex gap-2">
                                    <input type="text" x-model="choice.key" placeholder="value" class="{{ $subInput }} w-1/3">
                                    <input type="text" x-model="choice.label" placeholder="label" class="{{ $subInput }} flex-1">
                                    <button type="button" @click="removeChoice({{ $var }}, ci)" class="px-2 text-sm text-red-600 hover:text-red-500">&times;</button>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="addChoice({{ $var }})" class="mt-1.5 text-xs font-semibold text-wp-blue hover:text-wp-blue-hover">+ Add choice</button>
                    </div>
                </template>

                {{-- Nested repeater sub-fields --}}
                <template x-if="{{ $var }}.type === 'repeater'">
                    <div class="mt-2">
                        @if ($depth < $maxDepth)
                            @include('rjcms::admin.breads._subfield-editor', [
                                'parent' => $var,
                                'var' => $var.'c',
                                'depth' => $depth + 1,
                                'maxDepth' => $maxDepth,
                                'fieldTypes' => $fieldTypes,
                            ])
                        @else
                            <p class="rounded border border-wp-border-light bg-gray-50 px-2 py-1.5 text-xs text-wp-muted">
                                Maximum nesting depth reached — this repeater can't contain further repeaters.
                            </p>
                        @endif
                    </div>
                </template>
            </div>
        </template>
    </div>

    <button type="button" @click="addSubfield({{ $parent }})"
            class="mt-2 text-xs font-semibold text-wp-blue hover:text-wp-blue-hover">+ Add sub-field</button>
</div>
