<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Format: nama => [harga acuan, deskripsi].
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
            'Ganti bendix' => [
                90000,
                'Penggantian bendix starter yang slip atau tidak mengait, supaya mesin bisa distarter normal.',
            ],
            'Ganti magnetic switch' => [
                110000,
                'Penggantian magnetic switch (solenoid) starter yang macet atau hanya berbunyi klik.',
            ],
        ];

        $imageDir = database_path('seeders/images/service-types');

        foreach ($types as $name => [$price, $description]) {
            $type = ServiceType::updateOrCreate(
                ['name' => $name],
                ['default_price' => $price, 'description' => $description],
            );

            $this->attachBundledPhoto($type, $imageDir);
        }
    }

    // Foto bawaan: database/seeders/images/service-types/{slug nama}.*
    // Dipasang hanya kalau jasa belum punya foto (atau berkasnya hilang dari disk),
    // jadi foto yang diunggah lewat panel tidak ditimpa.
    private function attachBundledPhoto(ServiceType $type, string $imageDir): void
    {
        if (filled($type->photo) && Storage::disk('public')->exists($type->photo)) {
            return;
        }

        $source = File::glob($imageDir . '/' . Str::slug($type->name) . '.*')[0] ?? null;

        if ($source === null) {
            return;
        }

        $photo = 'service-types/' . basename($source);
        Storage::disk('public')->put($photo, File::get($source));

        $type->photo = $photo;
        $type->save();
    }
}