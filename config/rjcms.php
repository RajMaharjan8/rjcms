<?php

use Rjcodes\Rjcms\Models\Bread;
use Rjcodes\Rjcms\Models\BreadField;
use Rjcodes\Rjcms\Models\Category;
use Rjcodes\Rjcms\Models\CustomField;
use Rjcodes\Rjcms\Models\Media;
use Rjcodes\Rjcms\Models\Menu;
use Rjcodes\Rjcms\Models\MenuItem;
use Rjcodes\Rjcms\Models\Post;
use Rjcodes\Rjcms\Models\Setting;
use Rjcodes\Rjcms\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Authenticatable user model
    |--------------------------------------------------------------------------
    |
    | The model the CMS authenticates and assigns roles to. Defaults to the
    | package's own User. Point this at your application's User model if you
    | want the CMS to use it — that model must use Spatie's `HasRoles` trait.
    |
    */
    'user_model' => User::class,

    /*
    |--------------------------------------------------------------------------
    | Auth guard
    |--------------------------------------------------------------------------
    |
    | The CMS registers and uses its own session guard (backed by user_model)
    | so it never touches the host app's default `web` guard / User model.
    | Change this only if "rjcms" collides with a guard you already define.
    |
    */
    'guard' => 'rjcms',

    /*
    |--------------------------------------------------------------------------
    | Admin panel URL prefix
    |--------------------------------------------------------------------------
    |
    | The URL segment the admin panel is mounted under, e.g. "admin" => /admin.
    | Only the URL changes — route *names* stay `admin.*` regardless, so links
    | built with route('admin.dashboard') keep working.
    |
    */
    'prefix' => 'admin',

    /*
    |--------------------------------------------------------------------------
    | Route middleware
    |--------------------------------------------------------------------------
    |
    | Middleware applied to all CMS routes (admin + public). The CMS relies on
    | session + CSRF, so keep the `web` group.
    |
    */
    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Register the "/" -> "/admin" redirect
    |--------------------------------------------------------------------------
    |
    | When true the package redirects the site root to the admin panel. Left
    | off by default so the package never hijacks an existing app's homepage.
    |
    */
    'register_root_redirect' => false,

    /*
    |--------------------------------------------------------------------------
    | Super admin role
    |--------------------------------------------------------------------------
    |
    | The role granted every permission via a Gate::before bypass and protected
    | from deletion. Seeded automatically.
    |
    */
    'super_admin_role' => 'super_admin',

    /*
    |--------------------------------------------------------------------------
    | Default admin account
    |--------------------------------------------------------------------------
    |
    | Seeded by `php artisan rjcms:install`. Override the email/password at
    | install time with --admin-email / --admin-password, or here. Change the
    | password immediately in production.
    |
    */
    'admin' => [
        'name' => 'Super Admin',
        'email' => env('RJCMS_ADMIN_EMAIL', 'admin@admin.com'),
        'password' => env('RJCMS_ADMIN_PASSWORD', 'password'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Relationship picker models
    |--------------------------------------------------------------------------
    |
    | Eloquent models a BREAD "relationship" field may point at. Add your own
    | application models here to expose them in the BREAD builder.
    |
    */
    'relationship_models' => [
        Post::class,
        Category::class,
        User::class,
        Menu::class,
        MenuItem::class,
        Media::class,
        Setting::class,
        Bread::class,
        BreadField::class,
        CustomField::class,
    ],

];
