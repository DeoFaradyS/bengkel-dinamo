<?php

use App\Enums\BookingType;
use App\Enums\ServiceSource;
use App\Enums\ServiceStatus;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Livewire\BookingForm;
use App\Models\Service;
use App\Models\ServiceType;
use Livewire\Livewire;

// Asal servis dan hak ubah admin (17 Nov 2026). Kode modul: SRC.
// Booking online: data yang ditulis pelanggan dikunci. Servis buatan admin bebas diubah.
// Jasa dan spare part baru muncul saat Dikerjakan atau Selesai.

function servisOnline(array $o = []): Service
{
    return Service::factory()->create(array_merge(['source' => 'online'], $o));
}

function ubahServis(Service $s)
{
    return Livewire::test(EditService::class, ['record' => $s->getRouteKey()]);
}

test('SRC-001: booking dari form beranda tercatat online', function () {
    jadiTamu();

    $tanggal = collect(range(1, 7))->map(fn ($i) => now()->addDays($i)->startOfDay())
        ->first(fn ($d) => \App\Support\Booking\Slots::bookable($d)->contains(fn ($s) => $s['available']));
    $jam = \App\Support\Booking\Slots::bookable($tanggal)->first(fn ($s) => $s['available'])['time']->format('Y-m-d H:i');

    Livewire::test(BookingForm::class)
        ->set('customer_name', 'Bu Sari')
        ->set('phone', '081234500077')
        ->set('complaint', 'Dinamo panas')
        ->set('slot_date', $tanggal->format('Y-m-d'))
        ->call('pilihSlot', $jam)
        ->call('submit')
        ->assertHasNoErrors();

    expect(Service::firstOrFail()->source)->toBe(ServiceSource::Online);
});

test('SRC-002: servis dari Tambah servis di panel tercatat admin', function () {
    Livewire::test(CreateService::class)
        ->fillForm(serviceFormData())
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Service::firstOrFail()->source)->toBe(ServiceSource::Admin);
});

test('SRC-003: booking online, isian data pelanggan diganti teks biasa di form ubah', function () {
    $s = servisOnline([
        'booking_type' => BookingType::HomeService,
        'latitude' => -7.7174465,
        'longitude' => 113.0755426,
    ]);

    ubahServis($s)
        ->assertFormFieldHidden('customer_name')
        ->assertFormFieldHidden('customer_phone')
        ->assertFormFieldHidden('complaint')
        ->assertFormFieldHidden('booking_type')
        ->assertFormFieldHidden('latitude')
        ->assertFormFieldHidden('longitude')
        ->assertFormFieldHidden('address_note')
        ->assertFormFieldHidden('scheduled_at')
        ->assertFormFieldEnabled('status')
        ->assertSee($s->customer_name)
        ->assertSee($s->customer_phone);
});

test('SRC-004: booking online, data pelanggan yang dipaksa diubah tetap tidak tersimpan', function () {
    $s = servisOnline([
        'customer_name' => 'Bu Sari',
        'customer_phone' => '081234500077',
        'complaint' => 'Dinamo panas',
    ]);

    ubahServis($s)
        ->set('data.customer_name', 'Nama Diganti')
        ->set('data.customer_phone', '089999999999')
        ->set('data.complaint', 'Keluhan diganti')
        ->set('data.booking_type', BookingType::HomeService->value)
        ->call('save');

    $s->refresh();

    expect($s->customer_name)->toBe('Bu Sari')
        ->and($s->customer_phone)->toBe('081234500077')
        ->and($s->complaint)->toBe('Dinamo panas')
        ->and($s->booking_type)->toBe(BookingType::Workshop);
});

// Awalnya (17 Nov 2026, tahap 1) jadwal Dikonfirmasi boleh digeser admin. Diputuskan ulang
// di hari yang sama: jadwal ikut terkunci, pelanggan membatalkan lalu booking ulang (BTL).
test('SRC-005: booking online Dikonfirmasi, jadwal juga terkunci', function () {
    $s = servisOnline(['status' => ServiceStatus::Confirmed]);
    $lama = $s->scheduled_at;

    ubahServis($s)
        ->assertFormFieldHidden('scheduled_at')
        ->set('data.scheduled_at', nextSlot(3, 10)->format('Y-m-d H:i:s'))
        ->call('save');

    expect($s->fresh()->scheduled_at->equalTo($lama))->toBeTrue();
});

test('SRC-006: booking online Dikerjakan, jadwal terkunci', function () {
    $s = servisOnline(['status' => ServiceStatus::InProgress]);
    $lama = $s->scheduled_at;

    ubahServis($s)
        ->assertFormFieldHidden('scheduled_at')
        ->set('data.scheduled_at', nextSlot(3, 10)->format('Y-m-d H:i:s'))
        ->call('save');

    expect($s->fresh()->scheduled_at->equalTo($lama))->toBeTrue();
});

test('SRC-007: servis buatan admin, data pelanggan tetap bisa diubah', function () {
    $s = Service::factory()->create(['source' => 'admin', 'customer_name' => 'Pak Budi']);

    ubahServis($s)
        ->assertFormFieldEnabled('customer_name')
        ->fillForm(['customer_name' => 'Pak Budi Santoso'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($s->fresh()->customer_name)->toBe('Pak Budi Santoso');
});

test('SRC-008: status Menunggu atau Dikonfirmasi, bagian Jasa dan Spare Part tidak tampil', function (ServiceStatus $status) {
    ubahServis(servisOnline(['status' => $status]))
        ->assertFormFieldHidden('items')
        ->assertFormFieldHidden('serviceParts');
})->with([
    'Pending' => ServiceStatus::Pending,
    'Confirmed' => ServiceStatus::Confirmed,
]);

test('SRC-009: status Dikerjakan, atau dipilih Selesai, bagian Jasa dan Spare Part tampil', function () {
    $s = servisOnline(['status' => ServiceStatus::InProgress]);

    ubahServis($s)
        ->assertFormFieldVisible('items')
        ->assertFormFieldVisible('serviceParts');

    $baru = servisOnline(['status' => ServiceStatus::Confirmed]);

    ubahServis($baru)
        ->fillForm(['status' => ServiceStatus::InProgress->value])
        ->assertFormFieldVisible('items')
        ->assertFormFieldVisible('serviceParts');
});

test('SRC-010: servis Dikonfirmasi yang sudah punya jasa, disimpan tanpa jasanya terhapus', function () {
    $type = ServiceType::factory()->create();
    $s = servisOnline(['status' => ServiceStatus::Confirmed]);
    $s->items()->create(['service_type_id' => $type->id, 'name' => $type->name, 'price' => 50000]);

    ubahServis($s)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($s->items()->count())->toBe(1);
});
