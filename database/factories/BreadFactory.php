<?php

namespace Rjcodes\Rjcms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Rjcodes\Rjcms\Models\Bread;
use Rjcodes\Rjcms\Models\Post;

/**
 * @extends Factory<Bread>
 */
class BreadFactory extends Factory
{
    /** @var class-string<Bread> */
    protected $model = Bread::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => Str::ucfirst($name),
            'name_plural' => Str::ucfirst(Str::plural($name)),
            'slug' => Str::slug(Str::plural($name)),
            'model' => Post::class,
            'table_name' => 'posts',
            'icon' => null,
            'description' => fake()->optional()->sentence(),
            'order' => 0,
        ];
    }
}
