<?php

use App\Enums\BookingType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            // NULL = tamu atau walk-in. Terisi = booking dari akun customer.
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Snapshot data pelanggan: dipakai tamu dan Cek Status Booking.
            $table->string('customer_name');
            $table->string('customer_phone', 16);

            $table->text('complaint');
            $table->dateTime('scheduled_at')->index();
            $table->string('status')->default('pending');

            $table->string('booking_type', 20)
                ->default(BookingType::Workshop->value);

            // Hanya home service.
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('address')->nullable();
            $table->decimal('distance_km', 8, 3)->nullable();

            // Snapshot ongkos saat booking dibuat.
            $table->decimal('fuel_price', 12, 2)->nullable();
            $table->decimal('transport_fee', 12, 2)->default(0);

            $table->text('cancel_reason')->nullable();
            // Penjaga: stok dikurangi sekali saja saat servis selesai.
            $table->timestamp('stock_synced_at')->nullable();

            // Catatan bebas siapa yang mengerjakan. Bukan relasi.
            $table->string('mechanic_names')->nullable();
            // NULL = belum lunas. Hanya bermakna untuk servis selesai. Pembayaran tunai.
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'scheduled_at']);
            $table->index(['user_id', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};