<?php

use App\Enums\ServiceStatus;
use App\Models\Service;
use App\Models\ServiceType;
use App\Models\WhatsappMessage;
use App\Support\WhatsApp\FonnteSender;
use App\Support\WhatsApp\LogSender;
use App\Support\WhatsApp\WhatsAppSender;
use Illuminate\Support\Facades\Http;

// WhatsApp otomatis ke pelanggan (20 Nov 2026, aturan 27). Kode modul: WAO.
// Pengirim palsu dipasang di sini, jadi tidak ada WA sungguhan yang terkirim.

class PengirimPalsu implements WhatsAppSender
{
    public array $terkirim = [];

    public function __construct(public bool $berhasil = true)
    {
    }

    public function send(string $phone, string $message): array
    {
        $this->terkirim[] = ['phone' => $phone, 'message' => $message];

        return ['ok' => $this->berhasil, 'detail' => $this->berhasil ? 'success! message in queue' : 'insufficient quota'];
    }

    public function name(): string
    {
        return 'fonnte';
    }
}

function pasangPengirim(bool $berhasil = true): PengirimPalsu
{
    $palsu = new PengirimPalsu($berhasil);
    app()->instance(WhatsAppSender::class, $palsu);

    return $palsu;
}

function waServis(array $o = []): Service
{
    return Service::factory()->create(array_merge([
        'customer_name' => 'Bu Sari',
        'customer_phone' => '081234500055',
        'status' => ServiceStatus::Pending,
        'scheduled_at' => '2026-11-24 09:00:00',
    ], $o));
}

test('WAO-001: booking dikonfirmasi, pelanggan dapat WA berisi kode dan jadwal', function () {
    $wa = pasangPengirim();
    $s = waServis();

    $s->update(['status' => ServiceStatus::Confirmed]);

    $kode = substr($s->booking_code, 0, 3) . ' ' . substr($s->booking_code, 3);

    expect($wa->terkirim)->toHaveCount(1)
        ->and($wa->terkirim[0]['phone'])->toBe('081234500055')
        ->and($wa->terkirim[0]['message'])->toContain('Halo Bapak/Ibu Bu Sari')
        ->toContain($kode)
        ->toContain('Selasa, 24 November 2026 pukul 09.00');
});

test('WAO-002: servis selesai, pesan memuat total biaya', function () {
    $wa = pasangPengirim();
    $s = waServis(['status' => ServiceStatus::InProgress]);
    $s->items()->create(['service_type_id' => ServiceType::factory()->create()->id, 'name' => 'Servis dinamo', 'price' => 150000]);

    $s->update(['status' => ServiceStatus::Completed]);

    expect($wa->terkirim[0]['message'])->toContain('sudah *selesai*')->toContain('Rp 150.000');
});

test('WAO-003: ditolak atau dibatalkan bengkel, pesan memuat alasan', function (ServiceStatus $status) {
    $wa = pasangPengirim();
    $s = waServis(['status' => $status === ServiceStatus::Rejected ? ServiceStatus::Pending : ServiceStatus::Confirmed]);

    $s->update(['status' => $status, 'cancel_reason' => 'Jadwal montir penuh']);

    expect($wa->terkirim[0]['message'])->toContain('*Alasan:* Jadwal montir penuh');
})->with([
    'Ditolak' => ServiceStatus::Rejected,
    'Dibatalkan' => ServiceStatus::Cancelled,
]);

test('WAO-004: status lain (Dikerjakan) dan perubahan selain status tidak mengirim WA', function () {
    $wa = pasangPengirim();
    $s = waServis(['status' => ServiceStatus::Confirmed]);

    $s->update(['status' => ServiceStatus::InProgress]);
    $s->update(['mechanic_names' => 'Cak Budi']);

    expect($wa->terkirim)->toBe([]);
});

test('WAO-005: pelanggan membatalkan sendiri, tidak dikirimi WA', function () {
    $wa = pasangPengirim();
    $s = waServis();

    app(\App\Actions\CancelBookingByCustomer::class)->handle($s, 'Ganti jadwal');

    expect($s->fresh()->status)->toBe(ServiceStatus::Cancelled)
        ->and($wa->terkirim)->toBe([]);
});

