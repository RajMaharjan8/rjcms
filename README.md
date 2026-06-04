# RJ CMS

A WordPress-style content management system for Laravel — an admin panel, a visual
**BREAD** field-builder, media library, navigation menus, roles & permissions, site
settings, and a public blog — installable into any Laravel 12/13 app.

```
composer require rjcodes/rjcms
php artisan rjcms:install
```

That's it. Visit `/admin` and sign in.

---

## Requirements

- PHP **8.3+**
- Laravel **12 or 13**
- Livewire **4**

## Installation

1. Require the package:
   ```bash
   composer require rjcodes/rjcms
   ```
2. Run the installer (publishes assets + config, migrates, seeds, creates your admin):
   ```bash
   php artisan rjcms:install
   ```
   You'll be prompted for an admin email and password. Non-interactive / CI:
   ```bash
   php artisan rjcms:install --admin-email="you@example.com" --admin-password="secret" --no-interaction
   ```
3. Open `/admin` and log in.

Want demo content to explore the BREAD builder? Add `--demo`:
```bash
php artisan rjcms:install --demo
```

The installer is **idempotent** — safe to re-run after upgrades.

## What you get

| Feature | Description |
|---|---|
| **Admin panel** | WordPress-style dashboard at `/admin`. |
| **BREAD builder** | Define content types and fields (text, image, relationship, repeater, …) visually — no migrations to write. |
| **Media library** | Upload, browse and pick images; inline uploads from the rich-text editor. |
| **Menus** | Drag-and-drop navigation builder, rendered via the `menu('slug')` helper. |
| **Roles & permissions** | Powered by spatie/laravel-permission, with a `super_admin` that bypasses every gate. |
| **Settings** | Typed, grouped site settings, read with the `setting('key')` helper. |
| **Blog** | Public blog with categories at `/blog`. |

## Configuration

Publish and edit the config if you need to:
```bash
php artisan vendor:publish --tag=rjcms-config
```

`config/rjcms.php` lets you change:

- `prefix` — mount the admin panel somewhere other than `/admin`.
- `user_model` — point the CMS at **your** `User` model (it must use Spatie's
  `HasRoles` trait). Defaults to the package's own `User`.
- `super_admin_role` — the all-powerful role name.
- `relationship_models` — models exposed in the BREAD relationship picker (add your own).
- `register_root_redirect` — set `true` to redirect `/` → the admin panel.

## Upgrading

```bash
composer update rjcodes/rjcms
php artisan migrate
php artisan vendor:publish --tag=rjcms-assets --force
```

## Customising the views

Views are namespaced `rjcms::`. To override any of them, publish and edit:
```bash
php artisan vendor:publish --tag=rjcms-views
```
Published views land in `resources/views/vendor/rjcms/` and take precedence.

## Contributing / developing the package

See **[DEVELOPMENT.md](DEVELOPMENT.md)** — a from-scratch guide to how this package
is built (service providers, publishing, Testbench, Packagist). Run the test suite
with:
```bash
composer install
vendor/bin/pest
```

## License

MIT.
