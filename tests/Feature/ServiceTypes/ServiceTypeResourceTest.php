<?php

use App\Filament\Resources\ServiceTypes\Pages\CreateServiceType;
use App\Filament\Resources\ServiceTypes\Pages\EditServiceType;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
    Storage::fake('public');
});

test('JSA-001: tambah jasa dengan deskripsi dan foto', function () {
    Livewire::test(CreateServiceType::class)
        ->fillForm([
            'name' => 'Gulung Dinamo',
            'default_price' => 150000,
            'description' => 'Gulung ulang kumparan.',
            'photo' => UploadedFile::fake()->image('gulung.jpg'),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $type = ServiceType::where('name', 'Gulung Dinamo')->first();
    expect($type)->not->toBeNull()
        ->and($type->description)->toBe('Gulung ulang kumparan.');
    Storage::disk('public')->assertExists($type->photo);
});

test('JSA-002: nama wajib diisi', function () {
    Livewire::test(CreateServiceType::class)
        ->fillForm(['name' => null, 'default_price' => 0])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

test('JSA-003: harga acuan tidak boleh negatif', function () {
    Livewire::test(CreateServiceType::class)
        ->fillForm(['name' => 'Tes', 'default_price' => -1])
        ->call('create')
        ->assertHasFormErrors(['default_price']);
});

test('JSA-004: deskripsi dan foto boleh kosong, harga acuan 0', function () {
    Livewire::test(CreateServiceType::class)
        ->fillForm(['name' => 'Cek Saja', 'default_price' => 0])
        ->call('create')
        ->assertHasNoFormErrors();

    $type = ServiceType::where('name', 'Cek Saja')->first();
    expect($type->description)->toBeNull()
        ->and($type->photo)->toBeNull()
        ->and((float) $type->default_price)->toBe(0.0);
});

test('JSA-005: ubah jasa lewat halaman edit', function () {
    $type = ServiceType::factory()->create(['name' => 'Lama']);

    Livewire::test(EditServiceType::class, ['record' => $type->getRouteKey()])
        ->fillForm(['name' => 'Baru', 'description' => 'Deskripsi baru'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($type->refresh()->name)->toBe('Baru')
        ->and($type->description)->toBe('Deskripsi baru');
});

test('JSA-006: photo_url null tanpa foto, terisi kalau ada foto', function () {
    $tanpa = ServiceType::factory()->create(['photo' => null]);
    $dengan = ServiceType::factory()->create(['photo' => 'service-types/a.jpg']);

    expect($tanpa->photo_url)->toBeNull()
        ->and($dengan->photo_url)->toContain('service-types/a.jpg');
});
/* ------------------------------------------------------------------ */
/* Hapus jasa = arsip (soft delete), keputusan D1 17 Nov 2026          */
/* ------------------------------------------------------------------ */

test('JSA-014: hapus jasa dari tabel mengarsipkan (soft delete), foto tidak ikut terhapus', function () {
    Storage::disk('public')->put('service-types/gulung.jpg', 'x');
    $type = ServiceType::factory()->create(['photo' => 'service-types/gulung.jpg']);

    Livewire::test(\App\Filament\Resources\ServiceTypes\Pages\ListServiceTypes::class)
        ->callAction(\Filament\Actions\Testing\TestAction::make('delete')->table($type));

    $this->assertSoftDeleted($type);
    Storage::disk('public')->assertExists('service-types/gulung.jpg');
});

test('JSA-015: jasa terarsip tidak muncul di beranda, /layanan, detailnya 404, dan tidak bisa dipilih di form servis', function () {
    $aktif = ServiceType::factory()->create(['name' => 'Jasa Aktif ZZZ']);
    $arsip = ServiceType::factory()->create(['name' => 'Jasa Arsip QQQ']);
    $arsip->delete();

    $this->get('/')->assertSee('Jasa Aktif ZZZ')->assertDontSee('Jasa Arsip QQQ');
    $this->get('/layanan')->assertSee('Jasa Aktif ZZZ')->assertDontSee('Jasa Arsip QQQ');
    $this->get('/layanan/' . $arsip->id)->assertNotFound();

    Livewire::test(\App\Filament\Resources\Services\Pages\CreateService::class)
        ->fillForm(serviceFormData(['items' => [['service_type_id' => $arsip->id]]]))
        ->call('create')
        ->assertHasFormErrors();
});

test('JSA-016: servis aktif yang memakai jasa terarsip tetap bisa dibuka dan disimpan', function () {
    $type = ServiceType::factory()->create(['name' => 'Ganti bearing']);
    $service = \App\Models\Service::factory()->create();
    $service->items()->create(['service_type_id' => $type->id, 'name' => $type->name, 'price' => 100000]);
    $type->delete();

    Livewire::test(\App\Filament\Resources\Services\Pages\EditService::class, ['record' => $service->getRouteKey()])
        ->assertSuccessful()
        ->call('save')
        ->assertHasNoFormErrors();

    expect($service->items()->count())->toBe(1)
        ->and((float) $service->items()->first()->price)->toBe(100000.0);
});
