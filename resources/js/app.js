import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Pin SVG digambar di kode, tanpa file gambar.
const pinIcon = L.divIcon({
    className: '',
    iconSize: [32, 42],
    iconAnchor: [16, 42],
    html: `
        <svg width="32" height="42" viewBox="0 0 32 42" xmlns="http://www.w3.org/2000/svg">
            <path d="M16 0C7.2 0 0 7.1 0 15.9 0 27.5 16 42 16 42s16-14.5 16-26.1C32 7.1 24.8 0 16 0z" fill="#dc2626"/>
            <circle cx="16" cy="16" r="6" fill="#ffffff"/>
        </svg>`,
});

// Dipanggil dari Blade: x-init="bookingMap($el, $wire)"
window.bookingMap = (el, wire) => {
    const center = [parseFloat(el.dataset.centerLat), parseFloat(el.dataset.centerLng)];
    const map = L.map(el).setView(center, 15);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    let marker = null;

    const save = (latlng) => {
        wire.latitude = Number(latlng.lat.toFixed(7));
        wire.longitude = Number(latlng.lng.toFixed(7));
    };

    const place = (latlng) => {
        if (marker) {
            marker.setLatLng(latlng);
        } else {
            marker = L.marker(latlng, { icon: pinIcon, draggable: true }).addTo(map);
            marker.on('dragend', () => save(marker.getLatLng()));
        }
        save(latlng);
    };

    map.on('click', (e) => place(e.latlng));

    // Pelanggan pindah tab lalu balik: pin lama dipulihkan.
    if (wire.latitude != null && wire.longitude != null) {
        const saved = L.latLng(wire.latitude, wire.longitude);
        place(saved);
        map.setView(saved, 16);
    }

    // Peta muncul di wadah yang baru tampil, ukurannya perlu dihitung ulang.
    setTimeout(() => map.invalidateSize(), 0);
};