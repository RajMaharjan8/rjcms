@php($choices = $field->option('choices', []))
<x-admin.field :label="$field->label" :name="$field->column_name">
    @if (empty($choices))
        <p class="text-sm text-gray-500">No options configured.</p>
    @else
        <x-admin.related-select :name="$field->column_name" :options="$choices" multiple
                                :selected="old($field->column_name, $value ?? [])" :required="$field->required" />
    @endif
</x-admin.field>
