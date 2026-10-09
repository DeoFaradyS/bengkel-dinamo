<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SparePart;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SparePartSeeder extends Seeder
{
    public function run(): void
    {
        // [nama => id], dibuat CategorySeeder
        $categoryIds = Category::pluck('id', 'name');

        $parts = [
            // Starter
            ['SP-001', 'Bendix Starter 12V', 'Starter', 8, 3, 'Rak A1', 85000],
            ['SP-002', 'Bendix Starter 24V (Truk)', 'Starter', 4, 2, 'Rak A1', 145000],
            ['SP-003', 'Magnetic Switch / Solenoid 12V', 'Starter', 2, 3, 'Rak A2', 120000],
            ['SP-004', 'Magnetic Switch / Solenoid 24V', 'Starter', 3, 2, 'Rak A2', 175000],
            ['SP-005', 'Arminture / Anker Starter 12V', 'Kumparan', 3, 2, 'Rak A3', 350000],
            ['SP-006', 'Arminture / Anker Starter 24V', 'Kumparan', 1, 2, 'Rak A3', 520000],
            ['SP-007', 'Spul / Field Coil Starter', 'Kumparan', 5, 2, 'Rak A4', 180000],
            ['SP-008', 'Kohlen / Carbon Brush Starter (set)', 'Sikat Arang', 15, 5, 'Laci B1', 25000],
            ['SP-009', 'Brush Holder Starter', 'Sikat Arang', 6, 3, 'Laci B1', 45000],
            ['SP-010', 'Gear Reduksi Starter', 'Starter', 2, 2, 'Rak A5', 210000],

            // Alternator
            ['SP-011', 'Regulator / IC Alternator 12V', 'Alternator', 10, 4, 'Rak C1', 75000],
            ['SP-012', 'Regulator / IC Alternator 24V', 'Alternator', 1, 3, 'Rak C1', 110000],
            ['SP-013', 'Dioda Plat / Rectifier', 'Alternator', 7, 3, 'Rak C2', 120000],
            ['SP-014', 'Kohlen / Carbon Brush Alternator (set)', 'Sikat Arang', 12, 5, 'Laci B2', 20000],
            ['SP-015', 'Rotor Alternator', 'Kumparan', 2, 1, 'Rak C3', 390000],
            ['SP-016', 'Stator / Spul Alternator', 'Kumparan', 3, 2, 'Rak C3', 310000],
            ['SP-017', 'Pulley Alternator', 'Alternator', 4, 2, 'Rak C4', 65000],

            // Motor listrik / pompa air
            ['SP-018', 'Kapasitor Motor 8uF', 'Lainnya', 12, 5, 'Laci D1', 22000],
            ['SP-019', 'Kapasitor Motor 12uF', 'Lainnya', 9, 5, 'Laci D1', 28000],
            ['SP-020', 'Seal Mekanik Pompa Air', 'Lainnya', 3, 5, 'Laci D2', 35000],
            ['SP-021', 'Kabel Email / Tembaga Gulung (kg)', 'Kumparan', 6, 2, 'Rak E1', 130000],

            // Umum
            ['SP-022', 'Bearing 6203', 'Bearing', 20, 8, 'Laci F1', 25000],
            ['SP-023', 'Bearing 6204', 'Bearing', 14, 8, 'Laci F1', 30000],
            ['SP-024', 'Bosh / Bushing Starter', 'Bearing', 18, 6, 'Laci F2', 12000],
            ['SP-025', 'Terminal Kabel & Baut Set', 'Lainnya', 30, 10, 'Laci F3', 8000],
        ];

        $imageDir = database_path('seeders/images/spare-parts');

        foreach ($parts as [$code, $name, $category, $stock, $minStock, $location, $price]) {
            $data = [
                'name' => $name,
                'category_id' => $categoryIds[$category],
                'stock' => $stock,
                'min_stock' => $minStock,
                'location' => $location,
                'price' => $price,
            ];

            // cari file gambar bernama kode part, format apa saja
            $source = File::glob("{$imageDir}/{$code}.*")[0] ?? null;

            if ($source) {
                $photo = 'spare-parts/' . basename($source);
                Storage::disk('public')->put($photo, File::get($source));
                $data['photo'] = $photo;
            }

            SparePart::updateOrCreate(['code' => $code], $data);
        }
    }
}