# Daftar Tes

Setiap tes memakai ID kasus uji (kode modul + nomor) dan judulnya menjelaskan satu aturan yang dicek. Ringkasan di bawah dibuat dari isi folder 	ests/.

Total tes ber-ID: **408**.

## Ringkasan per modul

| Kode | Jumlah | Mengetes | Berkas |
|---|---|---|---|
| ADM | 4 | (isi manual) | `tests/Feature/Booking/AdminBookingTypeTest.php` |
| ADR | 11 | (isi manual) | `tests/Feature/Booking/AddressTest.php` |
| ANT | 12 | Antrian servis | `tests/Feature/Services/ServiceQueueTest.php` |
| AUTH | 19 | Login, register, logout, tampilan halaman masuk dan daftar | `tests/Feature/Auth/CustomerAuthTest.php` |
| BKA | 7 | Booking untuk customer login (user_id, isi otomatis, HP ke akun) | `tests/Feature/Booking/BookingAccountTest.php` |
| BKD | 3 | (isi manual) | `tests/Feature/Distance/BookingDistanceTest.php` |
| BKG | 15 | Booking pelanggan (formulir, jadwal, aturan nomor aktif) | `tests/Feature/Booking/BookingFormTest.php`, `tests/Feature/Booking/CreateBookingTest.php` |
| BKT | 4 | (isi manual) | `tests/Feature/Booking/BookingTypeTest.php` |
| CMP | 5 | (isi manual) | `tests/Feature/Dashboard/CompositionChartsTest.php` |
| DSH | 19 | (isi manual) | `tests/Feature/Dashboard/DashboardPeriodTest.php` |
| DST | 2 | (isi manual) | `tests/Feature/Distance/DistanceServiceTest.php` |
| DTL | 5 | (isi manual) | `tests/Feature/Services/ServiceDetailTest.php` |
| EKT | 1 | (isi manual) | `tests/Feature/Categories/CategoryResourceTest.php` |
| ESP | 5 | (isi manual) | `tests/Feature/SpareParts/SparePartTest.php` |
| EXP | 5 | (isi manual) | `tests/Feature/Booking/ExpireBookingsTest.php` |
| FLW | 6 | (isi manual) | `tests/Feature/Services/ServiceStatusFlowTest.php` |
| FRM | 13 | (isi manual) | `tests/Feature/Booking/AdminServiceFormMapTest.php`, `tests/Feature/Booking/AdminServiceFormTypeTest.php`, `tests/Feature/Services/ServiceFormTest.php` |
| FSP | 3 | Sparepart | `tests/Feature/SpareParts/SparePartTest.php` |
| HAV | 5 | (isi manual) | `tests/Unit/Distance/HaversineCalculatorTest.php` |
| HKT | 2 | (isi manual) | `tests/Feature/Categories/CategoryResourceTest.php` |
| HPU | 7 | (isi manual) | `tests/Feature/Services/ServicePhoneTest.php` |
| HSP | 2 | (isi manual) | `tests/Feature/SpareParts/SparePartTest.php` |
| INV | 11 | (isi manual) | `tests/Feature/Services/ServiceInvoiceContentTest.php`, `tests/Feature/Services/ServiceInvoicePartTest.php`, `tests/Feature/Services/ServiceInvoiceTest.php` |
| JSA | 10 | Resource Jasa di panel | `tests/Feature/ServiceTypes/ServiceTypeResourceTest.php`, `tests/Feature/ServiceTypes/ServiceTypeSeederTest.php` |
| KAT | 2 | (isi manual) | `tests/Feature/Categories/CategoryResourceTest.php` |
| KNC | 11 | (isi manual) | `tests/Feature/Services/ServiceLockTest.php` |
| KPI | 10 | (isi manual) | `tests/Feature/Dashboard/KpiSummaryTest.php` |
| LIM | 4 | (isi manual) | `tests/Feature/Distance/BookingLimitTest.php` |
| LNS | 10 | Tandai Lunas dan status pembayaran servis | `tests/Feature/Services/MarkServicePaidTest.php` |
| LOC | 11 | Lokasi booking home service | `tests/Feature/Booking/BookingLocateTest.php`, `tests/Feature/Booking/BookingLocationTest.php` |
| LSP | 6 | Sparepart | `tests/Feature/SpareParts/SparePartTest.php` |
| MAP | 4 | (isi manual) | `tests/Feature/Booking/BookingMapTest.php` |
| MET | 12 | (isi manual) | `tests/Feature/Dashboard/ServiceMetricsTest.php` |
| NAV | 3 | (isi manual) | `tests/Feature/Services/ServiceNavigationBadgeTest.php` |
| NTF | 5 | (isi manual) | `tests/Feature/Booking/BookingNotificationTest.php`, `tests/Feature/Booking/BookingNotificationTypeTest.php` |
| PRC | 3 | Harga jasa | `tests/Feature/Services/ServicePriceFormTest.php`, `tests/Feature/ServiceTypes/ServiceTypePriceFormTest.php`, `tests/Feature/SpareParts/SparePartPriceFormTest.php` |
| PRV | 5 | (isi manual) | `tests/Feature/Distance/BookingPreviewTest.php` |
| RSN | 5 | (isi manual) | `tests/Feature/Booking/CancelReasonFormTest.php` |
| RWY | 9 | Riwayat Saya (/riwayat) | `tests/Feature/Riwayat/RiwayatTest.php` |
| SDR | 1 | Seeder | `tests/Feature/Distance/SeederDistanceTest.php` |
| SEC | 3 | Keamanan panel: akses /admin hanya owner, notifikasi hanya ke owner | `tests/Feature/Auth/PanelSecurityTest.php` |
| SED | 3 | Seeder | `tests/Feature/Booking/SeederHomeServiceTest.php`, `tests/Feature/Seeders/SeederTest.php` |
| SET | 2 | (isi manual) | `tests/Feature/Distance/DistanceMethodSettingTest.php` |
| SLB | 5 | (isi manual) | `tests/Feature/Booking/BookingSlotTest.php` |
| SLT | 8 | (isi manual) | `tests/Feature/Booking/SlotsTest.php` |
| SPF | 15 | Spare part di servis | `tests/Feature/Services/ServicePartTest.php` |
| SSP | 3 | Sparepart | `tests/Feature/SpareParts/SparePartTest.php` |
| STK | 17 | (isi manual) | `tests/Feature/Services/ServiceStockFormTest.php`, `tests/Feature/Services/ServiceStockTest.php`, `tests/Feature/SpareParts/StockMovementTest.php` |
| SUS | 5 | (isi manual) | `tests/Feature/Services/ServiceStockUsageTest.php` |
| TIP | 5 | (isi manual) | `tests/Feature/Booking/BookingTypeFormTest.php` |
| TKT | 4 | (isi manual) | `tests/Feature/Categories/CategoryResourceTest.php` |
| TPG | 13 | (isi manual) | `tests/Feature/Settings/WorkshopSettingTest.php` |
| TRD | 2 | (isi manual) | `tests/Feature/Dashboard/TrendChartTest.php` |
| TRN | 8 | (isi manual) | `tests/Feature/Transport/TransportFeeTest.php` |
| TSJ | 18 | (isi manual) | `tests/Feature/Services/ServiceItemTest.php` |
| TSP | 10 | (isi manual) | `tests/Feature/SpareParts/SparePartTest.php` |
| USP | 4 | (isi manual) | `tests/Feature/SpareParts/SparePartTest.php` |
| WAF | 2 | (isi manual) | `tests/Feature/Landing/WhatsAppFloatTest.php` |
| WAL | 4 | (isi manual) | `tests/Unit/WhatsAppLinkTest.php` |

