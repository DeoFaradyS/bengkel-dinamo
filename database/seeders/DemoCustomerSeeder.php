<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Dua akun pelanggan demo untuk mencoba booking, Riwayat, dan Profil (aturan 37).
 * Ikut dibuat di server juga. Password sengaja sederhana karena isinya hanya data demo;
 * jangan dipakai untuk pelanggan sungguhan.
 */
class DemoCustomerSeeder extends Seeder
{
    public const PASSWORD = 'pelanggan123';

    /** @var array<int, array{email: string, name: string, phone: string}> */
    public const ACCOUNTS = [
        ['email' => 'pelanggan1@dinamocakbudi.my.id', 'name' => 'Pelanggan Demo 1', 'phone' => '081234567890'],
        ['email' => 'pelanggan2@dinamocakbudi.my.id', 'name' => 'Pelanggan Demo 2', 'phone' => '081234567891'],
    ];

    public function run(): void
    {
        foreach (self::ACCOUNTS as $account) {
            User::firstOrNew(['email' => $account['email']])->forceFill([
                'name' => $account['name'],
                'role' => UserRole::Customer,
                'phone' => $account['phone'],
                'password' => self::PASSWORD,
            ])->save();
        }
    }
}
