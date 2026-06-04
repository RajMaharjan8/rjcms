<x-admin.field :label="$field->label" :name="$field->column_name">
    <livewire:admin.media-picker :name="$field->column_name" :value="$value" :multiple="true"
                                 :key="'media-picker-'.$field->column_name" />
</x-admin.field>
