<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $types = ServiceType::all()->keyBy('name');

        // [nama, hp, keluhan, status, jasa[[nama katalog, harga atau null]], hari, spare part (belum dipakai), alasan batal]
        // harga null = pakai harga katalog
        $data = [
            [
                'Pak Budi Santoso', '081234567801',
                'Dinamo starter Avanza ngik-ngik, tidak mau engkol',
                'completed',
                [['Servis dinamo', null], ['Ganti brush karbon', null]],
                -10,
                [['Bendix Starter 12V', 1], ['Kohlen / Carbon Brush Starter (set)', 1]],
            ],
            [
                'CV Maju Jaya', '081345678902',
                'Starter truk Hino 24V lemah, harus didorong',
                'completed',
                [['Servis dinamo', 100000], ['Ganti bearing', null], ['Ganti brush karbon', null]],
                -8,
                [['Bendix Starter 24V (Truk)', 1], ['Magnetic Switch / Solenoid 24V', 1], ['Bosh / Bushing Starter', 2]],
            ],
            [
                'Bu Sari Wulandari', '085678901203',
                'Aki sering tekor, lampu indikator cas menyala',
                'completed',
                [['Servis dinamo', null], ['Ganti brush karbon', null]],
                -6,
                [['Regulator / IC Alternator 12V', 1], ['Kohlen / Carbon Brush Alternator (set)', 1]],
            ],
            [
                'Toko Sinar Terang', '082112345604',
                'Dinamo ampere Carry bunyi kasar dan berdecit',
                'in_progress',
                [['Ganti bearing', null]],
                -1,
                [['Pulley Alternator', 1], ['Bearing 6203', 2]],
            ],
            [
                'Pak Hendra', '081998765405',
                'Alternator tidak ngecas, kemungkinan dioda jebol',
                'in_progress',
                [['Servis dinamo', null], ['Perbaikan kabel kelistrikan', null]],
                0,
                [['Dioda Plat / Rectifier', 1]],
            ],
            [
                'Bu Rina Marlina', '087712345606',
                'Dinamo pompa air tidak mau start',
                'in_progress',
                [['Servis dinamo', null]],
                0,
                [['Kapasitor Motor 12uF', 1], ['Seal Mekanik Pompa Air', 1]],
            ],
            [
                'UD Tani Makmur', '081511223307',
                'Gulung ulang spul starter truk',
                'confirmed',
                [['Rewinding kumparan', null]],
                1,
                [],
            ],
            [
                'Pak Agus Wijaya', '081244556608',
                'Cek starter Innova, bunyi klek saja',
                'confirmed',
                [['Servis dinamo', null]],
                2,
                [],
            ],
            [
                'Pak Dedi', '085211223309',
                'Servis alternator Xenia, minta dikerjakan',
                'cancelled',
                [['Servis dinamo', null]],
                -3,
                [],
                'Pelanggan batal, dinamo dibawa ke tempat lain',
            ],
            [
                'Bu Lilis', '081377889910',
                'Rotor alternator hangus total',
                'rejected',
                [],
                -2,
                [],
                'Kerusakan parah, biaya melebihi harga unit baru',
            ],
        ];

        foreach ($data as $d) {
            $service = Service::create([
                'customer_name' => $d[0],
                'phone' => $d[1],
                'complaint' => $d[2],
                'status' => $d[3],
                'scheduled_at' => now()->addDays($d[5])->setTime(rand(8, 16), [0, 30][rand(0, 1)]),
                'cancel_reason' => $d[7] ?? null,
            ]);

            foreach ($d[4] as [$name, $price]) {
                $type = $types[$name];

                $service->items()->create([
                    'service_type_id' => $type->id,
                    'name' => $type->name,
                    'price' => $price ?? $type->default_price,
                ]);
            }
        }
    }
}