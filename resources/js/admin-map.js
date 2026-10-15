import 'leaflet/dist/leaflet.css';
import { mountPinMap } from './map-pin';

// Dipanggil dari view form admin. Nilai form Filament ada di wire.data.*
window.adminPinMap = (el, wire) => {
    const pin = mountPinMap(el, {
        get: () => ({ lat: parseFloat(wire.data.latitude), lng: parseFloat(wire.data.longitude) }),
        set: (lat, lng) => {
            wire.data.latitude = lat;
            wire.data.longitude = lng;
            wire.$refresh();
        },
    });

    pin.sync();
    wire.$watch('data.latitude', pin.sync);
    wire.$watch('data.longitude', pin.sync);
};

window.dispatchEvent(new Event('admin-map-ready'));