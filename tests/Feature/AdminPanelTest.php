<?php

use Livewire\Livewire;
use Rjcodes\Rjcms\Database\Seeders\AdminUserSeeder;
use Rjcodes\Rjcms\Database\Seeders\RolePermissionSeeder;
use Rjcodes\Rjcms\Database\Seeders\SettingSeeder;
use Rjcodes\Rjcms\Models\User;

/**
 * Seed roles/settings and return a super admin (not yet authenticated).
 */
function superAdmin(): User
{
    (new RolePermissionSeeder)->setContainer(app())->run();
    (new SettingSeeder)->setContainer(app())->run();

    $user = User::factory()->create();
    $user->assignRole('super_admin');

    return $user;
}

it('renders the guest login page', function () {
    $this->get('/admin/login')
        ->assertOk()
        ->assertSee('Log In', false);
});

it('redirects unauthenticated visitors to the CMS login page', function () {
    // Must NOT throw "Route [login] not defined" — it redirects to admin.login.
    $this->get('/admin')->assertRedirect(route('admin.login'));
    $this->get('/admin/breads')->assertRedirect(route('admin.login'));
});

it('renders the admin dashboard for a super admin', function () {
    // Authenticate against the CMS's own guard, exactly as the admin area does.
    $this->actingAs(superAdmin(), 'rjcms')
        ->get('/admin')
        ->assertOk();
});

it('renders the BREAD builder index', function () {
    $this->actingAs(superAdmin(), 'rjcms')
        ->get('/admin/breads')
        ->assertOk();
});

it('authenticates the seeded admin through the real login flow', function () {
    // Reproduces a real host: log in via POST, then load /admin. The CMS guard
    // must resolve the package User (with HasRole) — not the host's App\Models\User.
    (new RolePermissionSeeder)->setContainer(app())->run();
    (new SettingSeeder)->setContainer(app())->run();
    (new AdminUserSeeder)->setContainer(app())->run();

    $this->post('/admin/login', [
        'email' => config('rjcms.admin.email'),
        'password' => config('rjcms.admin.password'),
    ])->assertRedirect(route('admin.dashboard'));

    $this->get('/admin')->assertOk();
});

it('resolves a package Livewire single-file component by bare name', function () {
    $this->actingAs(superAdmin(), 'rjcms');

    Livewire::test('admin.users-table')->assertOk();
});

it('renders the public blog index', function () {
    (new SettingSeeder)->setContainer(app())->run();

    $this->get('/blog')->assertOk();
});
