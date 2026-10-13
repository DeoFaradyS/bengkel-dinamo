<?php

namespace App\Livewire;

use App\Actions\CreateBooking;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\RateLimiter;
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

    // Honeypot: manusia tidak melihat field ini, bot mengisinya.
    public string $website = '';

    public bool $submitted = false;

    public function submit(): void
    {
        $key = 'booking:'.request()->ip();

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

        $this->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:16'],
            'complaint' => ['required', 'string', 'max:1000'],
            'scheduled_at' => ['required', 'date', 'after:now'],
        ], [], [
            'customer_name' => 'Nama',
            'phone' => 'No. HP',
            'complaint' => 'Keluhan',
            'scheduled_at' => 'Jadwal',
        ]);

        try {
            app(CreateBooking::class)->handle([
                'customer_name' => $this->customer_name,
                'phone' => $this->phone,
                'complaint' => $this->complaint,
                'scheduled_at' => $this->scheduled_at,
            ]);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field, $messages[0]);
            }

            return;
        }

        $this->reset(['customer_name', 'phone', 'complaint', 'scheduled_at']);
        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.booking-form');
    }
}