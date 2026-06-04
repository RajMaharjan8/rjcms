<?php

namespace Rjcodes\Rjcms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Rjcodes\Rjcms\Models\Media;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    /** @var class-string<Media> */
    protected $model = Media::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);
        $extension = fake()->randomElement(['jpg', 'png', 'webp']);
        $fileName = fake()->unique()->slug().'.'.$extension;

        return [
            'name' => $name,
            'file_name' => $fileName,
            'disk' => 'public',
            'path' => 'media/'.$fileName,
            'mime_type' => 'image/'.($extension === 'jpg' ? 'jpeg' : $extension),
            'extension' => $extension,
            'size' => fake()->numberBetween(10_000, 5_000_000),
            'alt_text' => fake()->sentence(),
            'width' => fake()->numberBetween(200, 2000),
            'height' => fake()->numberBetween(200, 2000),
            'uploaded_by' => null,
        ];
    }
}
