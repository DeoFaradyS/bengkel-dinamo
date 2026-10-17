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
use App\Enums\DistanceMethod;
use App\Models\WorkshopSetting;
use App\Support\Distance\DistanceResult;
use App\Support\Distance\DistanceService;

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
        $address = null;

        if ($type === BookingType::HomeService) {
            $coordinates = Validator::make($data, [
                'latitude' => ['required', 'numeric', 'between:-90,90'],
                'longitude' => ['required', 'numeric', 'between:-180,180'],
                'address' => ['nullable', 'string', 'max:255'],
            ], [
                'latitude.required' => 'Pilih lokasi di peta.',
                'longitude.required' => 'Pilih lokasi di peta.',
            ])->validate();

            $latitude = (float) $coordinates['latitude'];
            $longitude = (float) $coordinates['longitude'];
            $address = filled($coordinates['address'] ?? null) ? trim($coordinates['address']) : null;
        }

        // Dihitung di luar transaksi: panggilan ORS bisa sampai 5 detik.
        $distance = $type === BookingType::HomeService
            ? $this->measureDistance($latitude, $longitude)
            : null;

        if ($distance !== null) {
            $this->assertWithinRange($distance);
        }

        $phone = PhoneNumber::normalize($data['phone']);

        $service = DB::transaction(function () use ($data, $phone, $type, $latitude, $longitude, $address, $distance) {
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
                'address' => $address,
                'distance_air_km' => $distance?->airKm,
                'distance_road_km' => $distance?->roadKm,
                'distance_km' => $distance?->officialKm,
                'distance_method' => $distance?->method,
                'distance_estimated' => $distance?->estimated ?? false,
            ]);
        });

        Notification::make()
            ->title('Booking baru')
            ->body("{$service->customer_name} ({$service->phone}) booking {$type->getLabel()}, menunggu konfirmasi." . ($address ? " Alamat: {$address}." : ''))
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

    private function measureDistance(float $latitude, float $longitude): ?DistanceResult
    {
        $workshop = WorkshopSetting::first();

        // Booking tidak boleh gagal hanya karena data bengkel belum diisi.
        if ($workshop === null) {
            return null;
        }

        return app(DistanceService::class)->measure(
            (float) $workshop->latitude,
            (float) $workshop->longitude,
            $latitude,
            $longitude,
            $workshop->distance_method ?? DistanceMethod::Haversine,
        );
    }

    private function assertWithinRange(DistanceResult $distance): void
    {
        $max = (float) WorkshopSetting::first()?->max_distance_km;

        if ($max <= 0 || $distance->officialKm <= $max) {
            return;
        }

        throw ValidationException::withMessages([
            'latitude' => sprintf(
                'Lokasi di luar jangkauan. Jarak sekitar %s km, maksimal %s km.',
                number_format($distance->officialKm, 1, ',', '.'),
                number_format($max, 1, ',', '.'),
            ),
        ]);
    }
}