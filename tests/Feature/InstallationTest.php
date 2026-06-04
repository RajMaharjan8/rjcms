<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Rjcodes\Rjcms\Database\Seeders\AdminUserSeeder;
use Rjcodes\Rjcms\Database\Seeders\RolePermissionSeeder;
use Rjcodes\Rjcms\Database\Seeders\SettingSeeder;
use Rjcodes\Rjcms\Models\Permission;
use Rjcodes\Rjcms\Models\User;

it('merges the package configuration', function () {
    expect(config('rjcms.prefix'))->toBe('admin')
        ->and(config('rjcms.super_admin_role'))->toBe('super_admin')
        ->and(config('rjcms.user_model'))->toBe(User::class);
});

it('points spatie permission at the package permission model', function () {
    expect(config('permission.models.permission'))
        ->toBe(Permission::class);
});

it('registers the admin routes with the admin. name prefix', function () {
    expect(Route::has('admin.dashboard'))->toBeTrue()
        ->and(Route::has('admin.login'))->toBeTrue()
        ->and(Route::has('admin.logout'))->toBeTrue()
        ->and(Route::has('admin.breads.index'))->toBeTrue()
        ->and(Route::has('blog.index'))->toBeTrue();
});

it('auto-runs the package migrations', function () {
    foreach (['breads', 'bread_fields', 'settings', 'categories', 'menus', 'menu_items', 'medias', 'permissions', 'roles', 'posts'] as $table) {
        expect(Schema::hasTable($table))->toBeTrue("missing table: {$table}");
    }
});

it('exposes its views under the rjcms namespace', function () {
    expect(View::exists('rjcms::layouts.app'))->toBeTrue()
        ->and(View::exists('rjcms::layouts.admin'))->toBeTrue()
        ->and(View::exists('rjcms::admin.dashboard'))->toBeTrue()
        ->and(View::exists('rjcms::admin.bread.fields.text'))->toBeTrue();
});

it('resolves package anonymous blade components by bare name', function () {
    $html = Blade::render('<x-admin.button href="/x">Save</x-admin.button>');

    expect($html)->toContain('Save')->toContain('href="/x"');
});

it('seeds roles, permissions and a super admin idempotently', function () {
    (new RolePermissionSeeder)->setContainer($this->app)->run();
    (new SettingSeeder)->setContainer($this->app)->run();
    (new AdminUserSeeder)->setContainer($this->app)->run();
    // Re-run to prove idempotency.
    (new RolePermissionSeeder)->setContainer($this->app)->run();
    (new AdminUserSeeder)->setContainer($this->app)->run();

    expect(User::where('email', config('rjcms.admin.email'))->count())->toBe(1);

    $admin = User::where('email', config('rjcms.admin.email'))->first();
    expect($admin->hasRole('super_admin'))->toBeTrue();
});
