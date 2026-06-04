<?php

use Rjcodes\Rjcms\Models\User;

it('installs the cms end to end via rjcms:install', function () {
    $this->artisan('rjcms:install', [
        '--admin-email' => 'boss@example.com',
        '--admin-password' => 'secret-pass',
    ])->assertSuccessful();

    $admin = User::where('email', 'boss@example.com')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->hasRole('super_admin'))->toBeTrue();
});

it('is safe to run rjcms:install twice (idempotent)', function () {
    $this->artisan('rjcms:install', ['--admin-email' => 'boss@example.com'])->assertSuccessful();
    $this->artisan('rjcms:install', ['--admin-email' => 'boss@example.com'])->assertSuccessful();

    expect(User::where('email', 'boss@example.com')->count())->toBe(1);
});
