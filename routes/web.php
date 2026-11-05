<?php

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