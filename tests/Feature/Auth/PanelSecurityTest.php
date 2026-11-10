<?php

use App\Actions\CreateBooking;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('SEC-001: customer tidak bisa membuka panel /admin', function () {
    $customer = User::factory()->create(['role' => UserRole::Customer]);

    $this->actingAs($customer)->get('/admin')->assertForbidden();
});

it('SEC-002: owner bisa membuka panel /admin', function () {
    $owner = User::factory()->create(['role' => UserRole::Owner]);

    $this->actingAs($owner)->get('/admin')->assertOk();
});

it('SEC-003: notifikasi booking hanya masuk ke owner', function () {
    $owner = User::factory()->create(['role' => UserRole::Owner]);
    $customer = User::factory()->create(['role' => UserRole::Customer]);

    app(CreateBooking::class)->handle([
        'customer_name' => 'Budi',
        'phone' => '081234567890',
        'complaint' => 'Dinamo starter mati',
        'scheduled_at' => nextSlot(),
    ]);

    expect($owner->notifications()->count())->toBe(1)
        ->and($customer->notifications()->count())->toBe(0);
});