<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Pengaturan bengkel (aturan 25): nomor HP/WhatsApp bengkel (dipakai di landing dan tombol WA)
// dan link Google Maps lokasi bengkel (koordinat dibaca dari link ini, tidak diketik manual).
return new class extends Migration {
    public function up(): void
    {
        Schema::table('workshop_settings', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('id');
            $table->string('maps_url', 500)->nullable()->after('longitude');
        });

        // Baris yang sudah ada: nomor lama yang selama ini ditulis di kode, link dari koordinatnya.
        foreach (DB::table('workshop_settings')->get() as $row) {
            DB::table('workshop_settings')->where('id', $row->id)->update([
                'phone' => $row->phone ?? '08882509745',
                'maps_url' => $row->maps_url ?? ($row->latitude !== null && $row->longitude !== null
                    ? "https://www.google.com/maps?q={$row->latitude},{$row->longitude}"
                    : null),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('workshop_settings', function (Blueprint $table) {
            $table->dropColumn(['phone', 'maps_url']);
        });
    }
};
