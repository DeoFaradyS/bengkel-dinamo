<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Akun customer untuk demo dan uji manual. Hanya jalan di environment local. */
class DemoCustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::firstOrNew(['email' => 'customer@bengkel-dinamo.test']);

        $customer->forceFill([
            'name' => 'Pelanggan Demo',
            'role' => UserRole::Customer,
            'phone' => '081234567890',
            'password' => 'password',
        ])->save();
    }
}