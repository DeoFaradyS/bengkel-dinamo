<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Servis dinamo' => 50000,
            'Perbaikan kabel kelistrikan' => 200000,
            'Ganti brush karbon' => 75000,
            'Ganti bearing' => 100000,
            'Rewinding kumparan' => 350000,
        ];

        foreach ($types as $name => $price) {
            ServiceType::updateOrCreate(
                ['name' => $name],
                ['default_price' => $price],
            );
        }
    }
}