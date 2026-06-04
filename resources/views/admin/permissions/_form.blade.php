@php($permission ??= null)

<div class="flex flex-col gap-5">
    <x-admin.field label="Name" name="name" hint="e.g. browse_posts, edit_settings">
        <x-admin.input name="name" :value="$permission?->name" required autofocus />
    </x-admin.field>

    <div class="flex gap-3 pt-2">
        <x-admin.button type="submit">Save</x-admin.button>
        <x-admin.button variant="secondary" :href="route('admin.permissions.index')">Cancel</x-admin.button>
    </div>
</div>
