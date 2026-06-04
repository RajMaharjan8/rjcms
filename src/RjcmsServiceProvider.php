<?php

namespace Rjcodes\Rjcms;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;
use Livewire\Livewire;
use Rjcodes\Rjcms\Console\Commands\InstallCommand;
use Rjcodes\Rjcms\Http\Middleware\UseRjcmsGuard;
use Rjcodes\Rjcms\Models\Bread;
use Rjcodes\Rjcms\Models\Permission;

class RjcmsServiceProvider extends ServiceProvider
{
    /**
     * Register package services and merge configuration.
     *
     * `register()` runs for every package before any `boot()`, so it is the
     * right place to merge config and rebind dependencies (e.g. pointing
     * Spatie's permission package at our extended Permission model).
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/rjcms.php', 'rjcms');
    }

    /**
     * Bootstrap package routes, views, components, migrations and commands.
     */
    public function boot(): void
    {
        // Point Spatie at our extended Permission model (adds `action`/`group`
        // helpers). Done in boot(), not register(): by now Spatie has merged its
        // full permission config, so setting this single key can't clobber its
        // other defaults (models.role, models.team) the way a register-time
        // shallow merge would.
        config(['permission.models.permission' => Permission::class]);

        $this->registerAuthGuard();
        $this->registerRoutes();
        $this->registerViewsAndComponents();
        $this->registerLivewireComponents();

        // Re-apply the CMS guard on Livewire update requests, so component
        // authorization (@can / can:) resolves the package's User model.
        Livewire::addPersistentMiddleware(UseRjcmsGuard::class);
        $this->registerAuthorization();
        $this->registerSidebarComposer();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([InstallCommand::class]);
            $this->registerPublishing();
        }
    }

    /**
     * Register the CMS's own session auth guard, backed by the configured user
     * model. Kept separate from the host's `web` guard so the CMS never depends
     * on (or interferes with) the host application's authentication.
     */
    protected function registerAuthGuard(): void
    {
        $guard = config('rjcms.guard', 'rjcms');
        $provider = $guard.'_users';

        config([
            "auth.guards.{$guard}" => config("auth.guards.{$guard}", [
                'driver' => 'session',
                'provider' => $provider,
            ]),
            "auth.providers.{$provider}" => config("auth.providers.{$provider}", [
                'driver' => 'eloquent',
                'model' => config('rjcms.user_model'),
            ]),
        ]);
    }

    /**
     * Register the admin and public route files inside the configured
     * middleware group. Replaces the host's bootstrap/app.php `then:` closure.
     */
    protected function registerRoutes(): void
    {
        $web = config('rjcms.middleware', ['web']);

        // Admin panel — self-prefixes with config('rjcms.prefix'). UseRjcmsGuard
        // makes the CMS guard the default (so auth()/@can resolve the package
        // User); auth is ALSO pinned explicitly in the route file so it never
        // depends on middleware ordering.
        Route::middleware([...$web, UseRjcmsGuard::class])->group(function (): void {
            $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');
        });

        // Public blog/pages — registered last so admin paths win.
        Route::middleware($web)->group(function (): void {
            $this->loadRoutesFrom(__DIR__.'/../routes/public.php');

            if (config('rjcms.register_root_redirect', false)) {
                Route::redirect('/', '/'.ltrim((string) config('rjcms.prefix', 'admin'), '/'));
            }
        });
    }

    /**
     * Register the package's views under the `rjcms::` namespace and make its
     * anonymous Blade components (<x-admin.*>, <x-menu>) resolve by bare name.
     */
    protected function registerViewsAndComponents(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'rjcms');

        // Anonymous components live in resources/views/components. Registering
        // the path lets <x-admin.button> resolve to components/admin/button.
        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components');
    }

    /**
     * Tell Livewire to discover the package's single-file components, so
     * <livewire:admin.media-picker> resolves from components/admin/.
     */
    protected function registerLivewireComponents(): void
    {
        Livewire::addLocation(__DIR__.'/../resources/views/components');
    }

    /**
     * The super-admin role bypasses every gate check.
     */
    protected function registerAuthorization(): void
    {
        Gate::before(function ($user, string $ability): ?bool {
            return $user->hasRole(config('rjcms.super_admin_role', 'super_admin')) ? true : null;
        });
    }

    /**
     * Feed the admin sidebar its list of BREAD content types, filtered by the
     * viewer's permissions. Guards on Schema so it is safe before migration.
     */
    protected function registerSidebarComposer(): void
    {
        View::composer('rjcms::layouts.admin', function (ViewInstance $view): void {
            $breads = Schema::hasTable('breads')
                ? Bread::orderBy('order')->orderBy('name')->get()
                : collect();

            $view->with('breads', $breads->filter(
                fn (Bread $bread) => ! $bread->isPermissioned()
                    || auth()->user()?->can($bread->permission('browse')),
            )->values());
        });
    }

    /**
     * Declare what `php artisan vendor:publish` can copy into the host app.
     */
    protected function registerPublishing(): void
    {
        // Config — almost always published so installers can tweak it.
        $this->publishes([
            __DIR__.'/../config/rjcms.php' => config_path('rjcms.php'),
        ], 'rjcms-config');

        // Pre-built CSS/JS — published to public/vendor/rjcms (no Node needed).
        $this->publishes([
            __DIR__.'/../resources/dist' => public_path('vendor/rjcms'),
        ], 'rjcms-assets');

        // Views — opt-in, for installers who want to override the markup.
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/rjcms'),
        ], 'rjcms-views');
    }
}
