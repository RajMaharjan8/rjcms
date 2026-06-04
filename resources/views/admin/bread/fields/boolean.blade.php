<x-admin.field :label="$field->label" :name="$field->column_name">
    <label class="flex w-fit items-center gap-2 text-sm text-gray-700">
        <input type="checkbox" name="{{ $field->column_name }}" value="1"
               @checked(old($field->column_name, $value))
               class="rounded border-wp-border text-wp-blue focus:ring-wp-blue">
        Enabled
    </label>
</x-admin.field>
