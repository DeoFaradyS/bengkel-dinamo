<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Akun pemilik untuk masuk ke /admin. Wajib ada di semua environment.
 * Di produksi, isi OWNER_EMAIL dan OWNER_PASSWORD di .env.
 */
class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::firstOrNew(['email' => env('OWNER_EMAIL', 'owner@bengkel-dinamo.test')]);

        // forceFill: role sengaja tidak masuk $fillable supaya tidak bisa diisi dari form publik.
        $owner->forceFill([
            'name' => 'Cak Budi',
            'role' => UserRole::Owner,
            'password' => env('OWNER_PASSWORD', 'password'), // di-hash oleh cast 'hashed'
        ])->save();
    }
}