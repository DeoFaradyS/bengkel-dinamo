<?php

use App\Filament\Resources\SpareParts\Pages\CreateSparePart;
use App\Filament\Resources\SpareParts\Pages\EditSparePart;
use App\Filament\Resources\SpareParts\Pages\ListSpareParts;
use App\Models\Category;
use App\Models\SparePart;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

use function Pest\Laravel\actingAs;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    actingAs(User::factory()->create());
    Storage::fake('public');
});

// Data form valid. Field yang mau diuji tinggal ditimpa lewat $override.
function partData(array $override = []): array
{
    return array_merge([
        'code' => 'SP-001',
        'name' => 'Brush Karbon',
        'stock' => 10,
        'min_stock' => 2,
        'price' => 150000,
    ], $override);
}

function makePart(array $override = []): SparePart
{
    return SparePart::create(array_merge([
        'code' => 'SP-001',
        'name' => 'Brush Karbon',
    ], $override));
}

/* ------------------------------------------------------------------ */
/* TSP: tambah                                                         */
/* ------------------------------------------------------------------ */

it('TSP-001: submit dengan semua field valid', function () {
    $category = Category::create(['name' => 'Starter']);

    Livewire::test(CreateSparePart::class)
        ->fillForm(partData([
            'category_id' => $category->id,
            'location' => 'Rak A1',
            'photo' => UploadedFile::fake()->image('sp-001.jpg'),
        ]))
        ->call('create')
        ->assertHasNoFormErrors();

    expect(SparePart::where('code', 'SP-001')->exists())->toBeTrue();
});

it('TSP-002: field opsional dikosongkan tetap tersimpan', function () {
    Livewire::test(CreateSparePart::class)
        ->fillForm(partData(['category_id' => null, 'location' => null, 'photo' => null]))
        ->call('create')
        ->assertHasNoFormErrors();

    expect(SparePart::where('code', 'SP-001')->exists())->toBeTrue();
});

it('TSP-003: code dikosongkan ditolak', function () {
    Livewire::test(CreateSparePart::class)
        ->fillForm(partData(['code' => null]))
        ->call('create')
        ->assertHasFormErrors(['code' => 'required']);

    expect(SparePart::count())->toBe(0);
});

it('TSP-004: code duplikat ditolak', function () {
    makePart(['code' => 'SP-001']);

    Livewire::test(CreateSparePart::class)
        ->fillForm(partData(['code' => 'SP-001', 'name' => 'Lain']))
        ->call('create')
        ->assertHasFormErrors(['code' => 'unique']);

    expect(SparePart::count())->toBe(1);
});

