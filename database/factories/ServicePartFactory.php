<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\SparePart;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServicePartFactory extends Factory
{
    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'spare_part_id' => SparePart::factory(),
            'quantity' => 1,
            'unit_price' => 100000,
        ];
    }
}