## Rincian per berkas

### `tests/Feature/Auth/CustomerAuthTest.php`

- AUTH-001: register berhasil membuat customer, langsung login, redirect ke beranda
- AUTH-003: kolom role dari request diabaikan (tidak bisa daftar sebagai owner)
- AUTH-004: register ditolak kalau email sudah dipakai
- AUTH-006: register ditolak kalau kata sandi kurang dari 8 karakter
- AUTH-007: percobaan register ke-11 dalam semenit ditolak
- AUTH-008: login customer redirect ke beranda
- AUTH-009: login owner redirect ke /admin
- AUTH-010: kata sandi salah atau email tak dikenal memberi pesan sama
- AUTH-011: input kosong ditolak validasi
- AUTH-012: percobaan login ke-6 dalam semenit ditolak
- AUTH-013: logout mengakhiri sesi dan redirect ke beranda
- AUTH-014: tamu tidak bisa logout (middleware auth)
- AUTH-015: user yang sudah login diarahkan dari /masuk dan /daftar
- AUTH-016: customer tidak bisa membuka /admin
- AUTH-017: halaman /masuk tampil untuk tamu dengan formulir login
- AUTH-018: halaman /daftar tampil untuk tamu dengan semua kolom
- AUTH-019: navbar tamu memuat tautan Masuk dan Daftar, tanpa Keluar
- AUTH-020: navbar customer memuat nama dan tombol Keluar, tanpa tautan Panel
- AUTH-021: halaman /masuk dan /daftar tanpa navbar, dengan tombol kembali ke beranda

### `tests/Feature/Auth/PanelSecurityTest.php`

- SEC-001: customer tidak bisa membuka panel /admin
- SEC-002: owner bisa membuka panel /admin
- SEC-003: notifikasi booking hanya masuk ke owner

### `tests/Feature/Booking/AddressTest.php`

