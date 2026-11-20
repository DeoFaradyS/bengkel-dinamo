<?php

use App\Models\ServiceType;

// Slug URL jasa (D4) dan halaman 404 (20 Nov 2026). Kode modul: SLG.

test('SLG-001: jasa baru otomatis punya slug dari nama, dan detailnya terbuka lewat slug', function () {
    $jasa = ServiceType::factory()->create(['name' => 'Ganti Bearing Depan']);

    expect($jasa->slug)->toBe('ganti-bearing-depan');

    $this->get('/layanan/ganti-bearing-depan')->assertOk()->assertSee('Ganti Bearing Depan');
});

test('SLG-002: nama sama dapat slug berbeda, termasuk dengan jasa yang diarsip', function () {
    $lama = ServiceType::factory()->create(['name' => 'Servis Dinamo']);
    $lama->delete();
    $baru = ServiceType::factory()->create(['name' => 'Servis Dinamo']);

    expect($lama->slug)->toBe('servis-dinamo')
        ->and($baru->slug)->toBe('servis-dinamo-2');
});

test('SLG-003: nama diubah, slug ikut berubah', function () {
    $jasa = ServiceType::factory()->create(['name' => 'Ganti Brush']);
    $jasa->update(['name' => 'Ganti Brush Karbon']);

    expect($jasa->fresh()->slug)->toBe('ganti-brush-karbon');
});

test('SLG-004: URL lama berbasis id dialihkan permanen ke slug', function () {
    $jasa = ServiceType::factory()->create(['name' => 'Rewinding Kumparan']);

    $this->get('/layanan/' . $jasa->id)->assertRedirect('/layanan/rewinding-kumparan')->assertStatus(301);
});

test('SLG-005: kartu jasa di beranda dan /layanan menaut ke slug', function () {
    ServiceType::factory()->create(['name' => 'Ganti Bendix']);

    $this->get('/')->assertSee('/layanan/ganti-bendix', false);
    $this->get('/layanan')->assertSee('/layanan/ganti-bendix', false);
});

test('SLG-006: halaman yang tidak ada menampilkan 404 bergaya landing dengan tombol ke beranda', function () {
    $this->get('/layanan/tidak-ada-jasa-ini')
        ->assertNotFound()
        ->assertSee('Halaman tidak ditemukan')
        ->assertSee('Kembali ke Beranda');

    $this->get('/halaman-ngawur')->assertNotFound()->assertSee('Halaman tidak ditemukan');
});
