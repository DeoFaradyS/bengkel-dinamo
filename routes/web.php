<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\RiwayatController;
use App\Models\ServiceType;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfilController;

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

// Detail jasa pakai slug (D4), contoh /layanan/ganti-bearing. URL lama berbasis id
// (/layanan/3) dialihkan permanen ke slug. Jasa terarsip atau tidak ada: 404.
Route::get('/layanan/{slug}', function (string $slug) {
    $service = ServiceType::where('slug', $slug)->first();

    if ($service === null && ctype_digit($slug) && ($lama = ServiceType::find($slug))) {
        return redirect('/layanan/' . $lama->slug, 301);
    }

    abort_if($service === null, 404);

    return view('layanan.show', ['service' => $service]);
});

Route::middleware('guest')->group(function () {
    Route::get('/daftar', [RegisterController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/masuk', [LoginController::class, 'create'])->name('login');
    Route::post('/masuk', [LoginController::class, 'store'])->middleware('throttle:5,1');
});

Route::post('/keluar', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/riwayat', RiwayatController::class)->middleware('auth')->name('riwayat');
Route::get('/riwayat/{id}', [RiwayatController::class, 'show'])->whereNumber('id')->middleware('auth')->name('riwayat.show');
Route::post('/riwayat/{id}/batal', [RiwayatController::class, 'batal'])->whereNumber('id')->middleware(['auth', 'throttle:10,1'])->name('riwayat.batal');
Route::get('/riwayat/{id}/invoice', [RiwayatController::class, 'invoice'])->whereNumber('id')->middleware('auth')->name('riwayat.invoice');
Route::get('/profil', [ProfilController::class, 'edit'])->middleware('auth')->name('profil');
Route::put('/profil', [ProfilController::class, 'update'])->middleware('auth');