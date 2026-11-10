<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    Auth::logout();
    app('auth')->forgetGuards();
});

// Tes login dan register customer (D2). Kode modul: AUTH.
// Letakkan di tests/Feature/Auth/CustomerAuthTest.php.

function registerPayload(array $override = []): array
{
    return array_merge([
        'name' => 'Budi Pelanggan',
        'email' => 'budi@example.com',
        'phone' => '0812-3456-7890',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ], $override);
}

// ---------- Register ----------

test('AUTH-001: register berhasil membuat customer, langsung login, redirect ke beranda', function () {
    $this->post('/daftar', registerPayload())->assertRedirect('/');

    $user = User::where('email', 'budi@example.com')->firstOrFail();

    expect($user->role)->toBe(UserRole::Customer)
        ->and(Hash::check('rahasia123', $user->password))->toBeTrue();
    $this->assertAuthenticatedAs($user);
});

test('AUTH-002: nomor HP dinormalkan sebelum disimpan', function () {
    $this->post('/daftar', registerPayload());

    $phone = User::where('email', 'budi@example.com')->value('phone');

    expect($phone)->toMatch('/^0\d{8,14}$/');
});

test('AUTH-003: kolom role dari request diabaikan (tidak bisa daftar sebagai owner)', function () {
    $this->post('/daftar', registerPayload(['role' => 'owner']));

    expect(User::where('email', 'budi@example.com')->value('role'))
        ->toBe(UserRole::Customer);
});

test('AUTH-004: register ditolak kalau email sudah dipakai', function () {
    User::factory()->create(['email' => 'budi@example.com']);

    $this->post('/daftar', registerPayload())
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('AUTH-005: register ditolak kalau nomor HP tidak valid', function (string $phone) {
    $this->post('/daftar', registerPayload(['phone' => $phone]))
        ->assertSessionHasErrors(['phone' => 'Nomor HP tidak valid.']);

    $this->assertGuest();
})->with(['tanpa angka' => 'abc', 'kosong' => '', 'terlalu pendek' => '0812']);

test('AUTH-006: register ditolak kalau kata sandi pendek atau konfirmasi beda', function () {
    $this->post('/daftar', registerPayload(['password' => 'pendek', 'password_confirmation' => 'pendek']))
        ->assertSessionHasErrors('password');

    $this->post('/daftar', registerPayload(['password_confirmation' => 'beda12345']))
        ->assertSessionHasErrors('password');

    $this->assertGuest();
});

test('AUTH-007: percobaan register ke-11 dalam semenit ditolak', function () {
    for ($i = 0; $i < 10; $i++) {
        $this->post('/daftar', registerPayload(['name' => '']));
    }

    $this->post('/daftar', registerPayload(['name' => '']))->assertStatus(429);
});

// ---------- Login ----------

test('AUTH-008: login customer redirect ke beranda', function () {
    User::factory()->create(['email' => 'budi@example.com', 'password' => 'rahasia123']);

    $this->post('/masuk', ['email' => 'budi@example.com', 'password' => 'rahasia123'])
        ->assertRedirect('/');

    $this->assertAuthenticated();
});

test('AUTH-009: login owner redirect ke /admin', function () {
    User::factory()->owner()->create(['email' => 'owner@example.com', 'password' => 'rahasia123']);

    $this->post('/masuk', ['email' => 'owner@example.com', 'password' => 'rahasia123'])
        ->assertRedirect('/admin');
});

test('AUTH-010: kata sandi salah atau email tak dikenal memberi pesan sama', function () {
    User::factory()->create(['email' => 'budi@example.com', 'password' => 'rahasia123']);

    $this->post('/masuk', ['email' => 'budi@example.com', 'password' => 'salah'])
        ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);

    $this->post('/masuk', ['email' => 'tidakada@example.com', 'password' => 'rahasia123'])
        ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);

    $this->assertGuest();
});

test('AUTH-011: input kosong ditolak validasi', function () {
    $this->post('/masuk', [])->assertSessionHasErrors(['email', 'password']);
});

test('AUTH-012: percobaan login ke-6 dalam semenit ditolak', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->post('/masuk', ['email' => 'x@example.com', 'password' => 'salah']);
    }

    $this->post('/masuk', ['email' => 'x@example.com', 'password' => 'salah'])->assertStatus(429);
});

// ---------- Logout dan akses ----------

test('AUTH-013: logout mengakhiri sesi dan redirect ke beranda', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/keluar')->assertRedirect('/');

    $this->assertGuest();
});

test('AUTH-014: tamu tidak bisa logout (middleware auth)', function () {
    $this->post('/keluar')->assertRedirect();
    $this->assertGuest();
});

test('AUTH-015: user yang sudah login diarahkan dari /masuk dan /daftar', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/masuk')->assertRedirect();
    $this->get('/daftar')->assertRedirect();
});

test('AUTH-016: customer tidak bisa membuka /admin', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertForbidden();
});
// ---------- Tampilan ----------

test('AUTH-017: halaman /masuk tampil untuk tamu dengan formulir login', function () {
    $this->get('/masuk')
        ->assertOk()
        ->assertSee('action="/masuk"', false)
        ->assertSee('name="password"', false);
});

test('AUTH-018: halaman /daftar tampil untuk tamu dengan semua kolom', function () {
    $this->get('/daftar')
        ->assertOk()
        ->assertSee('action="/daftar"', false)
        ->assertSee('name="phone"', false)
        ->assertSee('name="password_confirmation"', false);
});

test('AUTH-019: navbar tamu memuat tautan Masuk dan Daftar, tanpa Keluar', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee(route('login'), false)
        ->assertSee(route('register'), false)
        ->assertDontSee('action="/keluar"', false);
});

test('AUTH-020: navbar customer memuat nama dan tombol Keluar, tanpa tautan Panel', function () {
    $user = User::factory()->create(['name' => 'Siti Pelanggan']);

    $this->actingAs($user)->get('/')
        ->assertOk()
        ->assertSee('Siti Pelanggan')
        ->assertSee('action="/keluar"', false)
        ->assertDontSee('href="/admin"', false);
});
