<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\RiwayatController;
use App\Models\ServiceType;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome', [
        // Maksimal 6 jasa untuk beranda, sisanya lewat /layanan.
        'services' => ServiceType::orderBy('id')->limit(6)->get(),
        'totalCount' => ServiceType::count(),
    ]);
});

Route::get('/layanan', function () {
    return view('layanan.index', [
        'services' => ServiceType::orderBy('id')->paginate(9),
    ]);
});

// Detail pakai id. Slug butuh kolom baru lewat migrasi, tanya dulu kalau mau.
Route::get('/layanan/{serviceType}', function (ServiceType $serviceType) {
    return view('layanan.show', ['service' => $serviceType]);
});

Route::middleware('guest')->group(function () {
    Route::get('/daftar', [RegisterController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/masuk', [LoginController::class, 'create'])->name('login');
    Route::post('/masuk', [LoginController::class, 'store'])->middleware('throttle:5,1');
});

Route::post('/keluar', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/riwayat', RiwayatController::class)->middleware('auth')->name('riwayat');