it('TSP-005: name dikosongkan ditolak', function () {
    Livewire::test(CreateSparePart::class)
        ->fillForm(partData(['name' => null]))
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('TSP-006: category_id yang tidak ada ditolak', function () {
    Livewire::test(CreateSparePart::class)
        ->fillForm(partData(['category_id' => 9999]))
        ->call('create')
        ->assertHasFormErrors(['category_id']);
});

// TSP-007 sampai TSP-013: pola sama, digabung pakai dataset.
it('TSP-007..013: angka kosong, negatif, atau desimal ditolak', function (string $field, mixed $value) {
    Livewire::test(CreateSparePart::class)
        ->fillForm(partData([$field => $value]))
        ->call('create')
        ->assertHasFormErrors([$field]);

    expect(SparePart::count())->toBe(0);
})->with([
    'TSP-007 stock kosong' => ['stock', null],
    'TSP-008 stock negatif' => ['stock', -1],
    'TSP-009 stock desimal' => ['stock', '1.5'],
    'TSP-010 min_stock kosong' => ['min_stock', null],
    'TSP-011 min_stock negatif' => ['min_stock', -1],
    'TSP-012 price kosong' => ['price', null],
    'TSP-013 price negatif' => ['price', -1],
]);

it('TSP-014: foto JPG atau PNG lolos', function (string $file) {
    Livewire::test(CreateSparePart::class)
        ->fillForm(partData(['photo' => UploadedFile::fake()->image($file)]))
        ->call('create')
        ->assertHasNoFormErrors();
})->with(['jpg' => ['a.jpg'], 'png' => ['a.png']]);

it('TSP-015: foto selain JPG atau PNG ditolak', function () {
    Livewire::test(CreateSparePart::class)
        ->fillForm(partData(['photo' => UploadedFile::fake()->image('a.gif')]))
        ->call('create')
        ->assertHasFormErrors(['photo']);
});

it('TSP-016: foto lebih dari 2 MB ditolak', function () {
    Livewire::test(CreateSparePart::class)
        ->fillForm(partData(['photo' => UploadedFile::fake()->image('besar.jpg')->size(3000)]))
        ->call('create')
        ->assertHasFormErrors(['photo']);
});

/* ------------------------------------------------------------------ */
/* ESP: ubah                                                           */
/* ------------------------------------------------------------------ */

it('ESP-001: ubah data dengan valid value', function () {
    $part = makePart();

    Livewire::test(EditSparePart::class, ['record' => $part->getRouteKey()])
        ->fillForm(['name' => 'Brush Baru', 'stock' => 20])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($part->fresh()->name)->toBe('Brush Baru')
        ->and($part->fresh()->stock)->toBe(20);
});

it('ESP-002: simpan tanpa ubah code tidak bentrok dengan diri sendiri', function () {
    $part = makePart();

    Livewire::test(EditSparePart::class, ['record' => $part->getRouteKey()])
        ->fillForm(['name' => 'Nama Lain'])
        ->call('save')
        ->assertHasNoFormErrors();
});

it('ESP-003: ubah code ke kode part lain ditolak', function () {
    makePart(['code' => 'SP-001']);
    $b = makePart(['code' => 'SP-002', 'name' => 'Bearing']);

    Livewire::test(EditSparePart::class, ['record' => $b->getRouteKey()])
        ->fillForm(['code' => 'SP-001'])
        ->call('save')
        ->assertHasFormErrors(['code' => 'unique']);
});

it('ESP-004: name dikosongkan ditolak', function () {
    $part = makePart();

    Livewire::test(EditSparePart::class, ['record' => $part->getRouteKey()])
        ->fillForm(['name' => null])
        ->call('save')
        ->assertHasFormErrors(['name' => 'required']);
});

it('ESP-005: kategori dikosongkan berhasil', function () {
    $category = Category::create(['name' => 'Starter']);
    $part = makePart(['category_id' => $category->id]);

    Livewire::test(EditSparePart::class, ['record' => $part->getRouteKey()])
        ->fillForm(['category_id' => null])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($part->fresh()->category_id)->toBeNull();
});

/* ------------------------------------------------------------------ */
/* HSP: hapus (HSP-002 butuh blok booted() di model SparePart)         */
/* ------------------------------------------------------------------ */

it('HSP-001: hapus part (soft delete)', function () {
    $part = makePart();

    Livewire::test(ListSpareParts::class)
        ->callAction(TestAction::make('delete')->table($part));

    $this->assertSoftDeleted($part);
});

it('HSP-002: kode part yang sudah dihapus bisa dipakai part baru', function () {
    $part = makePart(['code' => 'SP-001']);

    Livewire::test(ListSpareParts::class)
        ->callAction(TestAction::make('delete')->table($part));

    Livewire::test(CreateSparePart::class)
        ->fillForm(partData(['code' => 'SP-001']))
        ->call('create')
        ->assertHasNoFormErrors();

    expect(SparePart::where('code', 'SP-001')->count())->toBe(1);
});

/* ------------------------------------------------------------------ */
/* LSP: stok rendah. Baris merah = class bg-red-50! di recordClasses   */
/* ------------------------------------------------------------------ */

it('LSP-001: stock di bawah min_stock, baris merah', function () {
    $part = makePart(['stock' => 1, 'min_stock' => 5]);

    Livewire::test(ListSpareParts::class)
        ->assertCanSeeTableRecords([$part])
        ->assertSeeHtml('bg-red-50!');
});

it('LSP-002: stock sama dengan min_stock, baris merah', function () {
    $part = makePart(['stock' => 5, 'min_stock' => 5]);

    Livewire::test(ListSpareParts::class)
        ->assertCanSeeTableRecords([$part])
        ->assertSeeHtml('bg-red-50!');
});

it('LSP-003: stock di atas min_stock, tidak merah', function () {
    $part = makePart(['stock' => 10, 'min_stock' => 5]);

    Livewire::test(ListSpareParts::class)
        ->assertCanSeeTableRecords([$part])
        ->assertDontSeeHtml('bg-red-50!');
});

/* ------------------------------------------------------------------ */
/* SSP: search                                                         */
/* ------------------------------------------------------------------ */

it('SSP-001: cari pakai kode', function () {
    $a = makePart(['code' => 'SP-001', 'name' => 'Brush']);
    $b = makePart(['code' => 'SP-002', 'name' => 'Bearing']);

    Livewire::test(ListSpareParts::class)
        ->searchTable('SP-001')
        ->assertCanSeeTableRecords([$a])
        ->assertCanNotSeeTableRecords([$b]);
});

it('SSP-002: cari pakai nama', function () {
    $a = makePart(['code' => 'SP-001', 'name' => 'Brush']);
    $b = makePart(['code' => 'SP-002', 'name' => 'Bearing']);

    Livewire::test(ListSpareParts::class)
        ->searchTable('Bearing')
        ->assertCanSeeTableRecords([$b])
        ->assertCanNotSeeTableRecords([$a]);
});

it('SSP-003: cari kata yang tidak ada, tabel kosong', function () {
    $a = makePart();

    Livewire::test(ListSpareParts::class)
        ->searchTable('tidak-ada-xyz')
        ->assertCanNotSeeTableRecords([$a]);
});

/* ------------------------------------------------------------------ */
/* FSP: filter kategori. Nama filter 'category_id' = tebakan,          */
/* samakan dengan SelectFilter::make('...') di tabel.                  */
/* ------------------------------------------------------------------ */

it('FSP-001: filter 1 kategori', function () {
    $k1 = Category::create(['name' => 'Starter']);
    $k2 = Category::create(['name' => 'Dinamo']);
    $a = makePart(['code' => 'A', 'category_id' => $k1->id]);
    $b = makePart(['code' => 'B', 'category_id' => $k2->id]);

    Livewire::test(ListSpareParts::class)
        ->filterTable('category_id', [$k1->id])
        ->assertCanSeeTableRecords([$a])
        ->assertCanNotSeeTableRecords([$b]);
});

it('FSP-002: filter 2 kategori sekaligus', function () {
    $k1 = Category::create(['name' => 'Starter']);
    $k2 = Category::create(['name' => 'Dinamo']);
    $k3 = Category::create(['name' => 'Kolektor']);
    $a = makePart(['code' => 'A', 'category_id' => $k1->id]);
    $b = makePart(['code' => 'B', 'category_id' => $k2->id]);
    $c = makePart(['code' => 'C', 'category_id' => $k3->id]);

    Livewire::test(ListSpareParts::class)
        ->filterTable('category_id', [$k1->id, $k2->id])
        ->assertCanSeeTableRecords([$a, $b])
        ->assertCanNotSeeTableRecords([$c]);
});

it('FSP-003: tanpa filter, semua part tampil', function () {
    $k1 = Category::create(['name' => 'Starter']);
    $a = makePart(['code' => 'A', 'category_id' => $k1->id]);
    $b = makePart(['code' => 'B']);

    Livewire::test(ListSpareParts::class)
        ->assertCanSeeTableRecords([$a, $b]);
});