- ADR-001: Home Service, alamat tersimpan
- ADR-002: Home Service tanpa alamat tetap diterima
- ADR-003: Ke Bengkel, alamat diabaikan
- ADR-004: isi lonceng Home Service memuat alamat
- ADR-005: form pelanggan Home Service tanpa alamat tetap terkirim
- ADR-006: form pelanggan Home Service dengan alamat tersimpan
- ADR-007: form admin, alamat tampil hanya untuk Home Service
- ADR-008: form admin Home Service tanpa alamat tetap tersimpan
- ADR-009: form admin Home Service dengan alamat tersimpan
- ADR-010: ubah ke Ke Bengkel, alamat dikosongkan
- ADR-011: tabel tidak punya kolom alamat

### `tests/Feature/Booking/AdminBookingTypeTest.php`

- ADM-001: tabel punya kolom tipe layanan
- ADM-002: badge menampilkan label tipe
- ADM-003: filter tipe memisahkan home service dan workshop
- ADM-004: link Peta di form Edit muncul hanya kalau koordinat ada

### `tests/Feature/Booking/AdminServiceFormMapTest.php`

- FRM-009: Home Service, preview peta tampil
- FRM-010: Ke Bengkel, preview peta tidak ada

### `tests/Feature/Booking/AdminServiceFormTypeTest.php`

- FRM-001: form punya pilihan tipe layanan
- FRM-002: Ke Bengkel, isian koordinat tersembunyi
- FRM-003: Home Service, isian koordinat tampil
- FRM-004: Home Service tanpa koordinat ditolak
- FRM-005: koordinat di luar rentang ditolak
- FRM-006: Home Service dengan koordinat tersimpan
- FRM-007: Ke Bengkel tersimpan tanpa koordinat
- FRM-008: ubah Home Service jadi Ke Bengkel, koordinat dikosongkan

### `tests/Feature/Booking/BookingAccountTest.php`

- BKA-001: customer login, form terisi nama dan HP dari akun
- BKA-002: booking customer login menyimpan user_id, nama, dan HP di services
- BKA-003: HP akun kosong, tersimpan ke akun setelah booking pertama
- BKA-004: HP akun sudah ada, tidak ditimpa meski booking pakai HP lain
- BKA-005: tamu, form kosong dan user_id NULL
- BKA-006: customer login boleh mengganti nama (booking untuk orang lain), user_id tetap akun
- BKA-007: booking ditolak validasi, HP akun tidak berubah

### `tests/Feature/Booking/BookingFormTest.php`

- BKG-010: halaman booking terbuka tanpa login
- BKG-011: kirim booking valid, tersimpan Menunggu
- BKG-012: field wajib kosong ditolak
- BKG-013: HP aktif ditolak, data orang lain tidak bocor
- BKG-014: honeypot terisi, booking tidak disimpan
- BKG-015: lebih dari 5 kiriman per menit ditolak
- BKG-016: memilih slot mengisi jadwal
- BKG-017: slot penuh tidak bisa dipilih
- BKG-018: tanggal kemarin dan terlalu jauh dikoreksi ke batas

### `tests/Feature/Booking/BookingLocateTest.php`

- LOC-001: Home Service, tombol Lokasi saya tampil
- LOC-002: Ke Bengkel, tombol Lokasi saya tidak ada

### `tests/Feature/Booking/BookingLocationTest.php`

- LOC-001: tanpa tipe, booking default workshop
- LOC-002: home service dengan koordinat tersimpan
- LOC-003: home service tanpa koordinat lengkap ditolak
- LOC-004: koordinat di luar jangkauan ditolak
- LOC-005: workshop mengabaikan koordinat yang terkirim
- LOC-010: form default workshop, kirim tanpa koordinat lolos
- LOC-011: form home service dengan koordinat tersimpan
- LOC-012: form home service tanpa koordinat ditolak
- LOC-013: form tolak tipe layanan yang tidak dikenal

### `tests/Feature/Booking/BookingMapTest.php`

- MAP-001: workshop tidak punya wadah peta
- MAP-002: home service punya wadah peta
- MAP-003: wadah peta membawa titik awal bengkel
- MAP-004: placeholder lama sudah hilang

### `tests/Feature/Booking/BookingNotificationTest.php`

- NTF-001: booking masuk, semua owner dapat satu notifikasi
- NTF-002: isi notifikasi memuat nama pelanggan
- NTF-003: booking ditolak karena HP aktif, tidak ada notifikasi

### `tests/Feature/Booking/BookingNotificationTypeTest.php`

- NTF-010: booking home service, isi notifikasi menyebut Home Service
- NTF-011: booking workshop, isi notifikasi menyebut Ke Bengkel

### `tests/Feature/Booking/BookingSlotTest.php`

- SLB-001: booking di slot kosong berhasil
- SLB-002: slot yang sudah terisi ditolak
- SLB-003: servis final melepaskan slot
- SLB-004: jam bukan slot ditolak
- SLB-005: terlalu dekat atau terlalu jauh ditolak

### `tests/Feature/Booking/BookingTypeFormTest.php`

