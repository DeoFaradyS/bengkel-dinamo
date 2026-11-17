<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Support\PhoneNumber;

class ProfilController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        // Owner tidak punya area customer, arahkan ke panel.
        if ($request->user()->role === UserRole::Owner) {
            return redirect('/admin');
        }

        return view('profil', ['user' => $request->user()]);
    }

        public function update(Request $request): RedirectResponse
    {
        if ($request->user()->role === UserRole::Owner) {
            return redirect('/admin');
        }

        // Samakan bentuk nomor dengan form booking (0812...). Input sampah tetap dikirim
        // apa adanya supaya regex menolaknya, bukan disimpan sebagai kosong.
        $raw = trim((string) $request->input('phone'));
        $normalized = $raw === '' ? null : PhoneNumber::normalize($raw);
        $request->merge(['phone' => $normalized === '' ? $raw : $normalized]);

        // Email dan role sengaja tidak diterima dari input.
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'regex:/^08[0-9]{8,12}$/'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'phone.regex' => 'Nomor HP tidak valid. Contoh: 081234567890.',
        ]);

        $request->user()->forceFill([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
        ])->save();

        return redirect()->route('profil')->with('status', 'Profil berhasil disimpan.');
    }
}