<?php

namespace Rjcodes\Rjcms\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Rjcodes\Rjcms\Models\Media;

/**
 * Casts a JSON array of media IDs into a Collection of Media models (and back),
 * preserving the stored order. Lets gallery fields be looped directly in views.
 *
 * @implements CastsAttributes<Collection<int, Media>, iterable<Media|int|string>|null>
 */
class MediaCollectionCast implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     * @return Collection<int, Media>
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Collection
    {
        $ids = array_map('intval', json_decode($value ?: '[]', true) ?: []);

        if ($ids === []) {
            return new Collection;
        }

        return Media::whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Media $media): int|false => array_search($media->id, $ids))
            ->values();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        $ids = collect($value)
            ->map(fn ($item): int => $item instanceof Media ? $item->id : (int) $item)
            ->values()
            ->all();

        return json_encode($ids);
    }
}