test('WAO-006: gagal kirim, status tetap tersimpan dan kegagalan dicatat', function () {
    pasangPengirim(berhasil: false);
    $s = waServis();

    $s->update(['status' => ServiceStatus::Confirmed]);

    $catatan = WhatsappMessage::firstOrFail();

    expect($s->fresh()->status)->toBe(ServiceStatus::Confirmed)
        ->and($catatan->success)->toBeFalse()
        ->and($catatan->detail)->toBe('insufficient quota')
        ->and($catatan->event)->toBe(ServiceStatus::Confirmed);
});

test('WAO-007: Fonnte dipanggil ke api.fonnte.com/send dengan token di header Authorization', function () {
    Http::fake(['api.fonnte.com/*' => Http::response(['status' => true, 'detail' => 'success! message in queue'])]);

    $hasil = (new FonnteSender('TOKEN-UJI'))->send('081234500055', 'Halo');

    Http::assertSent(fn ($req) => $req->url() === 'https://api.fonnte.com/send'
        && $req->header('Authorization') === ['TOKEN-UJI']
        && $req['target'] === '081234500055'
        && $req['message'] === 'Halo'
        && $req['countryCode'] === '62');

    expect($hasil)->toBe(['ok' => true, 'detail' => 'success! message in queue']);
});

test('WAO-008: jawaban gagal dari Fonnte terbaca sebagai gagal beserta alasannya', function () {
    Http::fake(['api.fonnte.com/*' => Http::response(['Status' => false, 'reason' => 'token invalid'])]);

    expect((new FonnteSender('SALAH'))->send('081234500055', 'Halo'))
        ->toBe(['ok' => false, 'detail' => 'token invalid']);
});

test('WAO-010: pesan Selesai memuat link invoice yang bisa dibuka', function () {
    $wa = pasangPengirim();
    $s = waServis(['status' => ServiceStatus::InProgress]);

    $s->update(['status' => ServiceStatus::Completed]);

    preg_match('~Invoice \(PDF\): (\S+)~', $wa->terkirim[0]['message'], $m);

    expect($m[1] ?? null)->toContain('/invoice/' . $s->id)->toContain('signature=');
    $this->get($m[1])->assertOk()->assertHeader('Content-Type', 'application/pdf');
});

test('WAO-011: dikonfirmasi Ke Bengkel memuat link Maps bengkel, Panggil Montir memuat alamat pelanggan', function () {
    $wa = pasangPengirim();
    \App\Models\WorkshopSetting::create([
        'latitude' => -7.71,
        'longitude' => 113.07,
        'max_distance_km' => 7,
        'fuel_price' => 10000,
        'maps_url' => 'https://maps.google.com/?q=-7.71,113.07',
    ]);

    waServis(['booking_type' => \App\Enums\BookingType::Workshop])->update(['status' => ServiceStatus::Confirmed]);
    waServis(['customer_phone' => '081234500066', 'booking_type' => \App\Enums\BookingType::HomeService, 'address_note' => 'Jl. Mawar 12'])
        ->update(['status' => ServiceStatus::Confirmed]);

    expect($wa->terkirim[0]['message'])->toContain('Lokasi bengkel: https://maps.google.com/?q=-7.71,113.07')
        ->not->toContain('Montir akan datang')
        ->and($wa->terkirim[1]['message'])->toContain('Montir akan datang ke alamat: Jl. Mawar 12')
        ->not->toContain('Lokasi bengkel');
});

test('WAO-012: pesan Selesai memuat status pembayaran Lunas atau Belum lunas', function () {
    $wa = pasangPengirim();

    waServis(['status' => ServiceStatus::InProgress])->update(['status' => ServiceStatus::Completed]);
    waServis(['customer_phone' => '081234500066', 'status' => ServiceStatus::InProgress, 'paid_at' => now()])->update(['status' => ServiceStatus::Completed]);

    expect($wa->terkirim[0]['message'])->toContain('*Pembayaran:* Belum lunas')
        ->and($wa->terkirim[1]['message'])->toContain('*Pembayaran:* Lunas');
});

test('WAO-009: tanpa token Fonnte, aplikasi memakai mode log', function () {
    config(['services.fonnte.token' => null]);
    expect(app(WhatsAppSender::class))->toBeInstanceOf(LogSender::class);

    config(['services.fonnte.token' => 'ADA']);
    expect(app(WhatsAppSender::class))->toBeInstanceOf(FonnteSender::class);
});
