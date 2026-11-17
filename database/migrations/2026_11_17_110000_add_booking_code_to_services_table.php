<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Kode booking 6 angka. Dipakai pelanggan untuk Cek Booking bersama nomor HP,
// supaya status booking tidak bisa dilihat hanya dengan nama dan nomor HP.
return new class extends Migration {
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->char('booking_code', 6)->nullable()->unique()->after('id');
        });

        // Servis lama ikut dapat kode.
        $dipakai = [];

        DB::table('services')->whereNull('booking_code')->orderBy('id')->pluck('id')
            ->each(function ($id) use (&$dipakai) {
                do {
                    $kode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                } while (isset($dipakai[$kode]));

                $dipakai[$kode] = true;
                DB::table('services')->where('id', $id)->update(['booking_code' => $kode]);
            });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropUnique(['booking_code']);
            $table->dropColumn('booking_code');
        });
    }
};
