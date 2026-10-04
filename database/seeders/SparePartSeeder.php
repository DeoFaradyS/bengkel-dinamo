<?php

namespace Database\Seeders;

use App\Models\SparePart;
use Illuminate\Database\Seeder;

class SparePartSeeder extends Seeder
{
    public function run(): void
    {
        $parts = [
            // Starter (dinamo starter)
            ['Bendix Starter 12V', 'Starter', 8, 3, 'Rak A1', 85000],
            ['Bendix Starter 24V (Truk)', 'Starter', 4, 2, 'Rak A1', 145000],
            ['Magnetic Switch / Solenoid 12V', 'Starter', 2, 3, 'Rak A2', 120000],
            ['Magnetic Switch / Solenoid 24V', 'Starter', 3, 2, 'Rak A2', 175000],
            ['Arminture / Anker Starter 12V', 'Starter', 3, 2, 'Rak A3', 350000],
            ['Arminture / Anker Starter 24V', 'Starter', 1, 2, 'Rak A3', 520000],
            ['Spul / Field Coil Starter', 'Starter', 5, 2, 'Rak A4', 180000],
            ['Kohlen / Carbon Brush Starter (set)', 'Starter', 15, 5, 'Laci B1', 25000],
            ['Brush Holder Starter', 'Starter', 6, 3, 'Laci B1', 45000],
            ['Gear Reduksi Starter', 'Starter', 2, 2, 'Rak A5', 210000],

            // Alternator (dinamo ampere / cas)
            ['Regulator / IC Alternator 12V', 'Alternator', 10, 4, 'Rak C1', 75000],
            ['Regulator / IC Alternator 24V', 'Alternator', 1, 3, 'Rak C1', 110000],
            ['Dioda Plat / Rectifier', 'Alternator', 7, 3, 'Rak C2', 120000],
            ['Kohlen / Carbon Brush Alternator (set)', 'Alternator', 12, 5, 'Laci B2', 20000],
            ['Rotor Alternator', 'Alternator', 2, 1, 'Rak C3', 390000],
            ['Stator / Spul Alternator', 'Alternator', 3, 2, 'Rak C3', 310000],
            ['Pulley Alternator', 'Alternator', 4, 2, 'Rak C4', 65000],

            // Motor listrik / pompa air (dinamo rumah tangga)
            ['Kapasitor Motor 8uF', 'Motor Listrik', 12, 5, 'Laci D1', 22000],
            ['Kapasitor Motor 12uF', 'Motor Listrik', 9, 5, 'Laci D1', 28000],
            ['Seal Mekanik Pompa Air', 'Motor Listrik', 3, 5, 'Laci D2', 35000],
            ['Kabel Email / Tembaga Gulung (kg)', 'Motor Listrik', 6, 2, 'Rak E1', 130000],

            // Umum
            ['Bearing 6203', 'Umum', 20, 8, 'Laci F1', 25000],
            ['Bearing 6204', 'Umum', 14, 8, 'Laci F1', 30000],
            ['Bosh / Bushing Starter', 'Umum', 18, 6, 'Laci F2', 12000],
            ['Terminal Kabel & Baut Set', 'Umum', 30, 10, 'Laci F3', 8000],
        ];

        foreach ($parts as [$name, $category, $stock, $minStock, $location, $price]) {
            SparePart::updateOrCreate(
                ['name' => $name],
                [
                    'category' => $category,
                    'stock' => $stock,
                    'min_stock' => $minStock,
                    'location' => $location,
                    'price' => $price,
                ]
            );
        }
    }
}