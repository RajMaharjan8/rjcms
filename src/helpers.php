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
