<?php

namespace Rjcodes\Rjcms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Rjcodes\Rjcms\Models\Post;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /** @var class-string<Post> */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->sentence(12),
            'body' => fake()->paragraphs(3, true),
            'status' => fake()->randomElement(['draft', 'published', 'archived']),
            'tags' => [],
            'sections' => [],
            'is_published' => fake()->boolean(),
            'published_at' => fake()->optional()->dateTimeBetween('-1 year'),
        ];
    }

    /**
     * Indicate that the post is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
            'published_at' => now(),
        ]);
    }
}
