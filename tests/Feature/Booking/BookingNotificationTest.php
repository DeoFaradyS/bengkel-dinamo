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
        'scheduled_at' => now()->addDay(),
    ], $override);
}

it('NTF-001: booking masuk, semua admin dapat satu notifikasi', function () {
    $a = User::factory()->create();
    $b = User::factory()->create();

    app(CreateBooking::class)->handle(notifBookingData());

    expect($a->notifications()->count())->toBe(1)
        ->and($b->notifications()->count())->toBe(1);
});

it('NTF-002: isi notifikasi memuat nama pelanggan', function () {
    $admin = User::factory()->create();

    app(CreateBooking::class)->handle(notifBookingData(['customer_name' => 'Bu Sari']));

    $data = $admin->notifications()->first()->data;

    expect($data['title'])->toContain('Booking baru')
        ->and($data['body'])->toContain('Bu Sari');
});

it('NTF-003: booking ditolak karena HP aktif, tidak ada notifikasi', function () {
    $admin = User::factory()->create();
    Service::factory()->create(['phone' => '081234500001']);

    expect(fn () => app(CreateBooking::class)->handle(notifBookingData()))
        ->toThrow(ValidationException::class);

    expect($admin->notifications()->count())->toBe(0);
});