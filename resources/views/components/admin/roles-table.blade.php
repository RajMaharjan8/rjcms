<?php

use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

new class extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public string $sortDirection = 'asc';

    /**
     * Reset to the first page whenever the search term changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Toggle the sort direction on the role name column.
     */
    public function toggleSort(): void
    {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
    }

    /**
     * The paginated, filtered list of roles.
     */
    #[Computed]
    public function roles()
    {
        return Role::withCount(['permissions', 'users'])
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('name', $this->sortDirection)
            ->paginate(10);
    }
};
?>

<div>
    <div class="mb-4 flex items-center gap-3">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search roles…"
               class="w-full rounded border border-wp-border px-3 py-2 text-sm shadow-sm focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none sm:max-w-xs">
        <span wire:loading wire:target="search" class="text-sm text-wp-muted">Searching…</span>
    </div>

    <div class="overflow-x-auto rounded-md border border-wp-border bg-white shadow-sm">
        <table class="min-w-full divide-y divide-wp-border text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold tracking-wide text-wp-muted uppercase">
                <tr>
                    <th class="cursor-pointer px-4 py-3 select-none" wire:click="toggleSort">
                        Name <span class="text-wp-muted">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                    </th>
                    <th class="px-4 py-3">Permissions</th>
                    <th class="px-4 py-3">Users</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-wp-border-light">
                @forelse ($this->roles as $role)
                    <tr wire:key="role-{{ $role->id }}" class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-wp-ink">{{ $role->name }}</td>
                        <td class="px-4 py-3 text-wp-muted">{{ $role->permissions_count }}</td>
                        <td class="px-4 py-3 text-wp-muted">{{ $role->users_count }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-3 text-sm font-medium">
                                @can('view_roles')
                                    <a href="{{ route('admin.roles.show', $role) }}" class="text-wp-muted hover:text-wp-ink">View</a>
                                @endcan
                                @can('edit_roles')
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="text-wp-blue hover:text-wp-blue-hover">Edit</a>
                                @endcan
                                @can('delete_roles')
                                    <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                                          onsubmit="return confirm('Delete this role?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-500">Delete</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-wp-muted">
                            {{ $search !== '' ? 'No roles match your search.' : 'No roles yet.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $this->roles->links() }}</div>
</div>
