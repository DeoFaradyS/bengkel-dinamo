<?php

namespace App\Livewire;

use App\Actions\CreateBooking;
use App\Enums\BookingType;
use App\Support\PhoneNumber;
use App\Support\WhatsAppLink;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Support\Booking\Slots;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;


#[Layout('layouts.booking')]
class BookingForm extends Component
{
    public string $customer_name = '';
    public string $phone = '';
    public string $complaint = '';
    public string $scheduled_at = '';
    public string $slot_date = '';
    public string $booking_type = 'workshop';
    public ?float $latitude = null;
    public ?float $longitude = null;
    public string $address = '';

    // Honeypot: manusia tidak melihat field ini, bot mengisinya.
    public string $website = '';

    public bool $submitted = false;

    // Diisi setelah booking terkirim: kode untuk Cek Booking dan link konfirmasi ke WA bengkel.
    public ?string $bookingCode = null;
    public ?string $waLink = null;
    public ?string $distanceText = null;
    public ?string $distanceError = null;

    public function mount(): void
    {
        $this->slot_date = now()->format('Y-m-d');
        $this->fillFromAccount();
    }

    public function updatedSlotDate(): void
    {
        // Ganti tanggal, jam yang dipilih sebelumnya tidak berlaku lagi.
        $this->scheduled_at = '';

        try {
            $date = Carbon::createFromFormat('!Y-m-d', $this->slot_date);
        } catch (\Throwable) {
            return;
        }

        if (!$date) {
            return;
        }

        $min = now()->startOfDay();
        $max = now()->addDays((int) config('booking.max_days_ahead'))->startOfDay();

        if ($date->lt($min)) {
            $this->slot_date = $min->format('Y-m-d');
        } elseif ($date->gt($max)) {
            $this->slot_date = $max->format('Y-m-d');
        }
    }

    public function submit(): void
    {
        $key = 'booking:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('throttle', 'Terlalu banyak percobaan. Coba lagi sebentar.');

            return;
        }

        RateLimiter::hit($key, 60);

        if ($this->website !== '') {
            $this->submitted = true;

            return;
        }

        $this->phone = PhoneNumber::normalize($this->phone) ?? '';

        $rules = [
            'customer_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:16'],
            'complaint' => ['required', 'string', 'max:1000'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'booking_type' => ['required', Rule::enum(BookingType::class)],
        ];

        if ($this->booking_type === BookingType::HomeService->value) {
            $rules['latitude'] = ['required', 'numeric', 'between:-90,90'];
            $rules['longitude'] = ['required', 'numeric', 'between:-180,180'];
            $rules['address'] = ['nullable', 'string', 'max:255'];
        }

        $this->validate($rules, [
            'scheduled_at.required' => 'Pilih tanggal dan jam.',
            'latitude.required' => 'Pilih lokasi di peta.',
            'longitude.required' => 'Pilih lokasi di peta.',
        ], [
            'customer_name' => 'Nama',
            'phone' => 'No. HP',
            'complaint' => 'Keluhan',
            'scheduled_at' => 'Jadwal',
            'booking_type' => 'Tipe layanan',
            'latitude' => 'Lokasi',
            'longitude' => 'Lokasi',
            'address' => 'Alamat',
        ]);

        try {
            $service = app(CreateBooking::class)->handle([
                'customer_name' => $this->customer_name,
                'phone' => $this->phone,
                'complaint' => $this->complaint,
                'scheduled_at' => $this->scheduled_at,
                'booking_type' => $this->booking_type,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'address' => $this->address,
                'user_id' => Auth::id(),
            ]);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }

            return;
        }

        $this->rememberPhone();

        $this->bookingCode = $service->booking_code;
        $this->waLink = WhatsAppLink::make(config('booking.whatsapp'), implode("\n", [
            'Halo Budi Dinamo, saya sudah booking.',
            'Kode booking: ' . $service->booking_code,
            'Nama: ' . $service->customer_name,
            'Jadwal: ' . $service->scheduled_at->translatedFormat('l, j F Y · H.i'),
            'Jenis: ' . $service->booking_type->getLabel(),
            'Mohon dikonfirmasi, terima kasih.',
        ]));

        $this->reset([
            'customer_name',
            'phone',
            'complaint',
            'scheduled_at',
            'booking_type',
            'latitude',
            'longitude',
            'address',
            'distanceText',
            'distanceError',
        ]);
        $this->submitted = true;
    }

    public function hitungJarak(): void
    {
        $this->distanceText = null;
        $this->distanceError = null;

        if (
            $this->booking_type !== BookingType::HomeService->value
            || $this->latitude === null
            || $this->longitude === null
        ) {
            return;
        }

        // Membatasi pemanggilan beruntun.
        $key = 'distance:' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 20)) {
            return;
        }

        RateLimiter::hit($key, 60);

        $action = app(CreateBooking::class);
        $distance = $action->measureDistance($this->latitude, $this->longitude);

        if ($distance === null) {
            return;
        }

        $error = $action->rangeError($distance);

        if ($error !== null) {
            $this->distanceError = $error;

            return;
        }

        $this->distanceText = 'Jarak ke bengkel sekitar '
            . number_format($distance->airKm, 1, ',', '.') . ' km';
    }

    // Customer login: nama dan HP terisi dari akun. Tetap bisa diubah
    // (booking untuk orang lain), nilainya disalin ke services.
    protected function fillFromAccount(): void
    {
        $user = Auth::user();

        if ($user === null) {
            return;
        }

        $this->customer_name = $user->name;
        $this->phone = (string) $user->phone;
    }

    // HP diisi saat booking pertama: simpan ke akun kalau masih kosong.
    protected function rememberPhone(): void
    {
        $user = Auth::user();

        if ($user !== null && blank($user->phone)) {
            $user->forceFill(['phone' => $this->phone])->save();
        }
    }

    public function render()
    {
        return view('livewire.booking-form', ['daftarSlot' => $this->daftarSlot()]);
    }

    public function pilihSlot(string $time): void
    {
        $valid = $this->daftarSlot()->contains(
            fn($s) => $s['available'] && $s['time']->format('Y-m-d H:i') === $time
        );

        if ($valid) {
            $this->scheduled_at = $time;
            $this->resetErrorBag('scheduled_at');
        }
    }
    protected function daftarSlot(): Collection
    {
        try {
            $date = Carbon::createFromFormat('!Y-m-d', $this->slot_date);
        } catch (\Throwable) {
            return collect();
        }

        return $date ? Slots::bookable($date) : collect();
    }
}