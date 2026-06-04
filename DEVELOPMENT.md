# How this package was built — a guide to Laravel package development

This document explains, from first principles, how `rjcodes/rjcms` was turned from
a normal Laravel **application** into a reusable Composer **package**. It is meant
to teach you the moving parts so you can build your own package later. Every
example below is real code from this package.

> **TL;DR recipe** is at the bottom ([Quick recipe](#quick-recipe-build-your-own-package)).
> Read the rest first to understand *why* each step exists.

---

## 1. Application vs. package — the mental model

A Laravel **application** owns everything: `app/`, `routes/`, `resources/`,
`config/`, a `bootstrap/app.php`, a `public/` folder, a database. You run it.

A **package** owns *only its own code* and **plugs into** someone else's
application. It has no `bootstrap/app.php`, no `public/index.php`, no `.env`.
Instead it ships:

- PHP classes under a vendor namespace (here `Rjcodes\Rjcms\`),
- a **service provider** that tells the host app "register my routes, views,
  migrations, config, commands",
- assets/config/migrations the host can **publish** (copy) into itself.

The host installs it with `composer require rjcodes/rjcms`, and Laravel's
**package auto-discovery** wires it in. That's the whole game.

```
Application                          Package (this repo)
├── app/            App\             ├── src/            Rjcodes\Rjcms\
├── routes/                          ├── routes/         (loaded by the provider)
├── resources/views/                ├── resources/views/(namespaced "rjcms::")
├── config/                         ├── config/rjcms.php(merged + publishable)
├── database/migrations/            ├── database/migrations/(auto-loaded)
├── bootstrap/app.php  ← entrypoint └── src/RjcmsServiceProvider.php ← entrypoint
└── public/                            (no public/, no bootstrap, no .env)
```

---

## 2. `composer.json` — the package manifest

This file is what makes a folder a Composer package. The important keys:

```jsonc
{
    "name": "rjcodes/rjcms",          // vendor/package — your Packagist id
    "type": "library",                 // not "project"
    "license": "MIT",
    "require": {                       // what the HOST must also have
        "php": "^8.3",
        "illuminate/contracts": "^12.0 || ^13.0",
        "livewire/livewire": "^4.0 || ^4.3",
        "spatie/laravel-permission": "^7.4"
    },
    "require-dev": {                   // only for developing/testing the package
        "orchestra/testbench": "^10.0 || ^11.0",
        "pestphp/pest": "^4.0"
    },
    "autoload": {
        "psr-4": {                     // map namespace -> folder
            "Rjcodes\\Rjcms\\": "src/",
            "Rjcodes\\Rjcms\\Database\\Factories\\": "database/factories/",
            "Rjcodes\\Rjcms\\Database\\Seeders\\": "database/seeders/"
        },
        "files": ["src/helpers.php"]   // global helper functions
    },
    "extra": {
        "laravel": {
            "providers": ["Rjcodes\\Rjcms\\RjcmsServiceProvider"]
        }
    }
}
```

Three things to internalise:

1. **`require` vs `require-dev`.** Anything in `require` gets installed into the
   *host* app too. Keep it minimal — we even **removed** `spatie/laravel-medialibrary`
   after discovering nothing used it (our `Media` model is a plain model on a
   `medias` table). Fewer dependencies = smoother installs.
2. **PSR-4 autoloading** maps a namespace prefix to a directory. `Rjcodes\Rjcms\Models\User`
   lives at `src/Models/User.php`. A package must **not** claim the host's namespaces
   (`App\`, `Database\Seeders\`) — so our seeders/factories live under
   `Rjcodes\Rjcms\Database\…`.
3. **`extra.laravel.providers`** is Laravel **package auto-discovery**. When the host
   runs `composer require`, Laravel reads this and registers the provider
   automatically — the user never edits `config/app.php`.

---

## 3. PSR-4 namespaces — why we rewrote `App\` everywhere

The original app used `App\Models\User`, `App\Http\Controllers\…`, etc. In a
package that namespace is wrong (it would collide with the host's own `App\`).
So the conversion **moved `app/` → `src/`** and rewrote every `App\` to
`Rjcodes\Rjcms\`:

```bash
# move the code
cp -R app/Models src/Models   # …and the rest

# rewrite the namespace in every PHP file
find src routes database -name '*.php' \
  | xargs perl -i -pe 's/\bApp\\/Rjcodes\\Rjcms\\/g'
```

A subtle trap: **Blade files also contained PHP** (`use App\Models\Media;` inside
Livewire single-file components, and `\App\Enums\BreadFieldType` in `@php` blocks).
Those are not `.php` files, so the first pass missed them and Livewire blew up with
*"Class App\Models\User not found"*. The fix was to run the same rewrite over
`resources/views/**/*.blade.php`. **Lesson: search for the old namespace in Blade
too.**

### Factories under a non-default namespace
Laravel normally guesses a model's factory as `Database\Factories\{Model}Factory`.
Because ours live under `Rjcodes\Rjcms\Database\Factories\`, that guess fails. Two
fixes, both applied:

```php
// in each factory
protected $model = \Rjcodes\Rjcms\Models\Post::class;

// in each model
protected static function newFactory(): \Illuminate\Database\Eloquent\Factories\Factory
{
    return \Rjcodes\Rjcms\Database\Factories\PostFactory::new();
}
```

---

## 4. The service provider — the package's brain

`src/RjcmsServiceProvider.php` is where the package tells Laravel about itself.
Two lifecycle methods:

- **`register()`** runs first, for *every* provider, before any `boot()`. Only bind
  things into the container and merge config here — never assume another package has
  booted.
- **`boot()`** runs after all providers registered. Wire up routes, views, etc. here.

### 4a. Merging config (`register()`)
```php
$this->mergeConfigFrom(__DIR__.'/../config/rjcms.php', 'rjcms');
```
This makes `config('rjcms.prefix')` available **without** the host publishing the
file. `mergeConfigFrom` is shallow-merge: the host's published values win, your
defaults fill the gaps. We also repoint Spatie at our extended model:
```php
config(['permission.models.permission' => \Rjcodes\Rjcms\Models\Permission::class]);
```

### 4b. Routes (`boot()`)
The original app registered routes in `bootstrap/app.php`. A package has no such
file, so the provider does it:
```php
Route::middleware(config('rjcms.middleware', ['web']))->group(function () {
    $this->loadRoutesFrom(__DIR__.'/../routes/admin.php');   // self-prefixes "admin"
    $this->loadRoutesFrom(__DIR__.'/../routes/public.php');  // blog, registered last
});
```
We made the URL prefix configurable (`Route::prefix(config('rjcms.prefix','admin'))`)
**but kept the route *name* prefix fixed at `admin.`** — because 100+ views call
`route('admin.dashboard')`. Change the URL, not the names.

We also renamed the bare `login`/`logout` route names to `admin.login`/`admin.logout`
so they can't clash with a host app that already defines `login`.

### 4c. Views — namespacing with `rjcms::`
```php
$this->loadViewsFrom(__DIR__.'/../resources/views', 'rjcms');
```
Now `view('rjcms::admin.dashboard')` resolves to
`resources/views/admin/dashboard.blade.php`. Every internal reference had to gain the
prefix:
```blade
@extends('rjcms::layouts.admin')
@include('rjcms::admin.bread._form')
```
…including **dynamic** view strings built in PHP. The BREAD field enum returns a view
name, so the prefix went *in the PHP*, not the Blade:
```php
public function formView(): string
{
    return "rjcms::admin.bread.fields.{$this->value}";
}
```

### 4d. Components — Blade and Livewire
The CMS uses `<x-admin.button>` (anonymous Blade components) and
`<livewire:admin.media-picker>` (Livewire single-file components). To keep those tags
unchanged (rewriting them with regex is error-prone), we registered their directory:
```php
Blade::anonymousComponentPath(__DIR__.'/../resources/views/components'); // <x-admin.*>
Livewire::addLocation(__DIR__.'/../resources/views/components');         // <livewire:admin.*>
```
`Livewire::addLocation` is the v4 API blessed for packages — see
[Livewire docs → Packages](https://livewire.laravel.com/docs/4.x/packages).

### 4e. Migrations — auto-load, don't publish
```php
$this->loadMigrationsFrom(__DIR__.'/../database/migrations');
```
This means a plain `php artisan migrate` runs the package's migrations — and future
package versions ship new migrations automatically. (Publishing migrations freezes
them at install time, so we *don't*.) We deliberately **excluded** the
`users`/`cache`/`jobs` migrations: a fresh Laravel app already has those, and shipping
duplicates causes "table already exists".

### 4f. Publishing — letting the host copy files
```php
$this->publishes([__DIR__.'/../config/rjcms.php' => config_path('rjcms.php')], 'rjcms-config');
$this->publishes([__DIR__.'/../resources/dist'   => public_path('vendor/rjcms')], 'rjcms-assets');
$this->publishes([__DIR__.'/../resources/views'  => resource_path('views/vendor/rjcms')], 'rjcms-views');
```
The host runs `php artisan vendor:publish --tag=rjcms-config` to get an editable copy.
**Config and assets** are meant to be published; **views** are opt-in (only if you want
to override markup).

---

## 5. Frontend assets without forcing the host to run Node

A package can't rely on the host's Vite build. The original used
`@vite(['resources/css/app.css', ...])` — that won't find package CSS. The solution:

1. The package has its **own** `package.json` + Tailwind v4 and builds its CSS at
   *package-publish time*:
   ```jsonc
   "scripts": { "build": "tailwindcss -i ./resources/css/app.css -o ./resources/dist/app.css --minify" }
   ```
2. Tailwind's `@source` globs point at the **package's** Blade files so the right
   utilities are emitted:
   ```css
   @source '../views/**/*.blade.php';
   ```
3. The built file is **committed** to `resources/dist/` and shipped.
4. The host gets it via the `rjcms-assets` publish tag → `public/vendor/rjcms/`, and
   the layout references it with `asset()`:
   ```blade
   <link rel="stylesheet" href="{{ asset('vendor/rjcms/app.css') }}">
   ```

The host never needs Node, npm, or Tailwind. Pre-built assets = reliable installs.

---

## 6. The install command — one command, smooth setup

`php artisan rjcms:install` ([`src/Console/Commands/InstallCommand.php`](src/Console/Commands/InstallCommand.php))
orchestrates everything, and is **idempotent** (safe to re-run):

1. publish config + assets,
2. `migrate --force`,
3. seed roles/permissions/settings (idempotent `updateOrCreate`/`findOrCreate`),
4. create the super admin (prompts, or `--admin-email`/`--admin-password` flags),
5. `storage:link`,
6. reset Spatie's permission cache.

Seeders ship under `Rjcodes\Rjcms\Database\Seeders\` (not the host's `Database\Seeders\`)
and are invoked by class, e.g. `(new RolePermissionSeeder)->setContainer($this->laravel)->run()`.

---

## 7. Testing a package in isolation — Orchestra Testbench

You can't `php artisan test` a package — it has no application. **Testbench** boots a
minimal throwaway Laravel app around your package. See [`tests/TestCase.php`](tests/TestCase.php):

```php
abstract class TestCase extends Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app): array
    {
        return [LivewireServiceProvider::class, PermissionServiceProvider::class, RjcmsServiceProvider::class];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations'); // stock users/cache/jobs (a fake "host")
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('auth.providers.users.model', User::class); // so Spatie's guard resolves
        // …sqlite :memory: database
    }
}
```

Run `vendor/bin/pest`. Our 14 tests prove the provider boots, routes register,
migrations run, views resolve, **the Livewire single-file component renders from the
package**, and `rjcms:install` works twice in a row. These tests *are* the proof the
package will install cleanly in a real host.

---

## 8. Local development with a path repository

While developing, you want a real app to consume the package without publishing to
Packagist. Use a Composer **path repository** in the host app's `composer.json`:

```jsonc
"repositories": [
    { "type": "path", "url": "packages/rjcms", "options": { "symlink": true } }
],
"require": { "rjcodes/rjcms": "*" }
```

`symlink: true` means edits to `packages/rjcms/src` are live in the host's
`vendor/rjcodes/rjcms`. Run `composer require rjcodes/rjcms:*` and develop normally.

> Note: this repo's root *is itself* the original CMS app, so we validate via Testbench
> instead of wiring the path repo here (doing both would double-register the routes).
> In a brand-new host app, the path repo is the way to go.

---

## 9. Publishing to Packagist

1. Put the package in its **own git repository** (`git init` inside `packages/rjcms`,
   or split it out).
2. Tag a release with **SemVer**: `git tag v0.1.0 && git push --tags`. Composer
   resolves versions from git tags.
3. Go to [packagist.org](https://packagist.org) → *Submit* → paste the repo URL.
4. Add the **GitHub service hook** (Packagist shows you how) so new tags auto-update.
5. Consumers now run `composer require rjcodes/rjcms`.

Versioning rule of thumb (SemVer `MAJOR.MINOR.PATCH`): breaking change → bump MAJOR,
new feature → MINOR, bug fix → PATCH. Pre-1.0 (`0.x`) signals "still stabilising".

---

## Quick recipe: build your own package

The minimum steps, distilled. Say you're building `acme/widgets`:

```bash
mkdir -p packages/widgets/{src,config,database/migrations,resources/views,routes,tests}
cd packages/widgets
composer init   # name: acme/widgets, type: library
```

1. **composer.json** — set PSR-4 `"Acme\\Widgets\\": "src/"`, list runtime deps in
   `require`, add Testbench to `require-dev`, and:
   ```jsonc
   "extra": { "laravel": { "providers": ["Acme\\Widgets\\WidgetsServiceProvider"] } }
   ```
2. **Service provider** `src/WidgetsServiceProvider.php`:
   ```php
   public function register(): void {
       $this->mergeConfigFrom(__DIR__.'/../config/widgets.php', 'widgets');
   }
   public function boot(): void {
       $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
       $this->loadViewsFrom(__DIR__.'/../resources/views', 'widgets');
       $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
       if ($this->app->runningInConsole()) {
           $this->publishes([__DIR__.'/../config/widgets.php' => config_path('widgets.php')], 'widgets-config');
           $this->commands([\Acme\Widgets\Console\InstallCommand::class]);
       }
   }
   ```
3. **Write your code** under `src/`, **views** as `widgets::something`, **config** at
   `config/widgets.php`, **migrations** in `database/migrations/`.
4. **Test** with Testbench: a `tests/TestCase` returning your provider from
   `getPackageProviders()`, then `vendor/bin/pest`.
5. **Consume locally** via a `path` repository in a host app (`symlink: true`).
6. **Ship**: git tag `v0.1.0`, submit to Packagist.

Checklist of gotchas (all hit during this conversion):
- [ ] No `App\` left anywhere — **including Blade files**.
- [ ] Views referenced with the `namespace::` prefix, dynamic view strings too.
- [ ] Don't ship `users`/`cache`/`jobs` migrations (the host already has them).
- [ ] Keep route **names** stable; only make URLs configurable.
- [ ] Factories need `protected $model` + model `newFactory()` under a custom namespace.
- [ ] Pre-build & commit frontend assets; publish them; reference with `asset()`.
- [ ] Keep `require` lean — drop dependencies nothing uses.
- [ ] Make the installer idempotent.
```
