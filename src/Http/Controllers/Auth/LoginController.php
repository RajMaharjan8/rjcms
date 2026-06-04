<?php

namespace Rjcodes\Rjcms\Http\Controllers\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Rjcodes\Rjcms\Http\Controllers\Controller;
use Rjcodes\Rjcms\Http\Requests\Auth\LoginRequest;

class LoginController extends Controller
{
    /**
     * Display the login form.
     */
    public function show(): View
    {
        return view('rjcms::auth.login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Log the user out of the application.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard(config('rjcms.guard', 'rjcms'))->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
