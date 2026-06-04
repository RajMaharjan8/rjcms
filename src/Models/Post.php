<?php

namespace Rjcodes\Rjcms\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Rjcodes\Rjcms\Database\Factories\PostFactory;

/**
 * A blog post — a built-in content type with title, slug, body, featured image
 * and draft/published status, rendered publicly when published.
 */
#[Fillable(['title', 'slug', 'excerpt', 'body', 'featured_image', 'status', 'tags', 'sections', 'is_published', 'published_at', 'meta'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return PostFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'date',
            'tags' => 'array',
            'sections' => 'array',
            'meta' => 'array',
        ];
    }

    /**
     * Read a custom field value from the post's meta.
     */
    public function field(string $key, mixed $default = null): mixed
    {
        return data_get($this->meta, $key, $default);
    }

    /**
     * The post's featured image from the media library, if any.
     *
     * @return BelongsTo<Media, $this>
     */
    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_image');
    }

    /**
     * The categories this post is filed under.
     *
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Whether the post is publicly visible.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Limit a query to published posts.
     *
     * @param  Builder<Post>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published');
    }
}
