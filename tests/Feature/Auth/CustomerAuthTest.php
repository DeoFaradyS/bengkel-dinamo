<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    jadiTamu();
});

// Tes login dan register customer (D2). Kode modul: AUTH.
// Letakkan di tests/Feature/Auth/CustomerAuthTest.php.

function registerPayload(array $override = []): array
{
    return array_merge([
        'name' => 'Budi Pelanggan',
        'email' => 'budi@example.com',
        'password' => 'rahasia123',
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

test('AUTH-006: register ditolak kalau kata sandi kurang dari 8 karakter', function () {
    $this->post('/daftar', registerPayload(['password' => 'pendek']))
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
        ->assertSee('name="email"', false)
        ->assertDontSee('name="phone"', false);
});

// ---------- Login satu pintu ----------

test('AUTH-022: tamu yang membuka /admin diarahkan ke /masuk', function () {
    $this->get('/admin')->assertRedirect(route('login'));
});

test('AUTH-023: halaman login bawaan panel /admin/login tidak ada (404)', function () {
    $this->get('/admin/login')->assertNotFound();
});

test('AUTH-024: owner login lewat /masuk lalu bisa membuka /admin', function () {
    User::factory()->owner()->create(['email' => 'owner@example.com', 'password' => 'rahasia123']);

    $this->post('/masuk', ['email' => 'owner@example.com', 'password' => 'rahasia123'])
        ->assertRedirect('/admin');

    $this->get('/admin')->assertOk();
});
