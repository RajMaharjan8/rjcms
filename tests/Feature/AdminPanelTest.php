<?php

use Livewire\Livewire;
use Rjcodes\Rjcms\Database\Seeders\RolePermissionSeeder;
use Rjcodes\Rjcms\Database\Seeders\SettingSeeder;
use Rjcodes\Rjcms\Models\User;

/**
 * Seed roles/settings and return an authenticated super admin.
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

it('renders the admin dashboard for a super admin', function () {
    $this->actingAs(superAdmin())
        ->get('/admin')
        ->assertOk();
});

it('renders the BREAD builder index', function () {
    $this->actingAs(superAdmin())
        ->get('/admin/breads')
        ->assertOk();
});

it('resolves a package Livewire single-file component by bare name', function () {
    $this->actingAs(superAdmin());

    Livewire::test('admin.users-table')->assertOk();
});

it('renders the public blog index', function () {
    SettingSeeder::class && (new SettingSeeder)->setContainer(app())->run();

    $this->get('/blog')->assertOk();
});
