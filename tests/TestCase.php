<?php

namespace Rjcodes\Rjcms\Tests;

use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Rjcodes\Rjcms\Models\User;
use Rjcodes\Rjcms\RjcmsServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

/**
 * Base test case. Boots a minimal Laravel application (via Orchestra Testbench)
 * containing only this package and its dependencies — the same way a host app
 * would consume it after `composer require rjcodes/rjcms`.
 */
abstract class TestCase extends Orchestra
{
    /**
     * Providers the package relies on. In a real host these are auto-discovered
     * from each package's composer.json; Testbench registers them explicitly.
     *
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            PermissionServiceProvider::class,
            RjcmsServiceProvider::class,
        ];
    }

    /**
     * Load the stock host tables (users, cache, jobs, sessions) to mimic a
     * fresh Laravel app. The package's own migrations then run automatically
     * via the service provider's loadMigrationsFrom().
     */
    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    /**
     * Give the test application an encryption key and a sqlite database.
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
