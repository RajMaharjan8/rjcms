<?php

namespace Rjcodes\Rjcms\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Rjcodes\Rjcms\Http\Controllers\Controller;
use Rjcodes\Rjcms\Http\Requests\Admin\PermissionRequest;
use Rjcodes\Rjcms\Models\Permission;

class PermissionController extends Controller implements HasMiddleware
{
    /**
     * Permission gates applied per BREAD action.
     *
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:browse_permissions', only: ['index']),
            new Middleware('can:view_permissions', only: ['show']),
            new Middleware('can:create_permissions', only: ['create', 'store']),
            new Middleware('can:edit_permissions', only: ['edit', 'update']),
            new Middleware('can:delete_permissions', only: ['destroy']),
        ];
    }

    /**
     * Browse all permissions.
     */
    public function index(): View
    {
        return view('rjcms::admin.permissions.index');
    }

    /**
     * Show the form to add a permission.
     */
    public function create(): View
    {
        return view('rjcms::admin.permissions.create');
    }

    /**
     * Store a new permission.
     */
    public function store(PermissionRequest $request): RedirectResponse
    {
        Permission::create(['name' => $request->validated('name')]);

        return redirect()
            ->route('admin.permissions.index')
            ->with('status', 'Permission created.');
    }

    /**
     * Read a single permission.
     */
    public function show(Permission $permission): View
    {
        $permission->load('roles');

        return view('rjcms::admin.permissions.show', ['permission' => $permission]);
    }

    /**
     * Show the form to edit a permission.
     */
    public function edit(Permission $permission): View
    {
        return view('rjcms::admin.permissions.edit', ['permission' => $permission]);
    }

    /**
     * Update an existing permission.
     */
    public function update(PermissionRequest $request, Permission $permission): RedirectResponse
    {
        $permission->update(['name' => $request->validated('name')]);

        return redirect()
            ->route('admin.permissions.index')
            ->with('status', 'Permission updated.');
    }

    /**
     * Delete a permission.
     */
    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return redirect()
            ->route('admin.permissions.index')
            ->with('status', 'Permission deleted.');
    }
}
