<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spare_part_id')->constrained('spare_parts')->cascadeOnDelete();
            $table->string('type');
            $table->integer('quantity');
            $table->unsignedInteger('stock_after');
            $table->string('note')->nullable();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Stok yang sudah ada dicatat sebagai stok awal.
        DB::table('spare_parts')->where('stock', '>', 0)->orderBy('id')->each(function ($part) {
            DB::table('stock_movements')->insert([
                'spare_part_id' => $part->id,
                'type' => 'opening',
                'quantity' => $part->stock,
                'stock_after' => $part->stock,
                'note' => 'Stok awal (dari data lama)',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};