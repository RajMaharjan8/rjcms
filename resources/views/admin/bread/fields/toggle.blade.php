<x-admin.field :label="$field->label" :name="$field->column_name">
    <label class="inline-flex w-fit cursor-pointer items-center gap-3">
        <span class="relative inline-flex h-6 w-11 shrink-0">
            <input type="checkbox" name="{{ $field->column_name }}" value="1"
                   @checked(old($field->column_name, $value))
                   class="peer sr-only">
            <span class="h-6 w-11 rounded-full bg-gray-300 transition-colors peer-checked:bg-wp-blue peer-focus-visible:ring-2 peer-focus-visible:ring-wp-blue/40"></span>
            <span class="absolute top-0.5 left-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
        </span>
    </label>
</x-admin.field>
