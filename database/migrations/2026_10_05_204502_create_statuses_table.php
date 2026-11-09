<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tanggalnya sengaja lebih awal dari create_services_table: services punya FK ke tabel ini.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            // Kunci yang dirujuk kode lewat enum ServiceStatus. Jangan merujuk lewat id.
            $table->string('code', 20)->unique();
            $table->string('name', 50);
            // Status akhir: selesai, batal, ditolak, kedaluwarsa. Tidak berpindah lagi.
            $table->boolean('is_final')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statuses');
    }
};