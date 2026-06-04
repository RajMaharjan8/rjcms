<?php

use Rjcodes\Rjcms\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    /**
     * Reset to the first page whenever the search term changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Toggle sorting for the given column.
     */
    public function sortBy(string $field): void
    {
        $this->sortDirection = $this->sortField === $field && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortField = $field;
    }

    /**
     * The paginated, filtered list of users.
     */
    #[Computed]
    public function users()
    {
        $sortable = ['name', 'email', 'created_at'];

        return User::with('roles')
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->orderBy(in_array($this->sortField, $sortable, true) ? $this->sortField : 'created_at', $this->sortDirection)
            ->paginate(10);
    }
};
?>

<div>
    <div class="mb-4 flex items-center gap-3">
        <div class="relative flex-1 sm:max-w-xs">
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search name or email…"
                   class="w-full rounded border border-wp-border px-3 py-2 text-sm shadow-sm focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none">
        </div>
        <span wire:loading wire:target="search" class="text-sm text-wp-muted">Searching…</span>
    </div>

    <div class="overflow-x-auto rounded-md border border-wp-border bg-white shadow-sm">
        <table class="min-w-full divide-y divide-wp-border text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold tracking-wide text-wp-muted uppercase">
                <tr>
                    @foreach (['name' => 'Name', 'email' => 'Email'] as $field => $label)
                        <th class="cursor-pointer px-4 py-3 select-none" wire:click="sortBy('{{ $field }}')">
                            {{ $label }}
                            @if ($sortField === $field)
                                <span class="text-wp-muted">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </th>
                    @endforeach
                    <th class="px-4 py-3">Roles</th>
                    <th class="cursor-pointer px-4 py-3 select-none" wire:click="sortBy('created_at')">
                        Created
                        @if ($sortField === 'created_at')
                            <span class="text-wp-muted">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @endif
                    </th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-wp-border-light">
                @forelse ($this->users as $user)
                    <tr wire:key="user-{{ $user->id }}" class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-wp-ink">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-wp-muted">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @forelse ($user->roles as $role)
                                    <span class="rounded bg-blue-50 px-2 py-0.5 text-xs font-medium text-wp-blue">{{ $role->name }}</span>
                                @empty
                                    <span class="text-wp-muted">&mdash;</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-4 py-3 text-wp-muted">{{ $user->created_at->format('M j, Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-3 text-sm font-medium">
                                @can('view_users')
                                    <a href="{{ route('admin.users.show', $user) }}" class="text-wp-muted hover:text-wp-ink">View</a>
                                @endcan
                                @can('edit_users')
                                    <a href="{{ route('admin.users.edit', $user) }}" class="text-wp-blue hover:text-wp-blue-hover">Edit</a>
                                @endcan
                                @can('delete_users')
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                          onsubmit="return confirm('Delete this user?')">
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
                        <td colspan="5" class="px-4 py-10 text-center text-wp-muted">
                            {{ $search !== '' ? 'No users match your search.' : 'No users yet.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $this->users->links() }}</div>
</div>
