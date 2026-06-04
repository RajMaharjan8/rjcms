<?php

namespace Rjcodes\Rjcms\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Rjcodes\Rjcms\Enums\BreadFieldType;
use Rjcodes\Rjcms\Models\Bread;
use Rjcodes\Rjcms\Models\BreadField;

/**
 * @extends Factory<BreadField>
 */
class BreadFieldFactory extends Factory
{
    /** @var class-string<BreadField> */
    protected $model = BreadField::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'bread_id' => Bread::factory(),
            'column_name' => fake()->unique()->word(),
            'label' => fake()->words(2, true),
            'type' => BreadFieldType::Text,
            'required' => false,
            'options' => null,
            'order' => 0,
            'in_browse' => true,
            'in_read' => true,
            'in_edit' => true,
            'in_add' => true,
        ];
    }
}
