@php($handler = $field->handler())
@php($options = $handler->options($field))
@php($multiple = $handler->isMultiple($field))
<x-admin.field :label="$field->label" :name="$field->column_name">
    @if ($options->isEmpty())
        <p class="text-sm text-gray-500">No related records available — check the relationship's model and keys in the BREAD builder.</p>
    @elseif ($multiple)
        <x-admin.related-select :name="$field->column_name" :options="$options" multiple
                                :selected="old($field->column_name, $value ?? [])" :required="$field->required" />
    @else
        <x-admin.related-select :name="$field->column_name" :options="$options"
                                :selected="old($field->column_name, $value)" :required="$field->required" />
    @endif
</x-admin.field>
