<?php

namespace Database\Seeders;

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
                'phone' => '08882509745',
                'latitude' => -7.7174465,
                'longitude' => 113.0755426,
                'maps_url' => 'https://www.google.com/maps?q=-7.7174465,113.0755426',
                'max_distance_km' => 5,
                'fuel_price' => 10000,
            ]
        );
    }
}