- TIP-001: halaman booking menampilkan dua pilihan tipe
- TIP-002: default workshop, kotak lokasi tersembunyi
- TIP-003: pilih home service, kotak lokasi muncul
- TIP-004: balik ke workshop, kotak lokasi hilang lagi
- TIP-005: home service tanpa lokasi, pesan error tampil di layar

### `tests/Feature/Booking/BookingTypeTest.php`

- BKT-001: booking_type default workshop
- BKT-002: home service menyimpan latitude dan longitude
- BKT-003: workshop boleh tanpa koordinat
- BKT-004: label BookingType

### `tests/Feature/Booking/CancelReasonFormTest.php`

- RSN-001: status aktif, isian alasan tersembunyi
- RSN-002: status Ditolak atau Dibatalkan, isian alasan tampil
- RSN-003: Ditolak tanpa alasan ditolak form
- RSN-004: Dibatalkan tanpa alasan ditolak form
- RSN-005: Ditolak dengan alasan tersimpan

### `tests/Feature/Booking/CreateBookingTest.php`

- BKG-001: booking baru masuk status Menunggu
- BKG-002: HP disimpan dalam format 08...
- BKG-003: HP punya servis aktif ditolak, format apa pun
- BKG-004: HP dengan booking Menunggu juga ditolak
- BKG-005: HP dengan servis Selesai boleh booking lagi
- BKG-006: HP dengan servis yang di-soft-delete boleh booking lagi

### `tests/Feature/Booking/ExpireBookingsTest.php`

- EXP-001: Menunggu lewat 24 jam jadi Kedaluwarsa
- EXP-002: Menunggu yang jam janjinya sudah tiba jadi Kedaluwarsa
- EXP-003: Menunggu yang masih baru dan jadwalnya jauh tidak disentuh
- EXP-004: status selain Menunggu tidak disentuh
- EXP-005: booking kedaluwarsa melepas slotnya

### `tests/Feature/Booking/SeederHomeServiceTest.php`

- SED-001: seeder punya servis Home Service dengan koordinat
- SED-002: servis Ke Bengkel di seeder tanpa koordinat

### `tests/Feature/Booking/SlotsTest.php`

- SLT-001: sehari ada 9 slot, 07.00 sampai 16.00, tanpa jam istirahat
- SLT-002: jam di luar slot ditolak
- SLT-003: jam awal slot sah
- SLT-004: servis aktif menahan slot, servis final tidak
- SLT-005: servis yang sedang diedit tidak menghalangi slotnya sendiri
- SLT-006: slot yang tampil ke pelanggan menghormati jarak minimal dan batas hari
- SLT-007: slot penuh tetap tampil tapi ditandai tidak tersedia
- SLT-008: slot kurang dari 30 menit lagi tetap tampil tapi tidak tersedia

### `tests/Feature/Categories/CategoryResourceTest.php`

- KAT-001: tabel menampilkan daftar kategori
- KAT-002: tidak ada halaman Tambah dan Ubah terpisah, semua lewat modal
- TKT-001: tambah lewat modal dengan semua field valid
- TKT-002: deskripsi dikosongkan tetap tersimpan
- TKT-003: nama kategori dikosongkan ditolak
- TKT-004: spasi di awal dan akhir nama dibuang
- EKT-001: ubah lewat modal dengan nilai valid
- HKT-001: hapus kategori yang belum dipakai
- HKT-002: hapus kategori yang dipakai part tetap berhasil, part jadi tanpa kategori

### `tests/Feature/Dashboard/CompositionChartsTest.php`

- CMP-001: donat status tampil dengan semua label status
- CMP-002: donat tipe layanan tampil dengan kedua label
- CMP-003: jasa terlaris menampilkan nama jasa dari servis selesai
- CMP-004: spare part terlaris menampilkan nama part dari servis selesai
- CMP-005: dashboard memuat keempat grafik komposisi

### `tests/Feature/Dashboard/DashboardPeriodTest.php`

- DSH-001: tanpa filter, bawaannya bulan ini sampai hari ini
- DSH-002: hari ini
- DSH-003: 7 hari terakhir, hari ini ikut dihitung
- DSH-004: bulan ini
- DSH-005: tahun ini, 1 Januari sampai hari ini
- DSH-006: rentang tanggal sendiri
- DSH-007: tanggal terbalik ditukar otomatis
- DSH-008: tanggal sendiri belum lengkap, kembali ke bulan ini
- DSH-009: rentang terlalu panjang dipotong 366 hari
- DSH-010: pilihan tidak dikenal kembali ke bawaan
- DSH-011: periode pembanding menempel di depan dengan panjang sama
- DSH-012: halaman dashboard tampil dengan date picker periode
- DSH-013: label periode untuk pilihan bawaan dan preset
- DSH-014: label rentang sendiri berisi tanggal
- DSH-015: rentang dari date picker, format hari/bulan/tahun
- DSH-016: rentang terbalik ditukar, satu tanggal berarti satu hari
- DSH-017: rentang rusak, kembali ke bulan ini
- DSH-018: rentang yang sama dengan preset memakai nama preset
- DSH-019: daftar rentang untuk date picker memuat empat preset

