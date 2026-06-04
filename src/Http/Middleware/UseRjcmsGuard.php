<?php

namespace Rjcodes\Rjcms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Make the CMS's own auth guard the default for the duration of an admin
 * request. This lets the whole admin area (login, the `auth`/`guest`
 * middleware, the `auth()` helper, gates) use the package's User model —
 * the one with Spatie's HasRoles — instead of the host app's `App\Models\User`.
 */
class UseRjcmsGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        Auth::shouldUse(config('rjcms.guard', 'rjcms'));

        return $next($request);
    }
}
