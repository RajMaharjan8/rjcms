<?php

namespace Rjcodes\Rjcms\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

/**
 * Authenticate admin requests, redirecting guests to the CMS login page.
 *
 * The framework's default `auth` middleware redirects to a route named `login`,
 * which the CMS deliberately doesn't define (it uses `admin.login` to avoid
 * colliding with the host app). This sends guests to the right place instead.
 */
class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('admin.login');
    }
}
