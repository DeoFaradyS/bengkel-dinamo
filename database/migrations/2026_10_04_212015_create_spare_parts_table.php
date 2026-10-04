<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('spare_parts', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('category')->nullable();
    $table->unsignedInteger('stock')->default(0);
    $table->unsignedInteger('min_stock')->default(1);
    $table->string('location')->nullable();
    $table->decimal('price', 12, 2)->default(0);
    $table->timestamps();
    $table->softDeletes();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spare_parts');
    }
};
