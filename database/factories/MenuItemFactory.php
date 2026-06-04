<?php

namespace Rjcodes\Rjcms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Rjcodes\Rjcms\Models\Menu;
use Rjcodes\Rjcms\Models\MenuItem;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    /** @var class-string<MenuItem> */
    protected $model = MenuItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'parent_id' => null,
            'title' => ucfirst(fake()->words(2, true)),
            'url' => '/'.fake()->slug(),
            'target' => '_self',
            'order' => 0,
        ];
    }
}
