<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Jalan di semua environment, termasuk server (aturan 37): akun pemilik, 2 pelanggan demo,
        // pengaturan bengkel, kategori, dan jasa.
        $this->call([
            OwnerSeeder::class,
            DemoCustomerSeeder::class,
            WorkshopSettingSeeder::class,
            CategorySeeder::class,
            ServiceTypeSeeder::class,
        ]);

        // Data contoh, bukan di production: suku cadang (di server diisi sendiri oleh pemilik)
        // dan servis/booking palsu (supaya laporan di server berisi data asli).
        if (! app()->environment('production')) {
            $this->call([
                SparePartSeeder::class,
                ServiceSeeder::class,
            ]);
        }
    }
}