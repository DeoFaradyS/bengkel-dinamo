<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Data wajib: jalan di semua environment.
        $this->call([
            StatusSeeder::class,
            OwnerSeeder::class,
            WorkshopSettingSeeder::class,
            CategorySeeder::class,
            ServiceTypeSeeder::class,
        ]);

        // Data demo: hanya local, untuk uji manual dan demo bimbingan.
        if (app()->environment('local')) {
            $this->call([
                DemoCustomerSeeder::class,
                SparePartSeeder::class,
                ServiceSeeder::class,
            ]);
        }
    }
}