<?php

namespace App\Livewire;

use App\Actions\CancelBookingByCustomer;
use App\Enums\BookingType;
use App\Enums\ServiceStatus;
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
            'phone' => ['required', 'string', 'max:20', PhoneNumber::rule()],
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

        $this->results = $this->toResults($this->findMatches());
    }

    // Pelanggan membatalkan booking yang sedang tampil. Kode dan HP dicocokkan ulang
    // di server dan ikut batas percobaan, jadi tidak bisa dipakai menebak booking orang lain.
    public function batalkan(string $kode, ?string $alasan = null): void
    {
        $key = 'cek-booking:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('phone', 'Terlalu banyak percobaan. Coba lagi dalam ' . RateLimiter::availableIn($key) . ' detik.');

            return;
        }

        RateLimiter::hit($key, 60);

        $service = $this->findMatches()->firstWhere('booking_code', $kode);

        if ($service !== null) {
            app(CancelBookingByCustomer::class)->handle($service, $alasan);
        }

        $this->results = $this->toResults($this->findMatches());
    }

    /** @return \Illuminate\Support\Collection<int, Service> */
    private function findMatches()
    {
        $phone = PhoneNumber::normalize($this->phone);

        // Spasi dan tanda strip diabaikan, jadi "482 915" sama dengan "482915".
        $code = preg_replace('/[\s-]+/', '', $this->booking_code);

        // Kode dan nomor HP harus cocok dua-duanya. Pesan gagal sama untuk kode salah
        // dan nomor salah, supaya booking orang lain tidak bisa ditebak.
        return $phone && $code !== ''
            ? Service::where('booking_code', $code)
                ->where('customer_phone', $phone)
                ->get()
            : collect();
    }

    private function toResults($services): array
    {
        return $services->map(fn (Service $s) => [
            'booking_code' => $s->booking_code,
            'status' => $s->status->value,
            'status_label' => self::statusLabel($s->status->value),
            'type' => $s->booking_type->getLabel(),
            'scheduled_at' => $s->scheduled_at?->translatedFormat('l, j F Y · H.i'),
            'complaint' => $s->complaint,
            // D3 (17 Nov 2026): alasan tampil hanya untuk booking yang dibatalkan atau ditolak.
            'cancel_reason' => in_array($s->status, [ServiceStatus::Cancelled, ServiceStatus::Rejected], true)
                ? $s->cancel_reason
                : null,
            // Nomor antrean hari ini (aturan 22), hanya untuk booking yang menunggu dikerjakan atau sedang dikerjakan.
            'queue' => in_array($s->status, [ServiceStatus::Confirmed, ServiceStatus::InProgress], true)
                ? $s->queueInfo()
                : null,
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