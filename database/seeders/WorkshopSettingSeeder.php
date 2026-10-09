<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\WorkshopSetting;
use Illuminate\Database\Seeder;

class WorkshopSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WorkshopSetting::updateOrCreate(
            ['id' => 1],
            [
                'latitude' => -7.7174465,
                'longitude' => 113.0755426,
                'max_distance_km' => 5,
                'price_per_km' => 3000,
            ]
        );
    }
}
