<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Asal servis: online (form booking di beranda) atau admin (Tambah servis di panel).
// Booking online: nama, HP, keluhan, tipe, dan lokasi dikunci di panel admin.
// Servis lama dianggap online karena asalnya tidak tercatat.
return new class extends Migration {
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('source', 10)->default('online')->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
