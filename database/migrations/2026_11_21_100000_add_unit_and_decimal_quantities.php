<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Satuan suku cadang dan jumlah desimal (aturan 33), mengikuti standar software persediaan:
 * setiap barang punya satuan, dan jumlah boleh pecahan untuk barang yang ditimbang (0,35 kg).
 * - spare_parts.unit (pcs, set, pasang, kg, meter, liter), default pcs.
 * - stok, stok minimal, qty servis, dan riwayat stok: integer -> decimal(10,2).
 * Data lama: nama yang berakhiran "(kg)" atau "(set)" dipindah ke kolom satuan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spare_parts', function (Blueprint $table) {
            $table->string('unit', 10)->default('pcs')->after('name');
            $table->decimal('stock', 10, 2)->default(0)->change();
            $table->decimal('min_stock', 10, 2)->default(1)->change();
        });

        Schema::table('service_parts', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->default(1)->change();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('quantity_change', 10, 2)->change();
            $table->decimal('stock_after', 10, 2)->change();
        });

        foreach (['kg', 'set'] as $unit) {
            DB::table('spare_parts')->where('name', 'like', "% ({$unit})")->orderBy('id')->each(function ($part) use ($unit) {
                DB::table('spare_parts')->where('id', $part->id)->update([
                    'unit' => $unit,
                    'name' => preg_replace('/\s*\(' . $unit . '\)$/i', '', $part->name),
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->integer('quantity_change')->change();
            $table->unsignedInteger('stock_after')->change();
        });

        Schema::table('service_parts', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1)->change();
        });

        Schema::table('spare_parts', function (Blueprint $table) {
            $table->unsignedInteger('stock')->default(0)->change();
            $table->unsignedInteger('min_stock')->default(1)->change();
            $table->dropColumn('unit');
        });
    }
};
