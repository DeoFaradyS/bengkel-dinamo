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
    public string $customer_name = '';

    // null = belum dicari. Array kosong = dicari tapi tidak ketemu.
    public ?array $results = null;

    public function check(): void
    {
        $this->validate([
            'phone' => ['required', 'string', 'max:20'],
            'customer_name' => ['required', 'string', 'max:255'],
        ], [], [
            'phone' => 'Nomor HP',
            'customer_name' => 'Nama',
        ]);

        $key = 'cek-booking:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('phone', 'Terlalu banyak percobaan. Coba lagi dalam ' . RateLimiter::availableIn($key) . ' detik.');

            return;
        }

        RateLimiter::hit($key, 60);

        $phone = PhoneNumber::normalize($this->phone);

        // Pesan gagal sama untuk nomor salah dan nama salah, supaya data orang lain tidak bisa ditebak.
        $services = $phone
            ? Service::where('phone', $phone)
                ->whereRaw('LOWER(TRIM(customer_name)) = ?', [mb_strtolower(trim($this->customer_name))])
                ->orderByDesc('scheduled_at')
                ->limit(3)
                ->get()
            : collect();

        $this->results = $services->map(fn (Service $s) => [
            'status' => $s->status->value,
            'status_label' => self::statusLabel($s->status->value),
            'type' => $s->booking_type === BookingType::HomeService ? 'Home Service' : 'Ke Bengkel',
            'scheduled_at' => $s->scheduled_at?->translatedFormat('d F Y, H.i'),
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