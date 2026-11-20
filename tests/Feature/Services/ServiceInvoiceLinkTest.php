<?php

use App\Actions\NotifyCustomerWhatsApp;
use App\Enums\ServiceStatus;
use App\Models\Service;

// Link invoice publik bertanda tangan untuk pelanggan lewat WhatsApp (20 Nov 2026, aturan 28).

it('INV-014: link invoice bertanda tangan dibuka tamu tanpa login, hasilnya PDF', function () {
    $s = Service::factory()->create(['status' => ServiceStatus::Completed]);

    $res = $this->get(NotifyCustomerWhatsApp::invoiceLink($s));

    $res->assertOk()->assertHeader('Content-Type', 'application/pdf');
    expect(substr($res->getContent(), 0, 4))->toBe('%PDF');
});

it('INV-015: link tanpa tanda tangan, tanda tangan diubah, atau kedaluwarsa ditolak', function () {
    $s = Service::factory()->create(['status' => ServiceStatus::Completed]);
    $lain = Service::factory()->create(['status' => ServiceStatus::Completed]);
    $link = NotifyCustomerWhatsApp::invoiceLink($s);

    $this->get('/invoice/' . $s->id)->assertForbidden();
    $this->get(str_replace('/invoice/' . $s->id, '/invoice/' . $lain->id, $link))->assertForbidden();

    $this->travel(31)->days();
    $this->get($link)->assertForbidden();
});

it('INV-016: servis yang belum Selesai, link invoice memberi 404', function () {
    $s = Service::factory()->create(['status' => ServiceStatus::InProgress]);

    $this->get(NotifyCustomerWhatsApp::invoiceLink($s))->assertNotFound();
});
