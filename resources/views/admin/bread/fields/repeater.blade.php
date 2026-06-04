<x-admin.field :label="$field->label" :name="$field->column_name">
    <livewire:admin.repeater-field
        :name="$field->column_name"
        :subfields="$field->option('subfields', [])"
        :rows="old($field->column_name, $value ?? [])"
        :key="'repeater-'.$field->column_name" />
</x-admin.field>
