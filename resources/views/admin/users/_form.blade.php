@php($user ??= null)

<div class="flex flex-col gap-5">
    <x-admin.field label="Name" name="name">
        <x-admin.input name="name" :value="$user?->name" required autofocus />
    </x-admin.field>

    <x-admin.field label="Email" name="email">
        <x-admin.input name="email" type="email" :value="$user?->email" required />
    </x-admin.field>

    <x-admin.field label="Password" name="password"
                   :hint="$user ? 'Leave blank to keep the current password.' : null">
        <x-admin.input name="password" type="password" autocomplete="new-password" />
    </x-admin.field>

    <x-admin.field label="Confirm password" name="password_confirmation">
        <x-admin.input name="password_confirmation" type="password" autocomplete="new-password" />
    </x-admin.field>

    <x-admin.field label="Roles" name="roles">
        @php($selectedRoles = old('roles', $user?->roles->pluck('name')->all() ?? []))
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
            @forelse ($roles as $role)
                <label class="flex items-center gap-2 rounded-md border border-wp-border-light px-3 py-2 text-sm text-wp-muted">
                    <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                           @checked(in_array($role->name, $selectedRoles))
                           class="rounded border-wp-border text-wp-blue focus:ring-wp-blue">
                    {{ $role->name }}
                </label>
            @empty
                <p class="text-sm text-wp-muted">No roles available.</p>
            @endforelse
        </div>
    </x-admin.field>

    <div class="flex gap-3 pt-2">
        <x-admin.button type="submit">Save</x-admin.button>
        <x-admin.button variant="secondary" :href="route('admin.users.index')">Cancel</x-admin.button>
    </div>
</div>
