<?php

namespace App\Actions;

use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Models\User;
use App\Support\PhoneNumber;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateBooking
{
    /**
     * @param  array{customer_name: string, phone: string, complaint: string, scheduled_at: mixed}  $data
     */
    public function handle(array $data): Service
    {
        $phone = PhoneNumber::normalize($data['phone']);

        $service = DB::transaction(function () use ($data, $phone) {
            $hasActive = Service::query()
                ->where('phone', $phone)
                ->whereIn('status', ServiceStatus::activeValues())
                ->lockForUpdate()
                ->exists();

            if ($hasActive) {
                throw ValidationException::withMessages([
                    'phone' => 'Nomor ini masih punya servis yang belum selesai.',
                ]);
            }

            return Service::create([
                'customer_name' => $data['customer_name'],
                'phone' => $phone,
                'complaint' => $data['complaint'],
                'scheduled_at' => $data['scheduled_at'],
                'status' => ServiceStatus::Pending,
            ]);
        });

        Notification::make()
            ->title('Booking baru')
            ->body("{$service->customer_name} ({$service->phone}) menunggu konfirmasi.")
            ->icon('heroicon-o-clock')
            ->warning()
            ->sendToDatabase(User::all());

        return $service;
    }
}