### `tests/Feature/Dashboard/KpiSummaryTest.php`

- KPI-001: empat kartu dengan urutan tetap
- KPI-002: booking naik dibanding periode lalu
- KPI-003: booking turun dibanding periode lalu
- KPI-004: periode lalu kosong, belum ada pembanding
- KPI-005: dua periode sama-sama kosong, sama dengan periode lalu
- KPI-006: pendapatan dalam rupiah, jasa + spare part, dibanding periode lalu
- KPI-007: menunggu konfirmasi dihitung saat ini, tidak ikut periode
- KPI-008: kartu menunggu konfirmasi tanpa tren dan tanpa keterangan
- KPI-010: widget menampilkan keempat kartu
- KPI-011: dashboard memuat widget KPI

### `tests/Feature/Dashboard/ServiceMetricsTest.php`

- MET-001: booking masuk dihitung dari tanggal dibuat, batas hari ikut
- MET-002: servis selesai hanya status Selesai dengan jadwal di periode
- MET-003: pendapatan = jasa + spare part dari servis Selesai di periode saja
- MET-004: rata-rata nilai servis, nol kalau belum ada yang selesai
- MET-005: tingkat pembatalan = (batal + ditolak) dibagi booking masuk
- MET-006: periode sebelumnya sama panjang dan menempel di depan
- MET-007: tren per hari, satu entri untuk tiap tanggal di periode
- MET-008: komposisi status lengkap, status kosong bernilai nol
- MET-009: komposisi Home Service dan Ke Bengkel
- MET-010: jasa terlaris dari servis Selesai di periode, urut terbanyak
- MET-011: spare part terlaris diurutkan menurut jumlah terpakai
- MET-012: servis yang dihapus tidak ikut dihitung

### `tests/Feature/Dashboard/TrendChartTest.php`

- TRD-001: grafik tampil dengan judul dan dua garis
- TRD-002: dashboard memuat grafik tren

### `tests/Feature/Distance/BookingDistanceTest.php`

- BKD-001: Home Service menyimpan jarak udara
- BKD-002: Ke Bengkel tidak menghitung jarak
- BKD-003: data bengkel belum ada, booking tetap masuk tanpa jarak

### `tests/Feature/Distance/BookingLimitTest.php`

- LIM-001: jarak di bawah batas, booking lolos
- LIM-002: jarak lewat batas, ditolak dan tidak tersimpan
- LIM-003: pesan penolakan memuat jarak dan batas, terpasang di field lokasi
- LIM-004: Ke Bengkel tidak terkena batas jarak

### `tests/Feature/Distance/BookingPreviewTest.php`

- PRV-001: jarak udara tampil
- PRV-002: lewat batas, tampil pesan di luar jangkauan, bukan jarak
- PRV-003: belum pilih lokasi, tidak ada hitungan
- PRV-004: Ke Bengkel tidak menghitung jarak
- PRV-005: data bengkel belum ada, diam saja tanpa error

### `tests/Feature/Distance/DistanceMethodSettingTest.php`

- SET-001: admin ubah jarak maksimal dan harga BBM, tersimpan
- SET-002: jarak maksimal nol ditolak

### `tests/Feature/Distance/DistanceServiceTest.php`

- DST-001: jarak dihitung udara (Haversine)
- DST-002: titik sama, jarak nol

### `tests/Feature/Distance/SeederDistanceTest.php`

- SDR-001: servis Home Service dari seeder punya jarak, Ke Bengkel tidak

### `tests/Feature/Landing/WhatsAppFloatTest.php`

- WAF-001: beranda dan /layanan memuat tombol WA melayang dengan tautan wa.me
- WAF-002: halaman masuk dan daftar tidak memuat tombol WA

### `tests/Feature/Riwayat/RiwayatTest.php`

- RWY-001: tamu diarahkan ke halaman masuk
- RWY-002: customer melihat servis miliknya: status, tipe, dan keluhan
- RWY-003: servis milik user lain dan servis tamu tidak tampil
- RWY-004: alamat, alasan batal, dan harga tidak tampil
- RWY-005: servis yang di-soft-delete tidak tampil
- RWY-006: urutan terbaru dulu berdasarkan jadwal
- RWY-007: paginasi 10 per halaman
- RWY-008: customer tanpa booking melihat pesan kosong
- RWY-009: navbar customer memuat tautan Riwayat Saya, tamu tidak

### `tests/Feature/Seeders/SeederTest.php`

- SED-001: DatabaseSeeder jalan tanpa error dan mengisi data

### `tests/Feature/Services/MarkServicePaidTest.php`

