<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceType;
use App\Models\SparePart;
use Illuminate\Database\Seeder;
use App\Models\WorkshopSetting;
use App\Support\Distance\DistanceService;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $types = ServiceType::all()->keyBy('name');
        $parts = SparePart::all()->keyBy('name');

        // [nama, hp, keluhan, status, jasa[[nama katalog, harga atau null]], hari, spare part[[nama, qty]], alasan batal]
        // harga null = pakai harga katalog
        $data = [
            [
                'Pak Wawan Setiawan',
                '081311223340',
                'Starter pickup mati di garasi rumah, minta montir datang',
                'pending',
                [],
                1,
                [],
                null,
                ['lat' => -7.7188, 'lng' => 113.0790],
            ],
            [
                'Bu Ani Rahayu',
                '081311223341',
                'Dinamo pompa air rumah tidak mau menyala',
                'confirmed',
                [['Servis dinamo', null]],
                2,
                [],
                null,
                ['lat' => -7.7201, 'lng' => 113.0712, 'address' => 'Gang Melati 3, depan Indomaret, pagar hijau'],
            ],
            [
                'Pak Rudi Hartono',
                '081311223342',
                'Alternator mobil di garasi tidak ngecas',
                'completed',
                [['Servis dinamo', null]],
                -4,
                [['Kohlen / Carbon Brush Alternator (set)', 1]],
                null,
                ['lat' => -7.7165, 'lng' => 113.0830, 'address' => 'Jl. Kenanga 12, sebelah bengkel las, gerbang biru'],
            ],
            [
                'Pak Budi Santoso',
                '081234567801',
                'Dinamo starter Avanza ngik-ngik, tidak mau engkol',
                'completed',
                [['Servis dinamo', null], ['Ganti brush karbon', null]],
                -10,
                [['Bendix Starter 12V', 1], ['Kohlen / Carbon Brush Starter (set)', 1]],
            ],
            [
                'CV Maju Jaya',
                '081345678902',
                'Starter truk Hino 24V lemah, harus didorong',
                'completed',
                [['Servis dinamo', 100000], ['Ganti bearing', null], ['Ganti brush karbon', null]],
                -8,
                [['Bendix Starter 24V (Truk)', 1], ['Magnetic Switch / Solenoid 24V', 1], ['Bosh / Bushing Starter', 2]],
            ],
            [
                'Bu Sari Wulandari',
                '085678901203',
                'Aki sering tekor, lampu indikator cas menyala',
                'completed',
                [['Servis dinamo', null], ['Ganti brush karbon', null]],
                -6,
                [['Regulator / IC Alternator 12V', 1], ['Kohlen / Carbon Brush Alternator (set)', 1]],
            ],
            [
                'Toko Sinar Terang',
                '082112345604',
                'Dinamo ampere Carry bunyi kasar dan berdecit',
                'in_progress',
                [['Ganti bearing', null]],
                -1,
                [['Pulley Alternator', 1], ['Bearing 6203', 2]],
            ],
            [
                'Pak Hendra',
                '081998765405',
                'Alternator tidak ngecas, kemungkinan dioda jebol',
                'in_progress',
                [['Servis dinamo', null], ['Perbaikan kabel kelistrikan', null]],
                0,
                [['Dioda Plat / Rectifier', 1]],
            ],
            [
                'Bu Rina Marlina',
                '087712345606',
                'Dinamo pompa air tidak mau start',
                'in_progress',
                [['Servis dinamo', null]],
                0,
                [['Kapasitor Motor 12uF', 1], ['Seal Mekanik Pompa Air', 1]],
            ],
            [
                'UD Tani Makmur',
                '081511223307',
                'Gulung ulang spul starter truk',
                'confirmed',
                [['Rewinding kumparan', null]],
                1,
                [],
            ],
            [
                'Pak Agus Wijaya',
                '081244556608',
                'Cek starter Innova, bunyi klek saja',
                'confirmed',
                [['Servis dinamo', null]],
                2,
                [],
            ],
            [
                'Pak Dedi',
                '085211223309',
                'Servis alternator Xenia, minta dikerjakan',
                'cancelled',
                [['Servis dinamo', null]],
                -3,
                [],
                'Pelanggan batal, dinamo dibawa ke tempat lain',
            ],
            [
                'Bu Lilis',
                '081377889910',
                'Rotor alternator hangus total',
                'rejected',
                [],
                -2,
                [],
                'Kerusakan parah, biaya melebihi harga unit baru',
            ],
        ];

        $workshop = WorkshopSetting::first();

        foreach ($data as $d) {
            $service = Service::create([
                'customer_name' => $d[0],
                'phone' => $d[1],
                'complaint' => $d[2],
                'status' => $d[3],
                'scheduled_at' => now()->addDays($d[5])->setTime(rand(8, 16), [0, 30][rand(0, 1)]),
                'cancel_reason' => $d[7] ?? null,
                'booking_type' => isset($d[8]) ? 'home_service' : 'workshop',
                'latitude' => $d[8]['lat'] ?? null,
                'longitude' => $d[8]['lng'] ?? null,
                'address' => $d[8]['address'] ?? null,

            ]);

            
            // Servis lewat seeder tidak melewati CreateBooking, jadi jarak diisi di sini.
            if (isset($d[8]) && $workshop) {
                $distance = app(DistanceService::class)->measure(
                    (float) $workshop->latitude,
                    (float) $workshop->longitude,
                    (float) $d[8]['lat'],
                    (float) $d[8]['lng'],
                    $workshop->distance_method,
                );

                $service->update([
                    'distance_air_km' => $distance->airKm,
                    'distance_road_km' => $distance->roadKm,
                    'distance_km' => $distance->officialKm,
                    'distance_method' => $distance->method,
                    'distance_estimated' => $distance->estimated,
                ]);
            }

            foreach ($d[4] as [$name, $price]) {
                $type = $types[$name];

                $service->items()->create([
                    'service_type_id' => $type->id,
                    'name' => $type->name,
                    'price' => $price ?? $type->default_price,
                ]);
            }

            foreach ($d[6] as [$partName, $qty]) {
                $part = $parts->get($partName)
                    ?? throw new \RuntimeException("Spare part tidak ada di seeder: {$partName}");

                $service->serviceParts()->create([
                    'spare_part_id' => $part->id,
                    'quantity' => $qty,
                    'unit_price' => $part->price,
                ]);
            }

            $service->syncStock();
        }
    }
}