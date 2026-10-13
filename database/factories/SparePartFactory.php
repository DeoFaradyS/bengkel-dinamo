<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SparePartFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'SP-' . $this->faker->unique()->numerify('####'),
            'name' => $this->faker->words(2, true),
            'stock' => 10,
            'min_stock' => 1,
            'price' => 100000,
        ];
    }
}   