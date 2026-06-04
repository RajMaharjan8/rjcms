<?php

namespace Rjcodes\Rjcms\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Rjcodes\Rjcms\Http\Controllers\Controller;
use Rjcodes\Rjcms\Http\Requests\Admin\RoleRequest;
use Rjcodes\Rjcms\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    /**
     * The role name that may not be edited away or deleted.
     */
    private function protectedRole(): string
    {
        return config('rjcms.super_admin_role', 'super_admin');
    }

    /**
     * Permission gates applied per BREAD action.
     *
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:browse_roles', only: ['index']),
            new Middleware('can:view_roles', only: ['show']),
            new Middleware('can:create_roles', only: ['create', 'store']),
            new Middleware('can:edit_roles', only: ['edit', 'update']),
            new Middleware('can:delete_roles', only: ['destroy']),
        ];
    }

    /**
     * Browse all roles.
     */
    public function index(): View
    {
        return view('rjcms::admin.roles.index');
    }

    /**
     * Show the form to add a role.
     */
    public function create(): View
    {
        return view('rjcms::admin.roles.create', ['permissions' => Permission::orderBy('name')->get()]);
    }

    /**
     * Store a new role.
     */
    public function store(RoleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $role = Role::create(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()
            ->route('admin.roles.index')
            ->with('status', 'Role created.');
    }

    /**
     * Read a single role.
     */
    public function show(Role $role): View
    {
        $role->load('permissions');

        return view('rjcms::admin.roles.show', ['role' => $role]);
    }

    /**
     * Show the form to edit a role.
     */
    public function edit(Role $role): View
    {
        return view('rjcms::admin.roles.edit', [
            'role' => $role,
            'permissions' => Permission::orderBy('name')->get(),
        ]);
    }

    /**
     * Update an existing role.
     */
    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();

        if ($role->name !== $this->protectedRole()) {
            $role->update(['name' => $data['name']]);
        }

        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()
            ->route('admin.roles.index')
            ->with('status', 'Role updated.');
    }

    /**
     * Delete a role.
     */
    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === $this->protectedRole()) {
            return redirect()
                ->route('admin.roles.index')
                ->with('error', 'The super_admin role cannot be deleted.');
        }

        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('status', 'Role deleted.');
    }
}
