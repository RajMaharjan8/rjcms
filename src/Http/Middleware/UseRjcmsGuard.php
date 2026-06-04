<?php

namespace Rjcodes\Rjcms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Make the CMS's own auth guard the default for the request. Applied to the
 * admin route group AND registered with Livewire as persistent middleware, so
 * Livewire update requests (e.g. changing a listing's page size) resolve the
 * package's User model too — otherwise `@can`/`can:` checks run against the
 * host's default guard and fail with a 403.
 */
class UseRjcmsGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        Auth::shouldUse(config('rjcms.guard', 'rjcms'));

        return $next($request);
    }
}
