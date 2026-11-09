<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Data referensi: wajib ada di semua environment, termasuk saat tes.
 * `code` harus sama dengan nilai enum ServiceStatus. Kode merujuk lewat code, bukan id.
 */
class StatusSeeder extends Seeder
{
    /** @var array<string, array{name: string, is_final: bool}> */
    private const STATUSES = [
        'pending'     => ['name' => 'Menunggu',     'is_final' => false],
        'confirmed'   => ['name' => 'Dikonfirmasi', 'is_final' => false],
        'in_progress' => ['name' => 'Dikerjakan',   'is_final' => false],
        'completed'   => ['name' => 'Selesai',      'is_final' => true],
        'cancelled'   => ['name' => 'Dibatalkan',   'is_final' => true],
        'rejected'    => ['name' => 'Ditolak',      'is_final' => true],
        'expired'     => ['name' => 'Kedaluwarsa',  'is_final' => true],
    ];

    public function run(): void
    {
        // upsert: aman dijalankan berulang, tidak menggandakan.
        DB::table('statuses')->upsert(
            collect(self::STATUSES)
                ->map(fn (array $s, string $code) => ['code' => $code, ...$s])
                ->values()
                ->all(),
            uniqueBy: ['code'],
            update: ['name', 'is_final'],
        );
    }
}