import "leaflet/dist/leaflet.css";
import { mountPinMap } from "./map-pin";

// Dipanggil dari Blade: x-init="bookingMap($el, $wire)"
window.bookingMap = (el, wire) => {
    let timer;
    const pin = mountPinMap(el, {
        center: [
            parseFloat(el.dataset.centerLat),
            parseFloat(el.dataset.centerLng),
        ],
        get: () => ({
            lat: parseFloat(wire.latitude),
            lng: parseFloat(wire.longitude),
        }),
        set: (lat, lng) => {
            wire.latitude = lat;
            wire.longitude = lng;

            // Tunggu pin berhenti bergerak, lalu minta server hitung jarak.
            clearTimeout(timer);
            timer = setTimeout(() => wire.hitungJarak(), 500);
        },
    });

    el.pinMap = pin;

    // Pelanggan pindah tab lalu balik: pin lama dipulihkan.
    pin.sync();

    // Belum ada pin: coba ambil lokasi pelanggan sekarang.
    // Gagal atau ditolak: diam, peta tetap di bengkel.
    if (!Number.isFinite(parseFloat(wire.latitude))) {
        pin.locate({ onStart: () => {}, onDone: () => {}, onError: () => {} });
    }
};
