<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// D1: jasa yang dihapus diarsipkan (soft delete), bukan dihapus permanen,
// supaya tetap tersimpan meski hilang dari katalog dan form servis.
return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
