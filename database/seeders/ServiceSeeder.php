<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceType;
use App\Models\SparePart;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRole;
use App\Models\User;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        Auth::setUser(User::where('role', UserRole::Owner)->firstOrFail());
        $customer = User::where('email', 'customer@bengkel-dinamo.test')->firstOrFail();
        $types = ServiceType::all()->keyBy('name');
        $parts = SparePart::all()->keyBy('name');


        // jasa: [[nama katalog, harga atau null]], harga null = pakai harga katalog
        // parts: [[nama, qty]]
        // home: ['km' => jarak sekali jalan, 'lat', 'lng', 'address']
        $data = [
            // ---- Home Service: variasi jarak dan status ----
            [ // jarak sangat dekat: ongkos minimum Rp500
                'name' => 'Pak Wawan Setiawan',
                'customer_phone' => '081311223340',
                'status' => 'pending',
                'days' => 1,
                'complaint' => 'Starter pickup mati di garasi rumah, minta montir datang',
                'jasa' => [],
                'parts' => [],
                'home' => ['km' => 0.8, 'lat' => -7.7188, 'lng' => 113.0790],
            ],
            [ // kedaluwarsa: home service, admin tidak menjawab sampai jadwal lewat
                'name' => 'Pak Anwar',
                'customer_phone' => '081311223350',
                'status' => 'expired',
                'days' => -1,
                'complaint' => 'Alternator pickup tidak ngecas, minta dicek di rumah',
                'jasa' => [],
                'parts' => [],
                'home' => ['km' => 1.5, 'lat' => -7.7250, 'lng' => 113.0850],
            ],
            [ // jarak sedang, belum dikerjakan
                'name' => 'Bu Ani Rahayu',
                'customer_phone' => '081311223341',
                'status' => 'confirmed',
                'days' => 2,
                'complaint' => 'Dinamo pompa air rumah tidak mau menyala',
                'jasa' => [['Servis dinamo', null]],
                'parts' => [],
                'home' => ['km' => 5.0, 'lat' => -7.7201, 'lng' => 113.0712, 'address' => 'Gang Melati 3, depan Indomaret, pagar hijau'],
            ],
            [ // selesai + ongkos + spare part
                'name' => 'Pak Rudi Hartono',
                'customer_phone' => '081311223342',
                'status' => 'completed',
                'days' => -4,
                'complaint' => 'Alternator mobil di garasi tidak ngecas',
                'jasa' => [['Servis dinamo', null]],
                'parts' => [['Kohlen / Carbon Brush Alternator', 1]],
                'home' => ['km' => 3.0, 'lat' => -7.7165, 'lng' => 113.0830, 'address' => 'Jl. Kenanga 12, sebelah bengkel las, gerbang biru'],
                'paid' => true,
                'mechanic' => 'Cak Budi',
            ],
            [ // jauh, banyak jasa dan part: total paling besar
                'name' => 'Pak Haji Mansur',
                'customer_phone' => '081311223343',
                'status' => 'completed',
                'days' => -7,
                'complaint' => 'Dinamo genset pabrik tahu bunyi kasar, panas berlebih',
                'jasa' => [['Servis dinamo', null], ['Ganti bearing', null], ['Ganti brush karbon', null]],
                'parts' => [['Bearing 6203', 2], ['Kohlen / Carbon Brush Starter', 1]],
                'home' => ['km' => 12.0, 'lat' => -7.6890, 'lng' => 113.1250, 'address' => 'Pabrik tahu Barokah, Jl. Raya Kraksaan km 4'],
                'mechanic' => 'Cak Budi, Mas Andi',
            ],
            [ // sedang dikerjakan, Home Service
                'name' => 'Bu Yuli Astuti',
                'mechanic' => 'Mas Andi',
                'customer_phone' => '081311223344',
                'status' => 'in_progress',
                'days' => 0,
                'complaint' => 'Dinamo mesin cuci steam mati total',
                'jasa' => [['Rewinding kumparan', null]],
                'parts' => [['Kapasitor Motor 12uF', 1]],
                'home' => ['km' => 7.5, 'lat' => -7.7320, 'lng' => 113.0560],
            ],
            [ // batal, Home Service
                'name' => 'Pak Eko Prasetyo',
                'customer_phone' => '081311223345',
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
                'customer_phone' => '081377889910',
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
                'customer_phone' => '081234567801',
                'status' => 'completed',
                'days' => -10,
                'complaint' => 'Dinamo starter Avanza ngik-ngik, tidak mau engkol',
                'jasa' => [['Servis dinamo', null], ['Ganti brush karbon', null]],
                'parts' => [['Bendix Starter 12V', 1], ['Kohlen / Carbon Brush Starter', 1]],
                'paid' => true,
            ],
            [ // harga jasa diubah admin + tiga part
                'name' => 'CV Maju Jaya',
                'customer_phone' => '081345678902',
                'status' => 'completed',
                'days' => -8,
                'complaint' => 'Starter truk Hino 24V lemah, harus didorong',
                'jasa' => [['Servis dinamo', 100000], ['Ganti bearing', null], ['Ganti brush karbon', null]],
                'parts' => [['Bendix Starter 24V (Truk)', 1], ['Magnetic Switch / Solenoid 24V', 1], ['Bosh / Bushing Starter', 2]],
            ],
            [ // selesai, jasa saja tanpa spare part
                'name' => 'Pak Tono',
                'customer_phone' => '081311223346',
                'status' => 'completed',
                'days' => -5,
                'complaint' => 'Cek dinamo, bunyi halus saat idle',
                'jasa' => [['Servis dinamo', null]],
                'parts' => [],
                'paid' => true,
            ],
            [ // kedaluwarsa: admin tidak menjawab sampai jadwal lewat
                'name' => 'Mas Rizky',
                'customer_phone' => '081234567811',
                'status' => 'expired',
                'days' => -2,
                'complaint' => 'Starter Brio susah hidup pagi hari',
                'jasa' => [],
                'parts' => [],
            ],
            [ // kedaluwarsa: booking jauh hari, tidak dikonfirmasi 24 jam
                'name' => 'Bu Wati',
                'customer_phone' => '081234567812',
                'status' => 'expired',
                'days' => -1,
                'complaint' => 'Dinamo kipas radiator bunyi kasar',
                'jasa' => [],
                'parts' => [],
            ],
            [
                'name' => 'Bu Sari Wulandari',
                'customer_phone' => '085678901203',
                'status' => 'completed',
                'days' => -6,
                'complaint' => 'Aki sering tekor, lampu indikator cas menyala',
                'jasa' => [['Servis dinamo', null], ['Ganti brush karbon', null]],
                'parts' => [['Regulator / IC Alternator 12V', 1], ['Kohlen / Carbon Brush Alternator', 1]],
            ],
            [
                // Aturan 11: hanya satu servis Dikerjakan sekaligus (Bu Yuli). Yang lain Selesai/Dikonfirmasi.
                'name' => 'Toko Sinar Terang',
                'customer_phone' => '082112345604',
                'status' => 'completed',
                'mechanic' => 'Cak Budi',
                'days' => -1,
                'complaint' => 'Dinamo ampere Carry bunyi kasar dan berdecit',
                'jasa' => [['Ganti bearing', null]],
                'parts' => [['Pulley Alternator', 1], ['Bearing 6203', 2]],
            ],
            [
                // Antre hari ini, belum dikerjakan: jasa dan part belum diisi.
                'name' => 'Pak Hendra',
                'customer_phone' => '081998765405',
                'status' => 'confirmed',
                'days' => 0,
                'complaint' => 'Alternator tidak ngecas, kemungkinan dioda jebol',
                'jasa' => [],
                'parts' => [],
            ],
            [
                // Antre hari ini, belum dikerjakan: jasa dan part belum diisi.
                'name' => 'Bu Rina Marlina',
                'customer_phone' => '087712345606',
                'status' => 'confirmed',
                'days' => 0,
                'complaint' => 'Dinamo pompa air tidak mau start',
                'jasa' => [],
                'parts' => [],
            ],
            [ // dikonfirmasi, belum ada jasa: baris kosong boleh
                'name' => 'UD Tani Makmur',
                'customer_phone' => '081511223307',
                'status' => 'confirmed',
                'days' => 1,
                'complaint' => 'Gulung ulang spul starter truk',
                'jasa' => [['Rewinding kumparan', null]],
                'parts' => [],
            ],
            [ // dikonfirmasi tanpa jasa sama sekali
                'name' => 'Pak Agus Wijaya',
                'customer_phone' => '081244556608',
                'status' => 'confirmed',
                'days' => 2,
                'complaint' => 'Cek starter Innova, bunyi klek saja',
                'jasa' => [],
                'parts' => [],
            ],
            [ // beli part saja, tanpa jasa
                'name' => 'Pak Slamet',
                'customer_phone' => '081311223347',
                'status' => 'confirmed',
                'days' => 1,
                'complaint' => 'Beli brush karbon, dipasang sendiri',
                'jasa' => [],
                'parts' => [['Kohlen / Carbon Brush Starter', 2]],
            ],
            [ // baru masuk, kosong semua
                'name' => 'Mas Dimas',
                'customer_phone' => '081311223348',
                'status' => 'pending',
                'days' => 3,
                'complaint' => 'Dinamo sepeda motor listrik tidak berputar',
                'jasa' => [],
                'parts' => [],
            ],
            [ // batal, bengkel
                'name' => 'Pak Dedi',
                'customer_phone' => '085211223309',
                'status' => 'cancelled',
                'days' => -3,
                'complaint' => 'Servis alternator Xenia, minta dikerjakan',
                'jasa' => [['Servis dinamo', null]],
                'parts' => [],
                'reason' => 'Pelanggan batal, dinamo dibawa ke tempat lain',
            ],
            [ // ditolak, bengkel
                'name' => 'Pak Joko',
                'customer_phone' => '081311223349',
                'status' => 'rejected',
                'days' => -1,
                'complaint' => 'Dinamo tanpa nameplate, tidak ada suku cadangnya',
                'jasa' => [],
                'parts' => [],
                'reason' => 'Suku cadang tidak tersedia',
            ],

            [ // milik customer demo: selesai dan lunas
                'name' => 'Pelanggan Demo',
                'customer_phone' => '081234567890',
                'user' => true,
                'status' => 'completed',
                'paid' => true,
                'mechanic' => 'Cak Budi',
                'days' => -12,
                'complaint' => 'Servis dinamo starter rutin',
                'jasa' => [['Servis dinamo', null]],
                'parts' => [],
            ],
            [ // milik customer demo: selesai, belum lunas
                'name' => 'Pelanggan Demo',
                'customer_phone' => '081234567890',
                'user' => true,
                'status' => 'completed',
                'mechanic' => 'Mas Andi',
                'days' => -3,
                'complaint' => 'Starter ngik-ngik, bendix slip',
                'jasa' => [['Ganti bendix', null]],
                'parts' => [],
            ],
            [ // milik customer demo: sudah dikonfirmasi
                'name' => 'Pelanggan Demo',
                'customer_phone' => '081234567890',
                'user' => true,
                'status' => 'confirmed',
                'days' => 2,
                'complaint' => 'Cek dinamo pompa air',
                'jasa' => [],
                'parts' => [],
            ],
        ];

        foreach ($data as $d) {
            $home = $d['home'] ?? null;

            $service = Service::create([
                'customer_name' => $d['name'],
                'customer_phone' => $d['customer_phone'],
                'complaint' => $d['complaint'],
                'status' => $d['status'],
                'scheduled_at' => now()->addDays($d['days'])->setTime(rand(8, 16), [0, 30][rand(0, 1)]),
                'cancel_reason' => $d['reason'] ?? null,
                'booking_type' => $home ? 'home_service' : 'workshop',
                'latitude' => $home['lat'] ?? null,
                'longitude' => $home['lng'] ?? null,
                'address_note' => $home['address'] ?? null,
                'user_id' => ($d['user'] ?? false) ? $customer->id : null,
                'mechanic_names' => $d['mechanic'] ?? null,

                // Jarak diisi langsung. Model menghitung ongkos dan menyalin harga BBM saat simpan.
                'distance_km' => $home['km'] ?? null,
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

            if ($d['status'] === 'completed' && ($d['paid'] ?? false)) {
                // paid_at tidak di $fillable, jadi pakai forceFill.
                $service->forceFill(['paid_at' => $service->scheduled_at->copy()->addHours(3)])->save();
            }
        }
    }
}