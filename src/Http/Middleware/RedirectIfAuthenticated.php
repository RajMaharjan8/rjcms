<?php

namespace Rjcodes\Rjcms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keep already-authenticated admins out of the login page, sending them to the
 * dashboard. Checks the CMS guard so it is independent of the host app's auth.
 */
class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard(config('rjcms.guard', 'rjcms'))->check()) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
