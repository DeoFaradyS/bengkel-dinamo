<?php

namespace App\Actions;

use App\Enums\BookingType;
use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Models\User;
use App\Support\PhoneNumber;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateBooking
{
    /**
     * @param  array{customer_name: string, phone: string, complaint: string, scheduled_at: mixed, booking_type?: BookingType|string|null, latitude?: mixed, longitude?: mixed}  $data
     */
    public function handle(array $data): Service
    {
        $type = $this->resolveType($data['booking_type'] ?? BookingType::Workshop);

        $latitude = null;
        $longitude = null;

        if ($type === BookingType::HomeService) {
            $coordinates = Validator::make($data, [
                'latitude' => ['required', 'numeric', 'between:-90,90'],
                'longitude' => ['required', 'numeric', 'between:-180,180'],
            ], [
                'latitude.required' => 'Pilih lokasi di peta.',
                'longitude.required' => 'Pilih lokasi di peta.',
            ])->validate();

            $latitude = (float) $coordinates['latitude'];
            $longitude = (float) $coordinates['longitude'];
        }

        $phone = PhoneNumber::normalize($data['phone']);

        $service = DB::transaction(function () use ($data, $phone, $type, $latitude, $longitude) {
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
                'booking_type' => $type,
                'latitude' => $latitude,
                'longitude' => $longitude,
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

    private function resolveType(BookingType|string|null $value): BookingType
    {
        if ($value instanceof BookingType) {
            return $value;
        }

        $type = BookingType::tryFrom((string) $value);

        if ($type === null) {
            throw ValidationException::withMessages([
                'booking_type' => 'Tipe layanan tidak dikenal.',
            ]);
        }

        return $type;
    }
}