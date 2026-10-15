import 'leaflet/dist/leaflet.css';
import { mountPinMap } from './map-pin';

// Dipanggil dari Blade: x-init="bookingMap($el, $wire)"
window.bookingMap = (el, wire) => {
    const pin = mountPinMap(el, {
        center: [parseFloat(el.dataset.centerLat), parseFloat(el.dataset.centerLng)],
        get: () => ({ lat: parseFloat(wire.latitude), lng: parseFloat(wire.longitude) }),
        set: (lat, lng) => {
            wire.latitude = lat;
            wire.longitude = lng;
        },
    });

    // Pelanggan pindah tab lalu balik: pin lama dipulihkan.
    pin.sync();
};