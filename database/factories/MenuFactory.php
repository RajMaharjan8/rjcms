<?php

namespace Rjcodes\Rjcms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Rjcodes\Rjcms\Models\Menu;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    /** @var class-string<Menu> */
    protected $model = Menu::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => str($name)->slug()->value(),
        ];
    }
}
