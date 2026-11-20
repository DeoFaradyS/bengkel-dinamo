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
use App\Models\WorkshopSetting;
use App\Support\Distance\DistanceResult;
use App\Support\Distance\DistanceService;
use App\Support\Booking\Slots;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Enums\UserRole;

class CreateBooking
{
    // Satu nomor HP hanya boleh punya satu booking aktif (Menunggu, Dikonfirmasi, Dikerjakan).
    public const ACTIVE_BOOKING_MESSAGE = 'Nomor ini masih punya booking yang belum selesai. Cek statusnya di Cek Booking.';

    /**
     * @param  array{customer_name: string, phone: string, complaint: string, scheduled_at: mixed, booking_type?: BookingType|string|null, latitude?: mixed, longitude?: mixed, user_id?: int|null}  $data
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

        // Dihitung di luar transaksi.
        $distance = $type === BookingType::HomeService
            ? $this->measureDistance($latitude, $longitude)
            : null;

        if ($distance !== null) {
            $this->assertWithinRange($distance);
        }

        $slot = $this->resolveSlot($data['scheduled_at']);

        $phone = PhoneNumber::normalize($data['phone']);

        try {
            $service = Cache::lock('booking-slot:' . $slot->timestamp, 10)->block(5, function () use ($data, $phone, $type, $latitude, $longitude, $address, $distance, $slot) {
                return DB::transaction(function () use ($data, $phone, $type, $latitude, $longitude, $address, $distance, $slot) {
                    $hasActive = Service::query()
                        ->where('customer_phone', $phone)
                        ->whereIn('status', ServiceStatus::activeValues())
                        ->lockForUpdate()
                        ->exists();

                    if ($hasActive) {
                        throw ValidationException::withMessages([
                            'phone' => self::ACTIVE_BOOKING_MESSAGE,
                        ]);
                    }

                    if (!Slots::isAvailable($slot)) {
                        throw ValidationException::withMessages([
                            'scheduled_at' => 'Jam ini sudah terisi. Pilih jam lain.',
                        ]);
                    }

                    return Service::create([
                        'customer_name' => $data['customer_name'],
                        'customer_phone' => $phone,
                        'complaint' => $data['complaint'],
                        'scheduled_at' => $slot,
                        'status' => ServiceStatus::Pending,
                        'booking_type' => $type,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'address_note' => $address,
                        'user_id' => $data['user_id'] ?? null,
                        'distance_km' => $distance?->airKm,
                    ]);
                });
            });
        } catch (LockTimeoutException) {
            throw ValidationException::withMessages([
                'scheduled_at' => 'Sistem sedang sibuk, coba lagi sebentar.',
            ]);
        }

        Notification::make()
            ->title('Booking baru')
            ->body("{$service->customer_name} ({$service->customer_phone}) booking {$type->getLabel()}, menunggu konfirmasi." . ($address ? " Alamat: {$address}." : ''))
            ->icon('heroicon-o-clock')
            ->warning()
            ->actions([\App\Support\OwnerNotification::openService($service)])
            ->sendToDatabase(User::where('role', UserRole::Owner->value)->get());

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

    private function resolveSlot(mixed $value): Carbon
    {
        try {
            $slot = Carbon::parse($value);
        } catch (\Throwable) {
            throw ValidationException::withMessages(['scheduled_at' => 'Jadwal tidak valid.']);
        }

        if (!Slots::isSlot($slot)) {
            throw ValidationException::withMessages(['scheduled_at' => 'Pilih salah satu jam yang tersedia.']);
        }

        $earliest = now()->addMinutes((int) config('booking.min_lead_minutes'));
        $latest = now()->addDays((int) config('booking.max_days_ahead'))->endOfDay();

        if ($slot->lt($earliest) || $slot->gt($latest)) {
            throw ValidationException::withMessages(['scheduled_at' => 'Jadwal di luar waktu yang bisa dipesan.']);
        }

        return $slot;
    }

    public function measureDistance(float $latitude, float $longitude): ?DistanceResult
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
        );
    }

    public function rangeError(DistanceResult $distance): ?string
    {
        $max = (float) WorkshopSetting::first()?->max_distance_km;

        if ($max <= 0 || $distance->airKm <= $max) {
            return null;
        }

        return sprintf(
            'Lokasi di luar jangkauan. Jarak sekitar %s km, maksimal %s km.',
            number_format($distance->airKm, 1, ',', '.'),
            number_format($max, 1, ',', '.'),
        );
    }

    private function assertWithinRange(DistanceResult $distance): void
    {
        $message = $this->rangeError($distance);

        if ($message !== null) {
            throw ValidationException::withMessages(['latitude' => $message]);
        }
    }
}