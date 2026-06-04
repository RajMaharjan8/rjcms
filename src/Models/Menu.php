<?php

namespace Rjcodes\Rjcms\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Rjcodes\Rjcms\Database\Factories\MenuFactory;

/**
 * A named navigation menu, fetched on the front end by its slug.
 */
#[Fillable(['name', 'slug'])]
class Menu extends Model
{
    /** @use HasFactory<MenuFactory> */
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return MenuFactory::new();
    }

    /**
     * Every item in the menu, flat and ordered.
     *
     * @return HasMany<MenuItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('order');
    }

    /**
     * Build the menu's items into a nested tree of root items, each with a
     * `children` relation set (recursively). Runs a single query.
     *
     * @return Collection<int, MenuItem>
     */
    public function tree(): Collection
    {
        $byParent = $this->items->groupBy(fn (MenuItem $item): string => (string) $item->parent_id);

        $attach = function (string $parentId) use (&$attach, $byParent): Collection {
            return ($byParent->get($parentId) ?? new Collection)
                ->each(fn (MenuItem $item) => $item->setRelation('children', $attach((string) $item->id)));
        };

        return $attach('');
    }
}
