<?php

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Models\Category;
use App\Models\SparePart;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

it('KAT-001: tabel menampilkan daftar kategori', function () {
    $a = Category::create(['name' => 'Starter']);
    $b = Category::create(['name' => 'Bearing']);

    Livewire::test(ManageCategories::class)
        ->assertCanSeeTableRecords([$a, $b]);
});

it('KAT-002: tidak ada halaman Tambah dan Ubah terpisah, semua lewat modal', function () {
    expect(CategoryResource::getPages())
        ->toHaveKey('index')
        ->not->toHaveKey('create')
        ->not->toHaveKey('edit');
});

it('TKT-001: tambah lewat modal dengan semua field valid', function () {
    Livewire::test(ManageCategories::class)
        ->callAction('create', data: ['name' => 'Kolektor', 'description' => 'Komutator dan perlengkapannya']);

    expect(Category::where('name', 'Kolektor')->exists())->toBeTrue();
});

it('TKT-002: deskripsi dikosongkan tetap tersimpan', function () {
    Livewire::test(ManageCategories::class)
        ->callAction('create', data: ['name' => 'Lainnya', 'description' => null]);

    expect(Category::where('name', 'Lainnya')->exists())->toBeTrue();
});

it('TKT-003: nama kategori dikosongkan ditolak', function () {
    Livewire::test(ManageCategories::class)
        ->callAction('create', data: ['name' => null, 'description' => 'Ada deskripsi']);

    expect(Category::count())->toBe(0);
});

it('TKT-004: spasi di awal dan akhir nama dibuang', function () {
    Livewire::test(ManageCategories::class)
        ->callAction('create', data: ['name' => '  Kolektor  ', 'description' => null]);

    expect(Category::where('name', 'Kolektor')->exists())->toBeTrue();
});

it('EKT-001: ubah lewat modal dengan nilai valid', function () {
    $category = Category::create(['name' => 'Starter', 'description' => 'Lama']);

    Livewire::test(ManageCategories::class)
        ->callAction(TestAction::make('edit')->table($category), data: ['description' => 'Baru']);

    expect($category->fresh()->description)->toBe('Baru');
});

it('HKT-001: hapus kategori yang belum dipakai', function () {
    $category = Category::create(['name' => 'Kosong']);

    Livewire::test(ManageCategories::class)
        ->callAction(TestAction::make('delete')->table($category));

    $this->assertModelMissing($category);
});

it('HKT-002: hapus kategori yang dipakai part tetap berhasil, part jadi tanpa kategori', function () {
    $category = Category::create(['name' => 'Starter']);
    $part = SparePart::create([
        'code' => 'SP-001',
        'name' => 'Brush Karbon',
        'category_id' => $category->id,
    ]);

    Livewire::test(ManageCategories::class)
        ->callAction(TestAction::make('delete')->table($category));

    $this->assertModelMissing($category);

    expect($part->fresh())->not->toBeNull()
        ->and($part->fresh()->category_id)->toBeNull();
});