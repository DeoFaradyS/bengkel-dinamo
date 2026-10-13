<?php

namespace Database\Factories;

use App\Enums\ServiceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_name' => $this->faker->name(),
            'phone' => $this->faker->unique()->numerify('08##########'),
            'complaint' => 'Dinamo tidak mau engkol',
            'scheduled_at' => now()->addDay(),
            'status' => ServiceStatus::Confirmed,
        ];
    }

    public function status(ServiceStatus $status): static
    {
        return $this->state(['status' => $status]);
    }

    public function completed(): static
    {
        return $this->status(ServiceStatus::Completed);
    }
    public function cancelled(): static
    {
        return $this->status(ServiceStatus::Cancelled);
    }

    public function rejected(): static
    {
        return $this->status(ServiceStatus::Rejected);
    }
}