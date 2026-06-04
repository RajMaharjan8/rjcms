<x-admin.field :label="$field->label" :name="$field->column_name">
    <textarea id="{{ $field->column_name }}" name="{{ $field->column_name }}" rows="5" data-rich-editor @required($field->required)
              class="rounded border border-wp-border px-3 py-2 text-sm shadow-sm focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none">{{ old($field->column_name, $value) }}</textarea>
</x-admin.field>

@include('rjcms::admin.partials._rich-editor')
