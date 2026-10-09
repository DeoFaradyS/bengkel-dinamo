<?php

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Models\SparePart;

use function Pest\Laravel\actingAs;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    actingAs(User::factory()->create());
});

it('TKT-001: submit dengan semua field valid', function () {
    Livewire::test(CreateCategory::class)
        ->fillForm(['name' => 'Kolektor', 'description' => 'Komutator dan perlengkapannya'])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect(CategoryResource::getUrl('index'));

    expect(Category::where('name', 'Kolektor')->exists())->toBeTrue();
});

it('TKT-002: deskripsi dikosongkan tetap tersimpan', function () {
    Livewire::test(CreateCategory::class)
        ->fillForm(['name' => 'Lainnya', 'description' => null])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Category::where('name', 'Lainnya')->exists())->toBeTrue();
});

it('TKT-003: nama kategori dikosongkan ditolak', function () {
    Livewire::test(CreateCategory::class)
        ->fillForm(['name' => null, 'description' => 'Ada deskripsi'])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);

    expect(Category::count())->toBe(0);
});

it('EKT-001: ubah data dengan valid value', function () {
    $category = Category::create(['name' => 'Starter', 'description' => 'Lama']);

    Livewire::test(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm(['description' => 'Baru'])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertRedirect(CategoryResource::getUrl('index'));

    expect($category->fresh()->description)->toBe('Baru');
});

it('HKT-001: hapus kategori yang belum dipakai', function () {
    $category = Category::create(['name' => 'Kosong']);

    Livewire::test(ListCategories::class)
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

    Livewire::test(ListCategories::class)
        ->callAction(TestAction::make('delete')->table($category));

    $this->assertModelMissing($category);

    expect($part->fresh())->not->toBeNull()
        ->and($part->fresh()->category_id)->toBeNull();
});