- LNS-001: servis Selesai yang belum lunas ditandai lunas, paid_at terisi
- LNS-002: servis yang belum Selesai ditolak, paid_at tetap NULL
- LNS-003: servis yang sudah lunas tidak bisa ditandai dua kali, paid_at tidak tertimpa
- LNS-004: paid_at tidak bisa diisi lewat mass assignment
- LNS-005: tombol Tandai Lunas tampil untuk servis Selesai yang belum lunas
- LNS-006: tombol Tandai Lunas tidak tampil kalau sudah lunas
- LNS-007: menekan Tandai Lunas di halaman View mengisi paid_at
- LNS-008: halaman View servis Selesai belum lunas menampilkan Belum Lunas, tanpa Dibayar pada
- LNS-009: halaman View servis lunas menampilkan Dibayar pada, tanpa Belum Lunas
- LNS-010: halaman View servis yang belum Selesai tidak menampilkan status pembayaran

### `tests/Feature/Services/ServiceDetailTest.php`

- DTL-001: detail tampil ringkasan Total Servis
- DTL-002: servis Dibatalkan menampilkan alasan
- DTL-003: servis non-batal tidak menampilkan alasan
- DTL-004: Home Service menampilkan alamat dan link peta
- DTL-005: Ke Bengkel tidak menampilkan link peta

### `tests/Feature/Services/ServiceFormTest.php`

- FRM-001: field wajib kosong ditolak
- FRM-002: HP 16 karakter diterima
- FRM-003: HP lebih dari 16 karakter ditolak

### `tests/Feature/Services/ServiceInvoiceContentTest.php`

- INV-011: invoice memuat pelanggan, jasa, part, dan total

### `tests/Feature/Services/ServiceInvoicePartTest.php`

- INV-010: part sudah di-soft-delete, invoicePdf tetap jadi PDF

### `tests/Feature/Services/ServiceInvoiceTest.php`

- INV-001: nomor invoice berformat INV-TANGGAL-ID4DIGIT
- INV-002: invoicePdf menghasilkan file PDF
- INV-003: invoicePdf tanpa jasa dan tanpa part tidak error
- INV-004: halaman Lihat servis Selesai, tombol Invoice tampil
- INV-005: halaman Lihat servis non-Selesai, tombol Invoice tersembunyi
- INV-006: tabel servis Selesai, tombol Invoice tampil
- INV-007: tabel servis aktif, tombol Invoice tersembunyi
- INV-008: tabel servis Dibatalkan, tombol Invoice tersembunyi
- INV-009: klik Invoice di tabel, file PDF terunduh

### `tests/Feature/Services/ServiceItemTest.php`

- TSJ-001: servis dengan dua jasa, dua baris tersimpan
- TSJ-002: pilih jasa, name dan price terisi dari katalog
- TSJ-003: harga diubah admin, baris pakai harga baru, katalog tidak berubah
- TSJ-004a: baris jasa kosong saat status Dikonfirmasi diabaikan
- TSJ-004b: baris jasa kosong saat status Selesai ditolak
- TSJ-005: harga kosong ditolak
- TSJ-006: harga bukan angka ditolak
- TSJ-007: harga negatif ditolak
- TSJ-008: harga 0 diterima
- TSJ-009: katalog naik harga, harga di nota lama tetap
- TSJ-010: jasa dihapus dari katalog, baris nota tetap utuh
- TSJ-011: servis di-soft-delete, baris jasa tetap ada
- TSJ-012: edit servis, tambah baris jasa
- TSJ-013: edit servis, hapus satu baris, baris lain aman
- TSJ-014: edit servis, ubah harga satu baris, baris lain tidak berubah
- TSJ-015: total jasa = jumlah harga semua baris
- TSJ-016: status Dikonfirmasi tanpa jasa tersimpan, total 0
- TSJ-017: status Selesai tanpa jasa ditolak

### `tests/Feature/Services/ServiceLockTest.php`

- KNC-001: status aktif bukan final
- KNC-002: Selesai, Dibatalkan, Ditolak adalah final
- KNC-003: servis aktif boleh diedit
- KNC-004: servis final tidak boleh diedit
- KNC-005: buka halaman Edit servis aktif berhasil
- KNC-006: buka halaman Edit servis final ditolak 403
- KNC-007: buka halaman Lihat servis final berhasil
- KNC-008: servis aktif, klik baris ke halaman Edit
- KNC-009: servis final, klik baris ke halaman Lihat
- KNC-010: servis aktif punya tombol Ubah tanpa Lihat
- KNC-011: servis final punya tombol Lihat tanpa Ubah

### `tests/Feature/Services/ServiceNavigationBadgeTest.php`

- NAV-001: tidak ada yang menunggu, badge kosong
- NAV-002: badge menghitung hanya status Menunggu
- NAV-003: servis Menunggu yang di-soft-delete tidak dihitung

### `tests/Feature/Services/ServicePartTest.php`

