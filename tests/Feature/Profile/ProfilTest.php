<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

test('PRF-001: simpan nama dan nomor, nomor disimpan dalam bentuk normal', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put('/profil', ['name' => 'Budi Baru', 'phone' => '+62 812-3456-7890'])
        ->assertRedirect('/profil')
        ->assertSessionHas('status');

    expect($user->fresh())->name->toBe('Budi Baru')->phone->toBe('081234567890');
});

test('PRF-002: nama kosong ditolak', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put('/profil', ['name' => '', 'phone' => '081234567890'])
        ->assertSessionHasErrors('name');
});

test('PRF-003: nomor HP boleh kosong', function () {
    $user = User::factory()->create(['phone' => '081234567890']);

    $this->actingAs($user)->put('/profil', ['name' => $user->name, 'phone' => ''])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->phone)->toBeNull();
});

test('PRF-004: nomor tidak valid ditolak', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put('/profil', ['name' => $user->name, 'phone' => 'abc'])
        ->assertSessionHasErrors('phone');
});

test('PRF-005: email dan role tidak bisa diubah lewat input paksa', function () {
    $user = User::factory()->create(['email' => 'asli@example.com']);

    $this->actingAs($user)->put('/profil', [
        'name' => 'Nama Baru',
        'phone' => '081234567890',
        'email' => 'palsu@example.com',
        'role' => 'owner',
    ]);

    expect($user->fresh())->email->toBe('asli@example.com')->role->toBe(UserRole::Customer);
});

test('PRF-006: owner terlempar ke panel', function () {
    $owner = User::factory()->owner()->create();

    $this->actingAs($owner)->get('/profil')->assertRedirect('/admin');
    $this->actingAs($owner)->put('/profil', ['name' => 'X', 'phone' => ''])->assertRedirect('/admin');
});

test('PRF-007: tamu diarahkan ke halaman masuk', function () {
    Auth::logout();
    app('auth')->forgetGuards();

    $this->get('/profil')->assertRedirect('/masuk');
});