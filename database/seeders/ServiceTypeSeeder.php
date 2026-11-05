<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Format: nama => [harga acuan, deskripsi]. Foto tidak diisi seeder, upload lewat panel.
        $types = [
            'Servis dinamo' => [
                50000,
                'Pemeriksaan dan perawatan dinamo secara menyeluruh: pembersihan, pengecekan komponen, dan pengujian setelah dirakit kembali.',
            ],
            'Perbaikan kabel kelistrikan' => [
                200000,
                'Perbaikan kabel dan sambungan kelistrikan dinamo yang putus, longgar, atau korslet.',
            ],
            'Ganti brush karbon' => [
                75000,
                'Penggantian brush karbon yang aus supaya putaran dinamo kembali stabil.',
            ],
            'Ganti bearing' => [
                100000,
                'Penggantian bearing yang aus atau berbunyi kasar supaya putaran dinamo halus.',
            ],
            'Rewinding kumparan' => [
                350000,
                'Penggulungan ulang kumparan dinamo yang rusak atau terbakar.',
            ],
            'Bubut komutator' => [
                120000,
                'Perataan permukaan komutator yang aus atau bergelombang supaya kontak dengan brush kembali rapat.',
            ],
            'Ganti bendix' => [
                90000,
                'Penggantian bendix starter yang slip atau tidak mengait, supaya mesin bisa distarter normal.',
            ],
            'Ganti magnetic switch' => [
                110000,
                'Penggantian magnetic switch (solenoid) starter yang macet atau hanya berbunyi klik.',
            ],
        ];

        foreach ($types as $name => [$price, $description]) {
            // Foto sengaja tidak disentuh, supaya foto yang sudah diunggah tidak hilang saat seeder jalan lagi.
            ServiceType::updateOrCreate(
                ['name' => $name],
                ['default_price' => $price, 'description' => $description],
            );
        }
    }
}