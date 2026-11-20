<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Catatan pesan WhatsApp otomatis ke pelanggan (aturan 27): terkirim atau gagal, untuk dilihat admin.
return new class extends Migration {
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('event', 20);      // status yang memicu: confirmed, completed, cancelled, rejected
            $table->string('phone', 20);
            $table->text('message');
            $table->string('sender', 10);     // fonnte atau log
            $table->boolean('success');
            $table->string('detail')->nullable();
            $table->timestamps();

            $table->index(['service_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
