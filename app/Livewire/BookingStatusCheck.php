<?php

namespace App\Livewire;

use App\Enums\BookingType;
use App\Models\Service;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class BookingStatusCheck extends Component
{
    public string $phone = '';
    public string $booking_code = '';

    // null = belum dicari. Array kosong = dicari tapi tidak ketemu.
    public ?array $results = null;

    public function check(): void
    {
        $this->validate([
            'phone' => ['required', 'string', 'max:20'],
            'booking_code' => ['required', 'string', 'max:20'],
        ], [], [
            'phone' => 'Nomor HP',
            'booking_code' => 'Kode booking',
        ]);

        $key = 'cek-booking:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('phone', 'Terlalu banyak percobaan. Coba lagi dalam ' . RateLimiter::availableIn($key) . ' detik.');

            return;
        }

        RateLimiter::hit($key, 60);

        $phone = PhoneNumber::normalize($this->phone);

        // Spasi dan tanda strip diabaikan, jadi "482 915" sama dengan "482915".
        $code = preg_replace('/[\s-]+/', '', $this->booking_code);

        // Kode dan nomor HP harus cocok dua-duanya. Pesan gagal sama untuk kode salah
        // dan nomor salah, supaya booking orang lain tidak bisa ditebak.
        $services = $phone
            ? Service::where('booking_code', $code)
                ->where('customer_phone', $phone)
                ->get()
            : collect();

        $this->results = $services->map(fn (Service $s) => [
            'booking_code' => $s->booking_code,
            'status' => $s->status->value,
            'status_label' => self::statusLabel($s->status->value),
            'type' => $s->booking_type->getLabel(),
            'scheduled_at' => $s->scheduled_at?->translatedFormat('l, j F Y · H.i'),
            'complaint' => $s->complaint,
        ])->all();
    }

    private static function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Menunggu konfirmasi',
            'confirmed' => 'Dikonfirmasi',
            'in_progress' => 'Sedang dikerjakan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'rejected' => 'Ditolak',
            'expired' => 'Kedaluwarsa',
            default => $status,
        };
    }

    public function render()
    {
        return view('livewire.booking-status-check');
    }
}