<?php

namespace Rjcodes\Rjcms\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Rjcodes\Rjcms\Models\Media;

/**
 * Casts a stored media ID into a Media model (and back), so fields like
 * `image` resolve straight to a usable Media object in views.
 *
 * @implements CastsAttributes<Media|null, Media|int|string|null>
 */
class SingleMediaCast implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Media
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Media::find((int) $value);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof Media ? $value->id : (int) $value;
    }
}
