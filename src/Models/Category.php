<?php

namespace Rjcodes\Rjcms\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Rjcodes\Rjcms\Database\Factories\CategoryFactory;

/**
 * A blog category. Posts may belong to many categories, and each category has
 * a public archive page at /blog/category/{slug}.
 */
#[Fillable(['name', 'slug', 'description'])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return CategoryFactory::new();
    }

    /**
     * Resolve route-model bindings by slug.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * The posts filed under this category.
     *
     * @return BelongsToMany<Post, $this>
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }
}
