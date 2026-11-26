<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Garansi suku cadang (26 Nov 2026, aturan 38). Kosong = tanpa garansi.
// service_parts menyimpan salinan, jadi servis lama tidak berubah saat master diubah.
// Tanggal "sampai" tidak disimpan: dihitung dari services.paid_at + warranty_days.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spare_parts', function (Blueprint $table) {
            $table->unsignedSmallInteger('warranty_days')->nullable()->after('price');
        });

        Schema::table('service_parts', function (Blueprint $table) {
            $table->unsignedSmallInteger('warranty_days')->nullable()->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('service_parts', fn (Blueprint $table) => $table->dropColumn('warranty_days'));
        Schema::table('spare_parts', fn (Blueprint $table) => $table->dropColumn('warranty_days'));
    }
};
