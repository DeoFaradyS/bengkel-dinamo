<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

// Slug jasa (D4): URL detail jasa jadi /layanan/ganti-bearing, bukan /layanan/3.
// Dibuat otomatis dari nama (ServiceType::booted). Jasa yang sudah ada diberi slug di sini.
return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        $dipakai = [];

        foreach (DB::table('service_types')->orderBy('id')->get(['id', 'name']) as $row) {
            $dasar = Str::slug($row->name) ?: 'jasa';
            $slug = $dasar;

            for ($i = 2; isset($dipakai[$slug]); $i++) {
                $slug = $dasar . '-' . $i;
            }

            $dipakai[$slug] = true;
            DB::table('service_types')->where('id', $row->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
