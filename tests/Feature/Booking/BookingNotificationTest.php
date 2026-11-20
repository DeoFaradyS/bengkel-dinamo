<?php

use App\Actions\CreateBooking;
use App\Models\Service;
use App\Models\User;
use Illuminate\Validation\ValidationException;

function notifBookingData(array $override = []): array
{
    return array_merge([
        'customer_name' => 'Bu Sari',
        'phone' => '081234500001',
        'complaint' => 'Dinamo panas',
        'scheduled_at' => nextSlot(),
    ], $override);
}

it('NTF-001: booking masuk, semua owner dapat satu notifikasi', function () {
    $a = User::factory()->owner()->create();
    $b = User::factory()->owner()->create();

    app(CreateBooking::class)->handle(notifBookingData());

    expect($a->notifications()->count())->toBe(1)
        ->and($b->notifications()->count())->toBe(1);
});

it('NTF-002: isi notifikasi memuat nama pelanggan', function () {
    $admin = User::factory()->owner()->create();

    app(CreateBooking::class)->handle(notifBookingData(['customer_name' => 'Bu Sari']));

    $data = $admin->notifications()->first()->data;

    expect($data['title'])->toContain('Booking baru')
        ->and($data['body'])->toContain('Bu Sari');
});

it('NTF-003: booking ditolak karena HP aktif, tidak ada notifikasi', function () {
    $admin = User::factory()->owner()->create();
    Service::factory()->create(['customer_phone' => '081234500001']);

    expect(fn () => app(CreateBooking::class)->handle(notifBookingData()))
        ->toThrow(ValidationException::class);

    expect($admin->notifications()->count())->toBe(0);
});
// Notifikasi bisa diklik ke detail servis (21 Nov 2026, aturan 36).

it('NTF-004: notifikasi booking baru membawa link ke detail servis dan ditandai dibaca saat diklik', function () {
    $admin = User::factory()->owner()->create();

    $service = app(CreateBooking::class)->handle(notifBookingData());

    $aksi = $admin->notifications()->first()->data['actions'][0];

    expect($aksi['url'])->toBe(\App\Filament\Resources\Services\ServiceResource::getUrl('view', ['record' => $service], panel: 'admin'))
        ->and($aksi['shouldMarkAsRead'])->toBeTrue()
        ->and($aksi['extraAttributes']['class'])->toBe(\App\Support\OwnerNotification::OPEN_CLASS);
});

it('NTF-005: notifikasi pembatalan oleh pelanggan juga membawa link ke detail servis', function () {
    $admin = User::factory()->owner()->create();
    $service = Service::factory()->create(['status' => \App\Enums\ServiceStatus::Pending]);

    app(\App\Actions\CancelBookingByCustomer::class)->handle($service, 'Ganti jadwal');

    expect($admin->notifications()->first()->data['actions'][0]['url'])
        ->toBe(\App\Filament\Resources\Services\ServiceResource::getUrl('view', ['record' => $service], panel: 'admin'));
});