- SPF-001: servis dengan satu part, baris tersimpan benar
- SPF-002: servis dengan dua part, dua baris tersimpan
- SPF-003: servis tanpa spare part tetap tersimpan
- SPF-004: qty melebihi stok ditolak
- SPF-005: qty sama dengan stok diterima
- SPF-006: qty 0 ditolak
- SPF-007: qty kosong ditolak
- SPF-008: baris part kosong diabaikan, tidak error dan tidak tersimpan
- SPF-009: harga satuan baris baru disalin dari sparepart
- SPF-010: harga satuan baris lama tidak bisa diubah
- SPF-011: part yang sama dua kali di satu servis ditolak
- SPF-012: pilih part, harga satuan terisi dari harga part
- SPF-013: subtotal = qty x harga satuan
- SPF-014: total spare part = jumlah semua subtotal
- SPF-015: grand total = total jasa + total spare part

### `tests/Feature/Services/ServicePhoneTest.php`

- HPU-001: HP sama dengan servis aktif ditolak
- HPU-002: HP sama dengan servis Selesai boleh
- HPU-003: HP sama dengan servis yang di-soft-delete boleh
- HPU-004: edit servis sendiri, HP tidak bentrok dengan dirinya
- HPU-005: edit HP jadi nomor servis aktif lain ditolak
- HPU-006: +62 dianggap sama dengan 08, servis aktif bentrok ditolak
- HPU-007: HP disimpan dalam format 08...

### `tests/Feature/Services/ServicePriceFormTest.php`

- PRC-001: harga jasa tampil bulat saat edit, tidak kali 100

### `tests/Feature/Services/ServiceQueueTest.php`

- ANT-001: tidak ada antrian, servis boleh mulai
- ANT-002: servis confirmed dengan jam lebih awal menghalangi
- ANT-003: servis confirmed dengan jam lebih akhir tidak menghalangi
- ANT-004: booking pending tidak menghalangi
- ANT-005: servis cancelled tidak menghalangi
- ANT-006: masih ada servis in_progress menghalangi
- ANT-007: jam sama, yang dibuat lebih dulu antre lebih dulu
- ANT-008: servis baru (tanpa record) menunggu yang jamnya sama atau lebih awal
- ANT-009: simpan ditolak dengan modal kalau belum giliran
- ANT-010: simpan berhasil kalau sudah giliran
- ANT-011: simpan servis baru tidak menyimpan baris jasa dan part kosong
- ANT-012: modal muncul kalau masih ada servis yang sedang dikerjakan

### `tests/Feature/Services/ServiceStatusFlowTest.php`

- FLW-001: perpindahan status sah lolos
- FLW-002: perpindahan status terlarang ditolak
- FLW-003: servis final tidak bisa diedit
- FLW-004: servis aktif masih bisa diedit
- FLW-005: form Edit cuma tampilkan status sekarang dan yang sah
- FLW-006: form Create menampilkan semua status kecuali Kedaluwarsa

### `tests/Feature/Services/ServiceStockFormTest.php`

- STK-010: stok kurang saat Selesai lewat form, tidak error, status dan stok tetap
- STK-011: edit ke status batal atau tolak, stok tidak berkurang

### `tests/Feature/Services/ServiceStockTest.php`

- STK-001: dua part, stok tiap part berkurang sesuai qty
- STK-002: stock_deducted_at terisi setelah sync
- STK-003: sync dua kali, stok hanya berkurang sekali
- STK-004: stok kurang, error dan semua part batal berkurang
- STK-005: servis tanpa spare part, sync tidak error
- STK-006: buat servis status non-Selesai, stok tidak berkurang
- STK-007: buat servis langsung Selesai, stok langsung berkurang
- STK-008: edit servis aktif jadi Selesai, stok berkurang
- STK-012: edit simpan Selesai tanpa konfirmasi, modal muncul dan stok tidak berubah
- STK-013: buat servis Selesai tanpa konfirmasi, modal muncul dan tidak tersimpan

### `tests/Feature/Services/ServiceStockUsageTest.php`

- SUS-001: servis selesai mencatat pemakaian di riwayat stok
- SUS-002: syncStock dipanggil dua kali, stok tidak berkurang dua kali
- SUS-003: stok kurang, servis gagal dan tidak ada catatan
- SUS-004: servis aktif jadi Selesai lewat halaman Edit, stok turun dan riwayat tercatat
- SUS-005: seeder hanya menandai sinkron servis Selesai

### `tests/Feature/ServiceTypes/ServiceTypePriceFormTest.php`

- PRC-003: harga acuan jenis servis tampil bulat saat edit, tidak kali 100

### `tests/Feature/ServiceTypes/ServiceTypeResourceTest.php`

- JSA-001: tambah jasa dengan deskripsi dan foto
- JSA-002: nama wajib diisi
- JSA-003: harga acuan tidak boleh negatif
- JSA-004: deskripsi dan foto boleh kosong, harga acuan 0
- JSA-005: ubah jasa lewat halaman edit
- JSA-006: photo_url null tanpa foto, terisi kalau ada foto

### `tests/Feature/ServiceTypes/ServiceTypeSeederTest.php`

