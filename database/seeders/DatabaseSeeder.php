<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Data wajib: jalan di semua environment.
        $this->call([
            OwnerSeeder::class,
            WorkshopSettingSeeder::class,
            CategorySeeder::class,
            ServiceTypeSeeder::class,
        ]);

        // Data demo: semua environment kecuali production, untuk uji manual, demo bimbingan, dan tes seeder.
        if (! app()->environment('production')) {
            $this->call([
                DemoCustomerSeeder::class,
                SparePartSeeder::class,
                ServiceSeeder::class,
            ]);
        }
    }
}