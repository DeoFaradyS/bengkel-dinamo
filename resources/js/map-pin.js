import L from 'leaflet';

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

// get() -> { lat, lng } (angka atau NaN). set(lat, lng) dipanggil saat pin berubah.
export function mountPinMap(el, { get, set, center = [-7.7174465, 113.0755426], zoom = 15 }) {
    const map = L.map(el).setView(center, zoom);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    let marker = null;

    const place = (latlng, notify) => {
        if (marker) {
            marker.setLatLng(latlng);
        } else {
            marker = L.marker(latlng, { icon: pinIcon, draggable: true }).addTo(map);
            marker.on('dragend', () => place(marker.getLatLng(), true));
        }
        if (notify) {
            set(Number(latlng.lat.toFixed(7)), Number(latlng.lng.toFixed(7)));
        }
    };

    map.on('click', (e) => place(e.latlng, true));

    // Baca nilai dari form, pindahkan pin kalau beda.
    const sync = () => {
        const { lat, lng } = get();
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
            return;
        }
        if (marker) {
            const now = marker.getLatLng();
            if (now.lat.toFixed(7) === lat.toFixed(7) && now.lng.toFixed(7) === lng.toFixed(7)) {
                return;
            }
        }
        const latlng = L.latLng(lat, lng);
        place(latlng, false);
        map.setView(latlng, 16);
    };

    // Peta muncul di wadah yang baru tampil, ukurannya perlu dihitung ulang.
    setTimeout(() => map.invalidateSize(), 0);

    // Ambil posisi perangkat, pindahkan pin. Pesan error dikirim ke onError.
    const locate = ({ onStart, onDone, onError }) => {
        if (!window.isSecureContext || !navigator.geolocation) {
            onError('Lokasi butuh HTTPS. Ketuk peta untuk pilih manual.');
            return;
        }
        onStart();
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const latlng = L.latLng(pos.coords.latitude, pos.coords.longitude);
                place(latlng, true);
                map.setView(latlng, 17);
                onDone();
            },
            (err) => {
                onDone();
                onError(err.code === 1
                    ? 'Izin lokasi ditolak. Ketuk peta untuk pilih manual.'
                    : 'Lokasi tidak ditemukan. Ketuk peta untuk pilih manual.');
            },
            { enableHighAccuracy: true, timeout: 10000 },
        );
    };

    return { map, sync, locate };
}