<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Akun pemilik untuk masuk ke /admin (aturan 37). Wajib ada di semua environment.
 *
 * - Email: OWNER_EMAIL di .env, bawaan budi@dinamocakbudi.my.id.
 * - Password: OWNER_PASSWORD di .env. Di production WAJIB diisi (frasa sandi, minimal 10
 *   karakter) dan tidak pernah ditulis di kode, karena halaman login bisa dibuka siapa saja.
 *   Di lokal dan tes boleh kosong, memakai "password".
 */
class OwnerSeeder extends Seeder
{
    public const DEFAULT_EMAIL = 'budi@dinamocakbudi.my.id';

    public function run(): void
    {
        $password = (string) env('OWNER_PASSWORD', '');

        if (app()->environment('production') && mb_strlen($password) < 10) {
            throw new RuntimeException('Isi OWNER_PASSWORD di .env (minimal 10 karakter) sebelum menjalankan seeder di server.');
        }

        $owner = User::firstOrNew(['email' => env('OWNER_EMAIL', self::DEFAULT_EMAIL)]);

        // forceFill: role sengaja tidak masuk $fillable supaya tidak bisa diisi dari form publik.
        $owner->forceFill([
            'name' => 'Cak Budi',
            'role' => UserRole::Owner,
            'password' => $password !== '' ? $password : 'password', // di-hash oleh cast 'hashed'
        ])->save();
    }
}
