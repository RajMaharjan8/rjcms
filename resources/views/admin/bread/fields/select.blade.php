@php($choices = $field->option('choices', []))
<x-admin.field :label="$field->label" :name="$field->column_name">
    <select id="{{ $field->column_name }}" name="{{ $field->column_name }}" @required($field->required)
            class="rounded border border-wp-border px-3 py-2 text-sm shadow-sm focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none">
        @unless ($field->required)
            <option value="">&mdash; Select &mdash;</option>
        @endunless
        @foreach ($choices as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) old($field->column_name, $value) === (string) $optionValue)>
                {{ $optionLabel }}
            </option>
        @endforeach
    </select>
</x-admin.field>
