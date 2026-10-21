<?php

namespace Database\Seeders;

use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Models\Service;
use Illuminate\Database\Seeder;

class TransportDemoSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['Budi (3 km)',      '081200000001', BookingType::HomeService, 3.0],
            ['Sari (4,3 km)',    '081200000002', BookingType::HomeService, 4.3],
            ['Andi (5 km)',      '081200000003', BookingType::HomeService, 5.0],
            ['Rina (12 km)',     '081200000004', BookingType::HomeService, 12.0],
            ['Joko (bengkel)',   '081200000005', BookingType::Workshop,    null],
        ];

        foreach ($rows as [$name, $phone, $type, $km]) {
            Service::firstOrCreate(
                ['phone' => $phone],
                [
                    'customer_name' => $name,
                    'complaint' => 'Data contoh ongkos',
                    'scheduled_at' => now(),
                    'status' => ServiceStatus::Pending,
                    'booking_type' => $type,
                    'latitude' => $km ? -7.7174465 : null,
                    'longitude' => $km ? 113.0755426 : null,
                    'distance_air_km' => $km,
                    'distance_road_km' => $km,
                    'distance_km' => $km,
                    'distance_method' => $km ? 'ors' : null,
                    'distance_estimated' => false,
                ],
            );
        }
    }
}