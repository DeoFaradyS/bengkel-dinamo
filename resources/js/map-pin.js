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

// Kotak cari tempat di pojok kanan atas peta (tidak menutupi pin yang ada di tengah). Memakai Nominatim (pencarian alamat OpenStreetMap,
// gratis, tanpa kunci API). Yang dikirim hanya teks pencarian. Cari saat Enter atau tombol Cari
// (bukan tiap ketikan) supaya sesuai aturan pemakaian wajar Nominatim. Hasil dibatasi Indonesia
// dan diutamakan sekitar Pasuruan.
function addSearch(map, onPick) {
    const Search = L.Control.extend({
        options: { position: 'topright' },
        onAdd() {
            const box = L.DomUtil.create('div');
            box.style.cssText = 'width:min(320px, calc(100vw - 6rem));background:#fff;border-radius:10px;box-shadow:0 2px 8px rgba(0,0,0,.15);font:14px/1.4 system-ui,sans-serif;color:#18181b;';
            box.innerHTML = `
                <div style="display:flex;gap:6px;padding:6px;">
                    <input type="search" placeholder="Cari desa, jalan, atau tempat" aria-label="Cari tempat di peta"
                        style="flex:1;min-width:0;padding:8px 10px;border:1px solid #d4d4d8;border-radius:8px;font:inherit;outline:none;">
                    <button type="button" style="padding:8px 12px;border:0;border-radius:8px;background:#dc2626;color:#fff;font:inherit;font-weight:600;cursor:pointer;">Cari</button>
                </div>
                <div data-hasil style="display:none;max-height:220px;overflow-y:auto;border-top:1px solid #f4f4f5;"></div>`;

            L.DomEvent.disableClickPropagation(box);
            L.DomEvent.disableScrollPropagation(box);

            const input = box.querySelector('input');
            const button = box.querySelector('button');
            const hasil = box.querySelector('[data-hasil]');

            const tampil = (html) => {
                hasil.innerHTML = html;
                hasil.style.display = html ? 'block' : 'none';
            };

            const pesan = (teks) => tampil(`<div style="padding:10px 12px;color:#71717a;">${teks}</div>`);

            const cari = async () => {
                const q = input.value.trim();
                if (q.length < 3) {
                    pesan('Ketik minimal 3 huruf.');
                    return;
                }

                pesan('Mencari...');
                const url = (sekitar) => 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=5&countrycodes=id'
                    + '&accept-language=id&viewbox=112.8,-7.55,113.4,-7.95' + (sekitar ? '&bounded=1' : '')
                    + '&q=' + encodeURIComponent(q);
                const ambil = async (sekitar) => {
                    const res = await fetch(url(sekitar), { headers: { Accept: 'application/json' } });
                    return res.ok ? res.json() : [];
                };

                try {
                    // Utamakan sekitar Pasuruan dan Probolinggo; kalau kosong baru cari se-Indonesia.
                    let data = await ambil(true);
                    if (!data.length) {
                        data = await ambil(false);
                    }

                    if (!data.length) {
                        pesan('Tidak ditemukan. Coba nama desa, kecamatan, atau jalan.');
                        return;
                    }

                    tampil('');
                    data.forEach((item) => {
                        const opsi = document.createElement('button');
                        opsi.type = 'button';
                        opsi.textContent = item.display_name;
                        opsi.style.cssText = 'display:block;width:100%;text-align:left;padding:10px 12px;border:0;border-bottom:1px solid #f4f4f5;background:#fff;font:inherit;cursor:pointer;';
                        opsi.onmouseenter = () => (opsi.style.background = '#f4f4f5');
                        opsi.onmouseleave = () => (opsi.style.background = '#fff');
                        // Klik hasil jangan sampai diteruskan ke peta (peta akan menaruh pin di posisi mouse).
                        opsi.addEventListener('click', (e) => {
                            e.stopPropagation();
                            onPick(L.latLng(Number(item.lat), Number(item.lon)));
                            setTimeout(() => tampil(''), 0);
                        });
                        hasil.appendChild(opsi);
                    });
                    hasil.style.display = 'block';
                } catch {
                    pesan('Pencarian gagal. Periksa internet, atau klik peta langsung.');
                }
            };

            button.addEventListener('click', cari);
            // Enter di kotak cari jangan sampai mengirim form booking.
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    cari();
                }
            });

            return box;
        },
    });

    map.addControl(new Search());
}

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

    // Pindah peta dulu tanpa animasi, baru taruh pin. Animasi zoom yang bertabrakan dengan
    // pembaruan form (Livewire) membuat posisi pin meleset.
    addSearch(map, (latlng) => {
        map.setView(latlng, 17, { animate: false });
        place(latlng, true);
    });

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

    // Peta muncul di wadah yang baru tampil (jendela pilih lokasi, bagian form yang baru terbuka),
    // ukurannya perlu dihitung ulang setiap kali ukuran wadah berubah, kalau tidak peta abu-abu.
    setTimeout(() => map.invalidateSize(), 0);
    if (window.ResizeObserver) {
        new ResizeObserver(() => map.invalidateSize()).observe(el);
    }

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