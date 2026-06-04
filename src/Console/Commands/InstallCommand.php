<?php

namespace Rjcodes\Rjcms\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Rjcodes\Rjcms\Database\Seeders\AdminUserSeeder;
use Rjcodes\Rjcms\Database\Seeders\BreadSeeder;
use Rjcodes\Rjcms\Database\Seeders\RolePermissionSeeder;
use Rjcodes\Rjcms\Database\Seeders\SettingSeeder;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * One-command installer for RJ CMS. Idempotent: it is safe to re-run after an
 * upgrade — publishing, migrating and seeding all no-op when already applied.
 */
class InstallCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'rjcms:install
        {--force : Overwrite published config and assets}
        {--demo : Also seed demo BREAD content}
        {--admin-email= : Email for the seeded super admin}
        {--admin-password= : Password for the seeded super admin}';

    /**
     * @var string
     */
    protected $description = 'Install RJ CMS: publish assets, migrate, seed and create the admin account';

    public function handle(): int
    {
        $this->components->info('Installing RJ CMS…');

        $this->publishAssets();

        $this->components->task('Running migrations', function (): void {
            Artisan::call('migrate', ['--force' => true], $this->output);
        });

        $this->seedFoundation();
        $this->createAdmin();
        $this->linkStorage();
        $this->resetPermissionCache();

        if ($this->option('demo')) {
            $this->components->task('Seeding demo content', fn () => (new BreadSeeder)->setContainer($this->laravel)->run());
        }

        $this->newLine();
        $this->components->info('RJ CMS installed. Visit /'.ltrim((string) config('rjcms.prefix', 'admin'), '/').'/login to sign in.');

        return self::SUCCESS;
    }

    /**
     * Publish the config file and pre-built CSS/JS into the host application.
     */
    protected function publishAssets(): void
    {
        $force = (bool) $this->option('force');

        $this->components->task('Publishing config', function () use ($force): void {
            Artisan::call('vendor:publish', array_filter([
                '--tag' => 'rjcms-config',
                '--force' => $force,
            ]));
        });

        $this->components->task('Publishing assets', function (): void {
            // Assets are always force-published so upgrades ship the latest CSS.
            Artisan::call('vendor:publish', [
                '--tag' => 'rjcms-assets',
                '--force' => true,
            ]);
        });
    }

    /**
     * Seed roles, permissions and default site settings (all idempotent).
     */
    protected function seedFoundation(): void
    {
        $this->components->task('Seeding roles & permissions', fn () => (new RolePermissionSeeder)->setContainer($this->laravel)->run());
        $this->components->task('Seeding settings', fn () => (new SettingSeeder)->setContainer($this->laravel)->run());
    }

    /**
     * Create (or update) the super admin account. Prompts interactively unless
     * credentials are supplied via flags or the command runs non-interactively.
     */
    protected function createAdmin(): void
    {
        $email = $this->option('admin-email');
        $password = $this->option('admin-password');

        if (! $email && $this->input->isInteractive()) {
            $email = text('Admin email', default: (string) config('rjcms.admin.email'), required: true);
            $password = password('Admin password (leave blank to keep the default)') ?: config('rjcms.admin.password');
        }

        config([
            'rjcms.admin.email' => $email ?: config('rjcms.admin.email'),
            'rjcms.admin.password' => $password ?: config('rjcms.admin.password'),
        ]);

        $this->components->task('Creating super admin', fn () => (new AdminUserSeeder)->setContainer($this->laravel)->run());

        $this->components->bulletList(['Admin email: '.config('rjcms.admin.email')]);
    }

    /**
     * Create the public storage symlink so uploaded media is web-accessible.
     */
    protected function linkStorage(): void
    {
        if (file_exists(public_path('storage'))) {
            return;
        }

        $this->components->task('Linking storage', function (): void {
            Artisan::call('storage:link');
        });
    }

    /**
     * Reset Spatie's permission cache so freshly seeded permissions resolve.
     */
    protected function resetPermissionCache(): void
    {
        if (! $this->input->isInteractive() && app()->runningUnitTests()) {
            return;
        }

        $this->components->task('Resetting permission cache', function (): void {
            Artisan::call('permission:cache-reset');
        });
    }
}
