import "leaflet/dist/leaflet.css";
import { mountPinMap } from "./map-pin";
import { Calendar } from "vanilla-calendar-pro";
import "vanilla-calendar-pro/styles/index.css";

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
};

// Dipanggil dari Blade: x-init="bookingDate($el, $wire)"
window.bookingDate = (el, wire) => {
    const format = new Intl.DateTimeFormat("id-ID", { dateStyle: "full" });
    const tampil = (ymd) => format.format(new Date(ymd + "T00:00:00"));

    const cal = new Calendar("#" + el.id, {
        inputMode: true,
        locale: "id-ID",
        selectedTheme: "light",
        selectionDatesMode: "single",
        selectedDates: [el.dataset.value],
        dateMin: el.dataset.min,
        dateMax: el.dataset.max,
        onChangeToInput(self) {
            const ymd = self.context.selectedDates[0];

            if (!ymd) return;

            el.value = tampil(ymd);
            wire.$set("slot_date", ymd);
        },
    });

    cal.init();
    el.value = tampil(el.dataset.value);
};
