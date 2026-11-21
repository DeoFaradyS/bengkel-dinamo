<?php

use App\Enums\UserRole;
use App\Models\Service;
use App\Models\SparePart;
use App\Models\User;
use Database\Seeders\DemoCustomerSeeder;
use Database\Seeders\OwnerSeeder;
use Illuminate\Support\Facades\Hash;

// Akun bawaan dan isi seeder di server (21 Nov 2026, aturan 37). Kode modul: AKN.

function aknEnv(?string $password): void
{
    if ($password === null) {
        putenv('OWNER_PASSWORD');
        unset($_ENV['OWNER_PASSWORD'], $_SERVER['OWNER_PASSWORD']);

        return;
    }
    putenv("OWNER_PASSWORD={$password}");
    $_ENV['OWNER_PASSWORD'] = $_SERVER['OWNER_PASSWORD'] = $password;
}

afterEach(function () {
    aknEnv(null);
    app()->detectEnvironment(fn () => 'testing');
});

test('AKN-001: lokal, seeder membuat pemilik dan 2 pelanggan demo yang bisa login dengan password mudah', function () {
    $this->seed();

    $pemilik = User::where('email', OwnerSeeder::DEFAULT_EMAIL)->firstOrFail();
    $pelanggan = User::whereIn('email', array_column(DemoCustomerSeeder::ACCOUNTS, 'email'))->get();

    expect($pemilik->role)->toBe(UserRole::Owner)
        ->and($pelanggan)->toHaveCount(2)
        ->and($pelanggan->every(fn ($u) => $u->role === UserRole::Customer && Hash::check(DemoCustomerSeeder::PASSWORD, $u->password)))->toBeTrue();
});

test('AKN-002: server (production) tanpa OWNER_PASSWORD, seeder menolak jalan', function () {
    app()->detectEnvironment(fn () => 'production');
    aknEnv(null);

    expect(fn () => app(\Database\Seeders\DatabaseSeeder::class)->__invoke())->toThrow(RuntimeException::class, 'OWNER_PASSWORD');

    expect(User::where('role', UserRole::Owner->value)->count())->toBe(0);
});

test('AKN-003: server dengan OWNER_PASSWORD, ada pemilik dan 2 pelanggan demo, tanpa suku cadang dan servis contoh', function () {
    app()->detectEnvironment(fn () => 'production');
    aknEnv('ayam-genteng-sepeda-58');

    // Langsung lewat kelas seeder: perintah db:seed di production meminta konfirmasi.
    app(\Database\Seeders\DatabaseSeeder::class)->__invoke();

    $pemilik = User::where('email', OwnerSeeder::DEFAULT_EMAIL)->firstOrFail();

    expect(Hash::check('ayam-genteng-sepeda-58', $pemilik->password))->toBeTrue()
        ->and(Hash::check('password', $pemilik->password))->toBeFalse()
        ->and(User::whereIn('email', array_column(DemoCustomerSeeder::ACCOUNTS, 'email'))->count())->toBe(2)
        ->and(SparePart::count())->toBe(0)
        ->and(Service::count())->toBe(0);
});
