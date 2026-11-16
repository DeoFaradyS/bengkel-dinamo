# Peta Tes: SIM Bengkel Cak Budi Dinamo

Satu baris per kelompok ID: kode, berkas, dan apa yang dicek. Sumber: nama `test(...)` dan `it(...)` di repo, per 10 Nov 2026 (530 lulus). Kalau menambah tes, tambahkan barisnya di sini. ID baru melanjutkan nomor terakhir di modulnya dan ID lama tidak diganti (aturan di `PROJECT_RULES.md` bagian 10).

Jalankan: `php artisan test`, atau satu modul: `php artisan test --filter=Riwayat`.

Catatan umum:
- `tests/Pest.php` login satu user acak sebelum tiap tes Feature. Tes yang butuh tamu wajib `Auth::logout()` dan `app('auth')->forgetGuards()` di `beforeEach`.
- Tes penerima notifikasi memakai `User::factory()->owner()`.
- Dataset status ada di `tests/Datasets/ServiceStatus.php`.

## Sparepart (`tests/Feature/SpareParts/`)

| Kode | Berkas | Yang dicek | Aturan |
| --- | --- | --- | --- |
| TSP-001..007, 014..016 | SparePartTest | Tambah part: field wajib, kode unik, kategori valid, angka tidak negatif atau desimal, foto JPG/PNG maks 2 MB | US-02 |
| ESP-001..005, ESP-STK | SparePartTest | Ubah part: kode tidak bentrok dengan diri sendiri, kategori boleh kosong, stok tidak bisa diubah lewat form Edit | US-02, aturan 1 |
| HSP-001..002 | SparePartTest | Hapus = soft delete, kode part terhapus bisa dipakai lagi | aturan 9 |
| LSP-001..006 | SparePartTest | Baris merah saat stok <= min_stock, urut paling kritis di atas | US-04 |
| SSP-001..003 | SparePartTest | Cari lewat kode dan nama | US-03 |
| FSP-001..003 | SparePartTest | Filter kategori | US-03 |
| USP-001..004 | SparePartTest | Ubah Stok: tambah, kurangi, tolak melebihi sisa, alasan Lainnya wajib keterangan | aturan 1 |
| PRC-002 | SparePartPriceFormTest | Harga part tampil bulat saat edit (bukan kali 100) | |
| STK-001..005 | StockMovementTest | Catatan stok awal, barang masuk, koreksi negatif, koreksi di bawah nol ditolak | aturan 1 |

## Servis (`tests/Feature/Services/`)

| Kode | Berkas | Yang dicek | Aturan |
| --- | --- | --- | --- |
| FRM-001..003 | ServiceFormTest | Field wajib, HP maks 16 karakter | |
| DTL-001..005 | ServiceDetailTest | Detail: Total Servis, alasan hanya untuk Dibatalkan, alamat dan link peta hanya Home Service | |
| INV-001..011 | ServiceInvoiceTest, ServiceInvoiceContentTest, ServiceInvoicePartTest | Nomor invoice, PDF jadi, tombol Invoice hanya untuk Selesai, part terhapus tetap bisa dicetak | |
| TSJ-001..017 (004 dua kali) | ServiceItemTest | Baris jasa: salin nama dan harga, harga boleh diubah, harga negatif atau kosong ditolak, nota lama tidak ikut perubahan katalog, total jasa | aturan 6, 7 |
| SPF-001..015 | ServicePartTest | Baris part: qty tidak melebihi stok, harga satuan disalin dan terkunci, part ganda ditolak, subtotal dan grand total | aturan 6, 7 |
| HPU-001..007 | ServicePhoneTest | Satu HP satu servis aktif, +62 dan 08 dianggap sama, HP disimpan `08...` | aturan 4 |
| KNC-001..011 | ServiceLockTest | Servis final terkunci: tidak bisa diedit, 403 di Edit, klik baris ke Lihat | aturan 3 |
| FLW-001..006 | ServiceStatusFlowTest | Transisi status sah dan terlarang, pilihan status di form | aturan 3 |
| ANT-001..012 | ServiceQueueTest | Antrian: satu `in_progress`, urutan confirmed, modal blokir | aturan 11 |
| STK-001..008, 012..013 | ServiceStockTest | Stok berkurang saat Selesai, sekali saja, stok kurang membatalkan, modal konfirmasi | aturan 2 |
| STK-010..011 | ServiceStockFormTest | Stok kurang lewat form tidak error, batal atau tolak tidak mengurangi stok | aturan 2 |
| SUS-001..005 | ServiceStockUsageTest | Pemakaian tercatat di riwayat stok, `syncStock` dua kali tidak menggandakan | aturan 1, 2 |
| LNS-001..010 | MarkServicePaidTest | Tandai Lunas: hanya Selesai, tidak dua kali, `paid_at` tidak mass-assignable, tombol, tampilan Lunas dan Belum Lunas | aturan 13 |
| NAV-001..003 | ServiceNavigationBadgeTest | Badge navigasi menghitung status Menunggu, tanpa yang di-soft-delete | |
| PRC-001 | ServicePriceFormTest | Harga jasa tampil bulat saat edit | |

