@php($role ??= null)
@php($isProtected = $role && $role->name === 'super_admin')
@php($selectedPermissions = old('permissions', $role?->permissions->pluck('name')->all() ?? []))

<div class="flex flex-col gap-5">
    <x-admin.field label="Name" name="name"
                   :hint="$isProtected ? 'The super_admin role name is locked.' : null">
        <input id="name" name="name" type="text" value="{{ old('name', $role?->name) }}" required @readonly($isProtected)
               class="rounded border border-wp-border px-3 py-2 text-sm shadow-sm read-only:bg-gray-100 focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none">
    </x-admin.field>

    <x-admin.field label="Permissions" name="permissions">
        <div class="flex flex-col gap-3">
            @forelse ($permissions->groupBy('group')->sortKeys() as $group => $groupPermissions)
                <div x-data="{ toggleAll(checked) { this.$refs.list.querySelectorAll('input[type=checkbox]').forEach((c) => (c.checked = checked)); } }"
                     class="rounded-md border border-wp-border-light">
                    <div class="flex items-center justify-between border-b border-wp-border-light bg-gray-50 px-3 py-2">
                        <span class="text-xs font-semibold tracking-wide text-wp-muted uppercase">{{ ucfirst($group) }}</span>
                        <label class="flex items-center gap-1.5 text-xs text-wp-muted">
                            <input type="checkbox" @change="toggleAll($event.target.checked)"
                                   class="rounded border-wp-border text-wp-blue focus:ring-wp-blue">
                            Select all
                        </label>
                    </div>
                    <div x-ref="list" class="grid grid-cols-2 gap-2 p-3 sm:grid-cols-3">
                        @foreach ($groupPermissions as $permission)
                            <label class="flex items-center gap-2 text-sm text-wp-muted">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                       @checked(in_array($permission->name, $selectedPermissions))
                                       class="rounded border-wp-border text-wp-blue focus:ring-wp-blue">
                                {{ ucfirst($permission->action) }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-sm text-wp-muted">No permissions available.</p>
            @endforelse
        </div>
    </x-admin.field>

    <div class="flex gap-3 pt-2">
        <x-admin.button type="submit">Save</x-admin.button>
        <x-admin.button variant="secondary" :href="route('admin.roles.index')">Cancel</x-admin.button>
    </div>
</div>
