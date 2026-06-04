<?php

use Rjcodes\Rjcms\Models\Permission;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    #[Url(as: 'q', except: '')]
    public string $search = '';

    /**
     * Permissions matching the search, grouped by their resource.
     */
    #[Computed]
    public function groups()
    {
        return Permission::withCount('roles')
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('name')
            ->get()
            ->groupBy('group')
            ->sortKeys();
    }
};
?>

<div>
    @php($total = $this->groups->reduce(fn ($carry, $permissions) => $carry + $permissions->count(), 0))
    @php($actionStyles = [
        'browse' => 'bg-slate-100 text-slate-600',
        'view' => 'bg-sky-100 text-sky-700',
        'create' => 'bg-emerald-100 text-emerald-700',
        'edit' => 'bg-amber-100 text-amber-700',
        'delete' => 'bg-rose-100 text-rose-700',
    ])

    {{-- Toolbar --}}
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="relative w-full sm:max-w-xs">
            <svg class="pointer-events-none absolute top-2.5 left-3 h-4 w-4 text-wp-muted" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.45 4.39l3.08 3.08a1 1 0 01-1.42 1.42l-3.08-3.08A7 7 0 012 9z" clip-rule="evenodd" />
            </svg>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search permissions…"
                   class="w-full rounded-md border border-wp-border bg-white py-2 pr-3 pl-9 text-sm shadow-sm focus:border-wp-blue focus:ring-1 focus:ring-wp-blue focus:outline-none">
        </div>
        <div class="flex items-center gap-3 text-sm text-wp-muted">
            <span wire:loading wire:target="search">Searching…</span>
            <span><span class="font-semibold text-wp-ink">{{ $total }}</span> permission{{ $total === 1 ? '' : 's' }} · {{ $this->groups->count() }} group{{ $this->groups->count() === 1 ? '' : 's' }}</span>
        </div>
    </div>

    {{-- Groups --}}
    <div class="grid items-start gap-5 lg:grid-cols-2">
        @forelse ($this->groups as $group => $permissions)
            <div wire:key="group-{{ $group }}" class="overflow-hidden rounded-lg border border-wp-border bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-wp-border bg-linear-to-r from-gray-50 to-white px-4 py-3">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-7 w-7 items-center justify-center rounded-md bg-wp-blue/10 text-xs font-bold text-wp-blue">{{ strtoupper(substr($group, 0, 1)) }}</span>
                        <h2 class="text-sm font-semibold text-wp-ink">{{ ucfirst($group) }}</h2>
                    </div>
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-wp-muted">{{ $permissions->count() }}</span>
                </div>
                <ul class="divide-y divide-wp-border-light">
                    @foreach ($permissions as $permission)
                        @php($action = (string) str($permission->name)->before('_'))
                        <li wire:key="permission-{{ $permission->id }}" class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50">
                            <span class="inline-flex w-16 justify-center rounded px-2 py-0.5 text-[11px] font-semibold capitalize {{ $actionStyles[$action] ?? 'bg-gray-100 text-gray-600' }}">{{ $action }}</span>
                            <span class="flex-1 truncate font-mono text-xs text-wp-ink">{{ $permission->name }}</span>
                            <span class="hidden whitespace-nowrap text-xs text-wp-muted sm:inline">{{ $permission->roles_count }} role{{ $permission->roles_count === 1 ? '' : 's' }}</span>
                            <div class="flex items-center gap-2.5 text-xs font-medium">
                                @can('view_permissions')
                                    <a href="{{ route('admin.permissions.show', $permission) }}" class="text-wp-muted hover:text-wp-ink">View</a>
                                @endcan
                                @can('edit_permissions')
                                    <a href="{{ route('admin.permissions.edit', $permission) }}" class="text-wp-blue hover:text-wp-blue-hover">Edit</a>
                                @endcan
                                @can('delete_permissions')
                                    <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}"
                                          onsubmit="return confirm('Delete this permission?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-500">Delete</button>
                                    </form>
                                @endcan
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @empty
            <div class="rounded-md border border-wp-border bg-white p-10 text-center text-wp-muted lg:col-span-2">
                {{ $search !== '' ? 'No permissions match your search.' : 'No permissions yet.' }}
            </div>
        @endforelse
    </div>
</div>