## Booking (`tests/Feature/Booking/`)

| Kode | Berkas | Yang dicek | Aturan |
| --- | --- | --- | --- |
| BKG-001..006 | CreateBookingTest | Booking baru Menunggu, HP `08...`, HP aktif ditolak, HP servis Selesai atau terhapus boleh lagi | aturan 4 |
| BKG-010..018 | BookingFormTest | Formulir tamu: kirim valid, wajib, honeypot, 5 kiriman per menit, pilih slot, koreksi tanggal | |
| BKA-001..007 | BookingAccountTest | Booking customer login: form terisi, `user_id` tersimpan, HP akun tersimpan atau tidak tertimpa, tamu `user_id` NULL | aturan 15 |
| SLB-001..005 | BookingSlotTest | Slot kosong lolos, terisi ditolak, servis final melepas slot | aturan 4 |
| SLT-001..008 | SlotsTest | 9 slot sehari, jam di luar slot, jarak minimal, slot penuh tampil tapi tidak tersedia | |
| EXP-001..005 | ExpireBookingsTest | Menunggu kedaluwarsa, status lain tidak disentuh, slot dilepas | aturan 5 |
| BKT-001..004 | BookingTypeTest | Default workshop, koordinat home service, label | |
| LOC-001..005, 010..013 | BookingLocationTest | Koordinat home service wajib dan di rentang, workshop mengabaikan koordinat, tipe tak dikenal ditolak | |
| LOC-001..002 | BookingLocateTest | Tombol "Pakai lokasi saya" hanya untuk Home Service | |
| MAP-001..004 | BookingMapTest | Wadah peta hanya untuk Home Service, titik awal bengkel | |
| TIP-001..005 | BookingTypeFormTest | Pilihan tipe di formulir, kotak lokasi muncul dan hilang, pesan error lokasi | |
| ADR-001..011 | AddressTest | Patokan alamat Home Service: tersimpan, opsional, diabaikan untuk workshop, tampil di notifikasi dan form admin | |
| ADM-001..004 | AdminBookingTypeTest | Kolom, badge, filter tipe di tabel admin, link Peta | |
| FRM-001..008 | AdminServiceFormTypeTest | Form admin: pilihan tipe, koordinat tampil atau tersembunyi, validasi, dikosongkan saat pindah ke workshop | |
| FRM-009..010 | AdminServiceFormMapTest | Preview peta di form admin hanya untuk Home Service | |
| RSN-001..005 | CancelReasonFormTest | Alasan wajib untuk Ditolak dan Dibatalkan, tersembunyi untuk status aktif | |
| NTF-001..003, 010..011 | BookingNotificationTest, BookingNotificationTypeTest | Notifikasi booking baru ke owner, isi memuat nama dan tipe | aturan 10 |
| SED-001..002 | SeederHomeServiceTest | Seeder punya servis Home Service dengan koordinat | |

## Jarak, ongkos, pengaturan bengkel

| Kode | Berkas | Yang dicek | Aturan |
| --- | --- | --- | --- |
| BKD-001..003 | Distance/BookingDistanceTest | Jarak udara tersimpan untuk Home Service | aturan 8 |
| LIM-001..004 | Distance/BookingLimitTest | Batas jarak maksimal, pesan penolakan | aturan 8 |
| PRV-001..005 | Distance/BookingPreviewTest | Pratinjau jarak di formulir | |
| SET-001..002 | Distance/DistanceMethodSettingTest | Ubah jarak maksimal dan harga BBM | |
| DST-001..002 | Distance/DistanceServiceTest | Jarak Haversine | |
| SDR-001 | Distance/SeederDistanceTest | Seeder Home Service punya jarak | |
| HAV-001..005 | Unit/Distance/HaversineCalculatorTest | Rumus Haversine | |
| TRN-001..008 | Transport/TransportFeeTest | Ongkos PP, pembulatan Rp500, snapshot harga BBM, hitung ulang saat jarak berubah | aturan 8 |
| TPG-001..013 | Settings/WorkshopSettingTest | Halaman pengaturan bengkel: validasi jarak, BBM, koordinat | |

## Dashboard (`tests/Feature/Dashboard/`)

