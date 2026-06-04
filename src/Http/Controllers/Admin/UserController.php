<?php

namespace Rjcodes\Rjcms\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Rjcodes\Rjcms\Http\Controllers\Controller;
use Rjcodes\Rjcms\Http\Requests\Admin\UserRequest;
use Rjcodes\Rjcms\Models\User;
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{
    /**
     * Permission gates applied per BREAD action.
     *
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:browse_users', only: ['index']),
            new Middleware('can:view_users', only: ['show']),
            new Middleware('can:create_users', only: ['create', 'store']),
            new Middleware('can:edit_users', only: ['edit', 'update']),
            new Middleware('can:delete_users', only: ['destroy']),
        ];
    }

    /**
     * Browse all users.
     */
    public function index(): View
    {
        return view('rjcms::admin.users.index');
    }

    /**
     * Show the form to add a user.
     */
    public function create(): View
    {
        return view('rjcms::admin.users.create', ['roles' => Role::orderBy('name')->get()]);
    }

    /**
     * Store a new user.
     */
    public function store(UserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $user->syncRoles($data['roles'] ?? []);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User created.');
    }

    /**
     * Read a single user.
     */
    public function show(User $user): View
    {
        $user->load('roles');

        return view('rjcms::admin.users.show', ['user' => $user]);
    }

    /**
     * Show the form to edit a user.
     */
    public function edit(User $user): View
    {
        return view('rjcms::admin.users.edit', [
            'user' => $user,
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    /**
     * Update an existing user.
     */
    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();
        $user->syncRoles($data['roles'] ?? []);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User updated.');
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'User deleted.');
    }
}
