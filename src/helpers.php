<?php

use Illuminate\Database\Eloquent\Collection;
use Rjcodes\Rjcms\Models\Menu;
use Rjcodes\Rjcms\Models\MenuItem;
use Rjcodes\Rjcms\Models\Setting;

if (! function_exists('menu')) {
    /**
     * Fetch a menu's items as a nested tree by its slug. Each returned item has
     * a `children` collection set (recursively). Returns an empty collection
     * when no menu matches, so it is always safe to loop over in a view.
     *
     * @return Collection<int, MenuItem>
     */
    function menu(string $slug): Collection
    {
        $menu = Menu::where('slug', $slug)->with('items')->first();

        return $menu?->tree() ?? new Collection;
    }
}

if (! function_exists('setting')) {
    /**
     * Resolve a site setting's typed value by key, with an optional default.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return Setting::value($key, $default);
    }
}

if (! function_exists('rjcms_asset')) {
    /**
     * Build a URL to a published CMS asset (public/vendor/rjcms/...), with a
     * cache-busting `?id=` based on the file's modified time. This means a host
     * app that re-publishes assets after an upgrade always serves the fresh
     * file instead of a browser-cached copy.
     */
    function rjcms_asset(string $file): string
    {
        $path = public_path('vendor/rjcms/'.ltrim($file, '/'));
        $version = is_file($path) ? filemtime($path) : null;

        return asset('vendor/rjcms/'.ltrim($file, '/')).($version ? '?id='.$version : '');
    }
}
