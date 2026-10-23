<?php

use App\Actions\CreateBooking;
use App\Enums\BookingType;
use App\Models\User;

function notifTipeData(array $override = []): array
{
    return array_merge([
        'customer_name' => 'Pak Budi',
        'phone' => '081234500004',
        'complaint' => 'Dinamo starter mati',
        'scheduled_at' => nextSlot(),
    ], $override);
}

it('NTF-010: booking home service, isi notifikasi menyebut Home Service', function () {
    $admin = User::factory()->create();

    app(CreateBooking::class)->handle(notifTipeData([
        'booking_type' => BookingType::HomeService->value,
        'latitude' => -7.7174465,
        'longitude' => 113.0755426,
    ]));

    $data = $admin->notifications()->first()->data;

    expect($data['title'])->toContain('Booking baru')
        ->and($data['body'])->toContain('Pak Budi')
        ->and($data['body'])->toContain('Home Service');
});

it('NTF-011: booking workshop, isi notifikasi menyebut Ke Bengkel', function () {
    $admin = User::factory()->create();

    app(CreateBooking::class)->handle(notifTipeData());

    $data = $admin->notifications()->first()->data;

    expect($data['body'])->toContain('Ke Bengkel')
        ->and($data['body'])->not->toContain('Home Service');
});