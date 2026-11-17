<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('spare_part_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            // Snapshot dari spare_parts.price saat part dipasang. Tidak bisa diubah.
            $table->decimal('unit_price', 12, 2);
            $table->timestamps();

            $table->unique(['service_id', 'spare_part_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_parts');
    }
};