<?php

namespace App\Livewire;

use App\Actions\CreateBooking;
use App\Enums\BookingType;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.booking')]
class BookingForm extends Component
{
    public string $customer_name = '';
    public string $phone = '';
    public string $complaint = '';
    public string $scheduled_at = '';

    public string $booking_type = 'workshop';
    public ?float $latitude = null;
    public ?float $longitude = null;
    public string $address = '';

    // Honeypot: manusia tidak melihat field ini, bot mengisinya.
    public string $website = '';

    public bool $submitted = false;
    public ?string $distanceText = null;
    public ?string $distanceError = null;

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
            app(CreateBooking::class)->handle([
                'customer_name' => $this->customer_name,
                'phone' => $this->phone,
                'complaint' => $this->complaint,
                'scheduled_at' => $this->scheduled_at,
                'booking_type' => $this->booking_type,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'address' => $this->address,
            ]);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }

            return;
        }

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

        // Melindungi kuota ORS dari pemanggilan beruntun.
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
            . number_format($distance->officialKm, 1, ',', '.') . ' km'
            . ($distance->estimated ? ' (perkiraan)' : '');
    }

    public function render()
    {
        return view('livewire.booking-form');
    }
}