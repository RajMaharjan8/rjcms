@php($dateValue = filled($value) ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d') : '')
<x-admin.field :label="$field->label" :name="$field->column_name">
    <input id="{{ $field->column_name }}" name="{{ $field->column_name }}" type="date"
           value="{{ old($field->column_name, $dateValue) }}" @required($field->required)
           class="rounded border border-wp-border px-3 py-2 text-sm shadow-sm focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none">
</x-admin.field>
