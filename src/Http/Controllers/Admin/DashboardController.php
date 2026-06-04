<?php

namespace Rjcodes\Rjcms\Http\Controllers\Admin;

use Illuminate\View\View;
use Rjcodes\Rjcms\Http\Controllers\Controller;
use Rjcodes\Rjcms\Models\Media;
use Rjcodes\Rjcms\Models\Permission;
use Rjcodes\Rjcms\Models\User;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): View
    {
        return view('rjcms::admin.dashboard', [
            'stats' => [
                'users' => User::count(),
                'roles' => Role::count(),
                'permissions' => Permission::count(),
                'medias' => Media::count(),
            ],
        ]);
    }
}
