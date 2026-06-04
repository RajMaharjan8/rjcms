<?php

namespace Rjcodes\Rjcms\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Rjcodes\Rjcms\Database\Factories\MenuItemFactory;

/**
 * A single link within a menu. Items nest via `parent_id` to form sub-menus.
 */
#[Fillable(['menu_id', 'parent_id', 'title', 'url', 'target', 'order'])]
class MenuItem extends Model
{
    /** @use HasFactory<MenuItemFactory> */
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return MenuItemFactory::new();
    }

    /**
     * The menu this item belongs to.
     *
     * @return BelongsTo<Menu, $this>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * The parent item, when this is a sub-menu link.
     *
     * @return BelongsTo<MenuItem, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    /**
     * Direct sub-menu items, ordered.
     *
     * @return HasMany<MenuItem, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    /**
     * Whether the item should open in a new browser tab.
     */
    public function opensInNewTab(): bool
    {
        return $this->target === '_blank';
    }

    /**
     * Whether the item links to the current request URL.
     */
    public function isActive(): bool
    {
        return filled($this->url) && url($this->url) === url()->current();
    }
}
