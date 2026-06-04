<?php

namespace Rjcodes\Rjcms\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Rjcodes\Rjcms\Http\Controllers\Controller;

/**
 * Lets the signed-in admin update their own username, email, and password.
 */
class AccountController extends Controller
{
    /**
     * Show the account form for the current user.
     */
    public function edit(Request $request): View
    {
        return view('rjcms::admin.account.edit', ['user' => $request->user()]);
    }

    /**
     * Update the current user's name, email, and (optionally) password.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
        ], [
            'current_password.current_password' => 'Your current password is incorrect.',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (filled($validated['password'] ?? null)) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()
            ->route('admin.account.edit')
            ->with('status', 'Account updated.');
    }
}
