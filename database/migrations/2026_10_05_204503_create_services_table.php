<?php

use App\Enums\ServiceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('phone', 16);
            $table->text('complaint');
            $table->dateTime('scheduled_at')->index();
            $table->string('status', 20)
                ->default(ServiceStatus::Pending->value)
                ->index();
            $table->decimal('labor_cost', 12, 2)->default(50000);
            $table->text('cancel_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
