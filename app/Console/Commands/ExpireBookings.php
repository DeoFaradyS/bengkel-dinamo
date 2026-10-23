<?php

namespace App\Console\Commands;

use App\Enums\ServiceStatus;
use App\Models\Service;
use Illuminate\Console\Command;

class ExpireBookings extends Command
{
    protected $signature = 'bookings:expire';

    protected $description = 'Ubah booking Menunggu Konfirmasi yang lewat batas waktu menjadi Kedaluwarsa';

    public function handle(): int
    {
        $expired = Service::query()
            ->where('status', ServiceStatus::Pending->value)
            ->where(function ($q) {
                $q->where('created_at', '<=', now()->subHours((int) config('booking.expire_hours')))
                    ->orWhere('scheduled_at', '<=', now()->addMinutes((int) config('booking.expire_before_minutes')));
            })
            ->get();

        // Per model, bukan update massal, supaya event dan observer Service tetap jalan.
        foreach ($expired as $service) {
            $service->forceFill(['status' => ServiceStatus::Expired])->save();
        }

        $this->info('Kedaluwarsa: ' . $expired->count());

        return self::SUCCESS;
    }
}