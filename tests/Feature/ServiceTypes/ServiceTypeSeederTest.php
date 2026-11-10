<?php

use App\Models\ServiceType;
use Database\Seeders\ServiceTypeSeeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Seeder jasa. Kode: JSA-007..009 (utang PLANNING.md bagian A) dan JSA-013.
// Letakkan di tests/Feature/ServiceTypes/ServiceTypeSeederTest.php.

test('JSA-007: seeder mengisi 7 jasa, semuanya punya deskripsi', function () {
    Storage::fake('public');
    $this->seed(ServiceTypeSeeder::class);

    expect(ServiceType::count())->toBe(7)
        ->and(ServiceType::whereNull('description')->orWhere('description', '')->count())->toBe(0);
});

test('JSA-008: seeder dijalankan dua kali tidak menggandakan jasa', function () {
    Storage::fake('public');
    $this->seed(ServiceTypeSeeder::class);
    $this->seed(ServiceTypeSeeder::class);

    expect(ServiceType::count())->toBe(7);
});

test('JSA-009: foto yang sudah diunggah tidak tertimpa saat seeder jalan lagi', function () {
    Storage::fake('public');
    $this->seed(ServiceTypeSeeder::class);

    Storage::disk('public')->put('service-types/unggahan-sendiri.jpg', 'isi');
    $type = ServiceType::where('name', 'Servis dinamo')->firstOrFail();
    $type->photo = 'service-types/unggahan-sendiri.jpg';
    $type->save();

    $this->seed(ServiceTypeSeeder::class);

    expect($type->fresh()->photo)->toBe('service-types/unggahan-sendiri.jpg');
});

test('JSA-013: setiap foto bawaan di folder seeder terpasang ke jasanya dan berkasnya ada di disk', function () {
    Storage::fake('public');
    $this->seed(ServiceTypeSeeder::class);

    foreach (File::glob(database_path('seeders/images/service-types/*.*')) as $file) {
        $slug = pathinfo($file, PATHINFO_FILENAME);
        $type = ServiceType::all()->first(fn ($t) => Str::slug($t->name) === $slug);

        expect($type)->not->toBeNull()
            ->and($type->photo)->toBe('service-types/' . basename($file))
            ->and(Storage::disk('public')->exists($type->photo))->toBeTrue();
    }
});