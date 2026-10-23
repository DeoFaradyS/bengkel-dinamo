<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceType;
use App\Models\SparePart;
use App\Models\WorkshopSetting;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $types = ServiceType::all()->keyBy('name');
        $parts = SparePart::all()->keyBy('name');
        $method = WorkshopSetting::first()?->distance_method;

        // jasa: [[nama katalog, harga atau null]], harga null = pakai harga katalog
        // parts: [[nama, qty]]
        // home: ['km' => jarak sekali jalan, 'lat', 'lng', 'address']
        $data = [
            // ---- Home Service: variasi jarak dan status ----
            [ // jarak sangat dekat: ongkos minimum Rp500
                'name' => 'Pak Wawan Setiawan',
                'phone' => '081311223340',
                'status' => 'pending',
                'days' => 1,
                'complaint' => 'Starter pickup mati di garasi rumah, minta montir datang',
                'jasa' => [],
                'parts' => [],
                'home' => ['km' => 0.8, 'lat' => -7.7188, 'lng' => 113.0790],
            ],
            [ // kedaluwarsa: home service, admin tidak menjawab sampai jadwal lewat
                'name' => 'Pak Anwar',
                'phone' => '081311223350',
                'status' => 'expired',
                'days' => -1,
                'complaint' => 'Alternator pickup tidak ngecas, minta dicek di rumah',
                'jasa' => [],
                'parts' => [],
                'home' => ['km' => 1.5, 'lat' => -7.7250, 'lng' => 113.0850],
            ],
            [ // jarak sedang, belum dikerjakan
                'name' => 'Bu Ani Rahayu',
                'phone' => '081311223341',
                'status' => 'confirmed',
                'days' => 2,
                'complaint' => 'Dinamo pompa air rumah tidak mau menyala',
                'jasa' => [['Servis dinamo', null]],
                'parts' => [],
                'home' => ['km' => 5.0, 'lat' => -7.7201, 'lng' => 113.0712, 'address' => 'Gang Melati 3, depan Indomaret, pagar hijau'],
            ],
            [ // selesai + ongkos + spare part
                'name' => 'Pak Rudi Hartono',
                'phone' => '081311223342',
                'status' => 'completed',
                'days' => -4,
                'complaint' => 'Alternator mobil di garasi tidak ngecas',
                'jasa' => [['Servis dinamo', null]],
                'parts' => [['Kohlen / Carbon Brush Alternator (set)', 1]],
                'home' => ['km' => 3.0, 'lat' => -7.7165, 'lng' => 113.0830, 'address' => 'Jl. Kenanga 12, sebelah bengkel las, gerbang biru'],
            ],
            [ // jauh, banyak jasa dan part: total paling besar
                'name' => 'Pak Haji Mansur',
                'phone' => '081311223343',
                'status' => 'completed',
                'days' => -7,
                'complaint' => 'Dinamo genset pabrik tahu bunyi kasar, panas berlebih',
                'jasa' => [['Servis dinamo', null], ['Ganti bearing', null], ['Ganti brush karbon', null]],
                'parts' => [['Bearing 6203', 2], ['Kohlen / Carbon Brush Starter (set)', 1]],
                'home' => ['km' => 12.0, 'lat' => -7.6890, 'lng' => 113.1250, 'address' => 'Pabrik tahu Barokah, Jl. Raya Kraksaan km 4'],
            ],
            [ // sedang dikerjakan, Home Service
                'name' => 'Bu Yuli Astuti',
                'phone' => '081311223344',
                'status' => 'in_progress',
                'days' => 0,
                'complaint' => 'Dinamo mesin cuci steam mati total',
                'jasa' => [['Rewinding kumparan', null]],
                'parts' => [['Kapasitor Motor 12uF', 1]],
                'home' => ['km' => 7.5, 'lat' => -7.7320, 'lng' => 113.0560],
            ],
            [ // batal, Home Service
                'name' => 'Pak Eko Prasetyo',
                'phone' => '081311223345',
                'status' => 'cancelled',
                'days' => -2,
                'complaint' => 'Alternator Carry tidak ngecas, minta dicek di rumah',
                'jasa' => [['Servis dinamo', null]],
                'parts' => [],
                'reason' => 'Pelanggan sudah menemukan masalahnya di aki',
                'home' => ['km' => 5.0, 'lat' => -7.7101, 'lng' => 113.0888],
            ],
            [ // ditolak, Home Service
                'name' => 'Bu Lilis',
                'phone' => '081377889910',
                'status' => 'rejected',
                'days' => -2,
                'complaint' => 'Rotor alternator hangus total',
                'jasa' => [],
                'parts' => [],
                'reason' => 'Kerusakan parah, biaya melebihi harga unit baru',
                'home' => ['km' => 3.0, 'lat' => -7.7150, 'lng' => 113.0700],
            ],

            // ---- Ke bengkel: variasi jasa dan spare part ----
            [ // jasa + part, lengkap
                'name' => 'Pak Budi Santoso',
                'phone' => '081234567801',
                'status' => 'completed',
                'days' => -10,
                'complaint' => 'Dinamo starter Avanza ngik-ngik, tidak mau engkol',
                'jasa' => [['Servis dinamo', null], ['Ganti brush karbon', null]],
                'parts' => [['Bendix Starter 12V', 1], ['Kohlen / Carbon Brush Starter (set)', 1]],
            ],
            [ // harga jasa diubah admin + tiga part
                'name' => 'CV Maju Jaya',
                'phone' => '081345678902',
                'status' => 'completed',
                'days' => -8,
                'complaint' => 'Starter truk Hino 24V lemah, harus didorong',
                'jasa' => [['Servis dinamo', 100000], ['Ganti bearing', null], ['Ganti brush karbon', null]],
                'parts' => [['Bendix Starter 24V (Truk)', 1], ['Magnetic Switch / Solenoid 24V', 1], ['Bosh / Bushing Starter', 2]],
            ],
            [ // selesai, jasa saja tanpa spare part
                'name' => 'Pak Tono',
                'phone' => '081311223346',
                'status' => 'completed',
                'days' => -5,
                'complaint' => 'Cek dinamo, bunyi halus saat idle',
                'jasa' => [['Servis dinamo', null]],
                'parts' => [],
            ],
            [ // kedaluwarsa: admin tidak menjawab sampai jadwal lewat
                'name' => 'Mas Rizky',
                'phone' => '081234567811',
                'status' => 'expired',
                'days' => -2,
                'complaint' => 'Starter Brio susah hidup pagi hari',
                'jasa' => [],
                'parts' => [],
            ],
            [ // kedaluwarsa: booking jauh hari, tidak dikonfirmasi 24 jam
                'name' => 'Bu Wati',
                'phone' => '081234567812',
                'status' => 'expired',
                'days' => -1,
                'complaint' => 'Dinamo kipas radiator bunyi kasar',
                'jasa' => [],
                'parts' => [],
            ],
            [
                'name' => 'Bu Sari Wulandari',
                'phone' => '085678901203',
                'status' => 'completed',
                'days' => -6,
                'complaint' => 'Aki sering tekor, lampu indikator cas menyala',
                'jasa' => [['Servis dinamo', null], ['Ganti brush karbon', null]],
                'parts' => [['Regulator / IC Alternator 12V', 1], ['Kohlen / Carbon Brush Alternator (set)', 1]],
            ],
            [
                'name' => 'Toko Sinar Terang',
                'phone' => '082112345604',
                'status' => 'in_progress',
                'days' => -1,
                'complaint' => 'Dinamo ampere Carry bunyi kasar dan berdecit',
                'jasa' => [['Ganti bearing', null]],
                'parts' => [['Pulley Alternator', 1], ['Bearing 6203', 2]],
            ],
            [
                'name' => 'Pak Hendra',
                'phone' => '081998765405',
                'status' => 'in_progress',
                'days' => 0,
                'complaint' => 'Alternator tidak ngecas, kemungkinan dioda jebol',
                'jasa' => [['Servis dinamo', null], ['Perbaikan kabel kelistrikan', null]],
                'parts' => [['Dioda Plat / Rectifier', 1]],
            ],
            [
                'name' => 'Bu Rina Marlina',
                'phone' => '087712345606',
                'status' => 'in_progress',
                'days' => 0,
                'complaint' => 'Dinamo pompa air tidak mau start',
                'jasa' => [['Servis dinamo', null]],
                'parts' => [['Kapasitor Motor 12uF', 1], ['Seal Mekanik Pompa Air', 1]],
            ],
            [ // dikonfirmasi, belum ada jasa: baris kosong boleh
                'name' => 'UD Tani Makmur',
                'phone' => '081511223307',
                'status' => 'confirmed',
                'days' => 1,
                'complaint' => 'Gulung ulang spul starter truk',
                'jasa' => [['Rewinding kumparan', null]],
                'parts' => [],
            ],
            [ // dikonfirmasi tanpa jasa sama sekali
                'name' => 'Pak Agus Wijaya',
                'phone' => '081244556608',
                'status' => 'confirmed',
                'days' => 2,
                'complaint' => 'Cek starter Innova, bunyi klek saja',
                'jasa' => [],
                'parts' => [],
            ],
            [ // beli part saja, tanpa jasa
                'name' => 'Pak Slamet',
                'phone' => '081311223347',
                'status' => 'confirmed',
                'days' => 1,
                'complaint' => 'Beli brush karbon, dipasang sendiri',
                'jasa' => [],
                'parts' => [['Kohlen / Carbon Brush Starter (set)', 2]],
            ],
            [ // baru masuk, kosong semua
                'name' => 'Mas Dimas',
                'phone' => '081311223348',
                'status' => 'pending',
                'days' => 3,
                'complaint' => 'Dinamo sepeda motor listrik tidak berputar',
                'jasa' => [],
                'parts' => [],
            ],
            [ // batal, bengkel
                'name' => 'Pak Dedi',
                'phone' => '085211223309',
                'status' => 'cancelled',
                'days' => -3,
                'complaint' => 'Servis alternator Xenia, minta dikerjakan',
                'jasa' => [['Servis dinamo', null]],
                'parts' => [],
                'reason' => 'Pelanggan batal, dinamo dibawa ke tempat lain',
            ],
            [ // ditolak, bengkel
                'name' => 'Pak Joko',
                'phone' => '081311223349',
                'status' => 'rejected',
                'days' => -1,
                'complaint' => 'Dinamo tanpa nameplate, tidak ada suku cadangnya',
                'jasa' => [],
                'parts' => [],
                'reason' => 'Suku cadang tidak tersedia',
            ],
        ];

        foreach ($data as $d) {
            $home = $d['home'] ?? null;

            $service = Service::create([
                'customer_name' => $d['name'],
                'phone' => $d['phone'],
                'complaint' => $d['complaint'],
                'status' => $d['status'],
                'scheduled_at' => now()->addDays($d['days'])->setTime(rand(8, 16), [0, 30][rand(0, 1)]),
                'cancel_reason' => $d['reason'] ?? null,
                'booking_type' => $home ? 'home_service' : 'workshop',
                'latitude' => $home['lat'] ?? null,
                'longitude' => $home['lng'] ?? null,
                'address' => $home['address'] ?? null,

                // Jarak diisi langsung. Model menghitung ongkos dan menyalin harga BBM saat simpan.
                'distance_air_km' => $home ? round($home['km'] * 0.85, 3) : null,
                'distance_road_km' => $home['km'] ?? null,
                'distance_km' => $home['km'] ?? null,
                'distance_method' => $home ? $method : null,
                'distance_estimated' => false,
            ]);

            foreach ($d['jasa'] as [$name, $price]) {
                $type = $types[$name];

                $service->items()->create([
                    'service_type_id' => $type->id,
                    'name' => $type->name,
                    'price' => $price ?? $type->default_price,
                ]);
            }

            foreach ($d['parts'] as [$partName, $qty]) {
                $part = $parts->get($partName)
                    ?? throw new \RuntimeException("Spare part tidak ada di seeder: {$partName}");

                $service->serviceParts()->create([
                    'spare_part_id' => $part->id,
                    'quantity' => $qty,
                    'unit_price' => $part->price,
                ]);
            }

            if ($d['status'] === 'completed') {
                $service->syncStock();
            }
        }
    }
}