- JSA-007: seeder mengisi 7 jasa, semuanya punya deskripsi
- JSA-008: seeder dijalankan dua kali tidak menggandakan jasa
- JSA-009: foto yang sudah diunggah tidak tertimpa saat seeder jalan lagi
- JSA-013: setiap foto bawaan di folder seeder terpasang ke jasanya dan berkasnya ada di disk

### `tests/Feature/Settings/WorkshopSettingTest.php`

- TPG-001: halaman terbuka, nilai awal tampil
- TPG-002: data valid disimpan
- TPG-003: jarak maksimal kosong ditolak
- TPG-004: jarak maksimal 0 ditolak
- TPG-005: jarak maksimal desimal (0,5) diterima
- TPG-006: harga BBM kosong ditolak
- TPG-007: harga BBM 0 ditolak
- TPG-008: harga BBM negatif ditolak
- TPG-009: latitude kosong ditolak
- TPG-010: latitude di luar rentang ditolak
- TPG-011: longitude kosong ditolak
- TPG-012: longitude di luar rentang ditolak
- TPG-013: simpan berkali-kali, tabel tetap satu baris

### `tests/Feature/SpareParts/SparePartPriceFormTest.php`

- PRC-002: harga part tampil bulat saat edit, tidak kali 100

### `tests/Feature/SpareParts/SparePartTest.php`

- TSP-001: submit dengan semua field valid
- TSP-002: field opsional dikosongkan tetap tersimpan
- TSP-003: code dikosongkan ditolak
- TSP-004: code duplikat ditolak
- TSP-005: name dikosongkan ditolak
- TSP-006: category_id yang tidak ada ditolak
- TSP-007..013: angka kosong, negatif, atau desimal ditolak
- TSP-014: foto JPG atau PNG lolos
- TSP-015: foto selain JPG atau PNG ditolak
- TSP-016: foto lebih dari 2 MB ditolak
- ESP-001: ubah data dengan valid value
- ESP-002: simpan tanpa ubah code tidak bentrok dengan diri sendiri
- ESP-003: ubah code ke kode part lain ditolak
- ESP-004: name dikosongkan ditolak
- ESP-005: kategori dikosongkan berhasil
- HSP-001: hapus part (soft delete)
- HSP-002: kode part yang sudah dihapus bisa dipakai part baru
- LSP-001: stock di bawah min_stock, baris merah
- LSP-002: stock sama dengan min_stock, baris merah
- LSP-003: stock di atas min_stock, tidak merah
- LSP-004: stok menipis tampil di atas stok aman
- LSP-005: antar yang menipis, selisih stok terhadap min_stock terbesar paling atas
- LSP-006: stok sama dengan min_stock ikut kelompok menipis, di bawah yang lebih kritis
- SSP-001: cari pakai kode
- SSP-002: cari pakai nama
- SSP-003: cari kata yang tidak ada, tabel kosong
- FSP-001: filter 1 kategori
- FSP-002: filter 2 kategori sekaligus
- FSP-003: tanpa filter, semua part tampil
- USP-001: tambah stok dari supplier
- USP-002: kurangi stok karena rusak
- USP-003: kurangi stok melebihi sisa ditolak
- USP-004: alasan Lainnya wajib keterangan

### `tests/Feature/SpareParts/StockMovementTest.php`

- STK-001: part baru dengan stok awal punya catatan stok awal
- STK-002: part baru dengan stok 0 tidak punya catatan
- STK-003: barang masuk menambah stok dan tercatat
- STK-004: koreksi negatif mengurangi stok
- STK-005: koreksi di bawah nol ditolak, stok tidak berubah

### `tests/Feature/Transport/TransportFeeTest.php`

- TRN-001: 5 km pulang-pergi, ongkos Rp2.500
- TRN-002: hasil dibulatkan ke atas per Rp500
- TRN-003: servis di bengkel, ongkos 0
- TRN-004: jarak kosong, ongkos 0
- TRN-005: harga BBM naik, servis lama tetap
- TRN-006: jarak berubah, ongkos dihitung ulang dengan harga BBM terbaru
- TRN-007: pindah ke bengkel lalu balik Home Service, dihitung ulang
- TRN-008: Home Service tanpa lokasi di bengkel, ongkos 0 dan tidak error

### `tests/Unit/Distance/HaversineCalculatorTest.php`

- HAV-001: titik sama, jarak nol
- HAV-002: 1 derajat bujur di ekuator sekitar 111,19 km
- HAV-003: 1 derajat lintang sekitar 111,19 km
- HAV-004: Paris ke Frankfurt sekitar 478 km
- HAV-005: titik A ke B sama dengan B ke A

### `tests/Unit/WhatsAppLinkTest.php`

- WAL-001: nomor 08 diubah ke format internasional
- WAL-002: nomor +62 dan 62 menghasilkan link sama
- WAL-003: pesan di-encode di link
- WAL-004: pesan kosong, link tanpa parameter text