| Kode | Berkas | Yang dicek | Aturan |
| --- | --- | --- | --- |
| DSH-001..019 | DashboardPeriodTest | Filter periode, preset, rentang sendiri, pembanding | US-08 |
| KPI-001..008, 010..011 | KpiSummaryTest | Empat kartu KPI dan tren dibanding periode lalu | US-08 |
| MET-001..012 | ServiceMetricsTest | Hitungan booking, selesai, pendapatan, pembatalan, tren, komposisi, terlaris | US-08 |
| TRD-001..002 | TrendChartTest | Grafik tren | US-08 |
| CMP-001..005 | CompositionChartsTest | Donat status dan tipe, jasa dan part terlaris | US-08 |

## Jasa dan kategori

| Kode | Berkas | Yang dicek | Aturan |
| --- | --- | --- | --- |
| JSA-001..006 | ServiceTypes/ServiceTypeResourceTest | Tambah dan ubah jasa, deskripsi dan foto opsional, `photo_url` | US-07 |
| JSA-007..009, 013 | ServiceTypes/ServiceTypeSeederTest | Seeder 7 jasa berdeskripsi, dua kali jalan tidak menggandakan, foto tidak tertimpa, foto bawaan terpasang | |
| PRC-003 | ServiceTypes/ServiceTypePriceFormTest | Harga acuan tampil bulat saat edit | |
| KAT, TKT, EKT, HKT | Categories/CategoryResourceTest | Daftar, tambah, ubah, hapus kategori lewat modal. Kategori yang dipakai part tetap bisa dihapus | |

## Akun customer dan area customer

| Kode | Berkas | Yang dicek | Aturan |
| --- | --- | --- | --- |
| AUTH-001..021 (tanpa 002 dan 005) | Auth/CustomerAuthTest | Daftar, masuk, keluar, throttle, role tidak bisa dipilih, redirect owner dan customer, halaman dan navbar tamu | aturan 14 |
| SEC-001..003 | Auth/PanelSecurityTest | Customer tidak bisa buka `/admin`, owner bisa, notifikasi hanya ke owner | aturan 10, 14 |
| RWY-001..018 | Riwayat/RiwayatTest | Riwayat milik sendiri, soft delete tersembunyi, urut, paginasi, filter status, detail, 404 milik orang lain, invoice hanya Selesai milik sendiri, owner dan tamu dialihkan | aturan 16 |
| PRF-001..007 | Profile/ProfilTest | Simpan nama dan HP (dinormalkan), HP opsional, email dan role tidak bisa diubah, owner dan tamu dialihkan | aturan 16 |

## Halaman publik dan unit

| Kode | Berkas | Yang dicek | Aturan |
| --- | --- | --- | --- |
| WAF-001..002 | Landing/WhatsAppFloatTest | Tombol WA melayang di beranda dan `/layanan`, tidak di `/masuk` dan `/daftar` | |
| WAL-001..004 | Unit/WhatsAppLinkTest | Tautan wa.me: format internasional, encode pesan | |
| (tanpa ID) | Unit/PhoneNumberTest | `PhoneNumber::normalize` ke `08...`, null dan kosong | |
| SED-001 | Seeders/SeederTest | `DatabaseSeeder` jalan tanpa error | |

## Catatan rapikan

**ID kembar (jangan diganti, dibedakan lewat berkas).** ID baru untuk modul ini harus melanjutkan nomor terbesar dari kedua berkas.
- `LOC-001..002`: BookingLocateTest dan BookingLocationTest.
- `STK-001..005`: ServiceStockTest dan StockMovementTest.
- `FRM-001..003`: ServiceFormTest dan AdminServiceFormTypeTest.
- `SED-001`: SeederHomeServiceTest dan SeederTest.
- `TSJ-004`: dua tes berbeda di ServiceItemTest (satu berkas).

**Judul tes yang sudah usang (assertion benar, judul ketinggalan).**
- RWY-002: judul menyebut keluhan, padahal keluhan pindah ke detail.
- RWY-004: judul "harga tidak tampil", padahal tabel Riwayat sekarang punya kolom Total (D8). Cek isi assertion setelah D8 diputuskan.
- RWY-009: judul menyebut "Riwayat Saya", labelnya sekarang "Riwayat Booking".
- BKG-010 dan TIP-001: judul menyebut "halaman booking", sekarang yang dibuka `/`.

**Belum ada tes (utang, lihat `PLANNING.md` bagian A).**
- Beranda memuat jasa dari DB, `/layanan` (kosong dan paginasi), `/layanan/{id}` dan 404-nya, `/booking` 404, tombol arah di peta.
- Cek Booking (`BookingStatusCheck`, aturan 12): tidak ada berkas tesnya sama sekali.
- Tabel Jasa (JSA-010..012), hapus jasa (D1).
- Riwayat: filter Tipe dan Pembayaran, urut Total, per halaman 25 dan 50, rincian dan total di detail.
- Login satu pintu: tamu ke `/admin` diarahkan ke `/masuk`, `/admin/login` 404.
- Dropdown navbar owner dan customer.
- `RestoreAction` dan `TrashedFilter` sparepart.