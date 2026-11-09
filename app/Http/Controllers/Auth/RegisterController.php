<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        // Nomor dinormalkan dulu. normalize() mengembalikan '' untuk input
        // tanpa angka, jadi validitas dijaga aturan regex di bawah.
        $request->merge(['phone' => PhoneNumber::normalize((string) $request->input('phone'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'regex:/^0\d{8,14}$/'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'phone.required' => 'Nomor HP tidak valid.',
            'phone.regex' => 'Nomor HP tidak valid.',
        ]);

        // role tidak di $fillable: default DB "customer" yang berlaku.
        $user = User::create($data);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/');
    }
}