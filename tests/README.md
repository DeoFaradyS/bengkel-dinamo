# Peta Tes: SIM Bengkel Cak Budi Dinamo

Satu baris per kelompok ID: kode, berkas, dan apa yang dicek. Sumber: nama `test(...)` dan `it(...)` di repo, per 10 Nov 2026 (660 lulus per 26 Nov). Kalau menambah tes, tambahkan barisnya di sini. ID baru melanjutkan nomor terakhir di modulnya dan ID lama tidak diganti (aturan di `PROJECT_RULES.md` bagian 10).

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
| UNT-001..006 | StockUnitTest | Satuan dan jumlah desimal: kg berkurang pecahan dan tercatat di riwayat, pcs menolak pecahan, batas stok kg tidak di bawah 0, servis 0,35 kg (subtotal dan potong stok), form servis menolak pecahan untuk pcs, format angka Indonesia dengan satuan | aturan 33 |

## Servis (`tests/Feature/Services/`)

| Kode | Berkas | Yang dicek | Aturan |
| --- | --- | --- | --- |
| FRM-001..003 | ServiceFormTest | Field wajib, HP maks 16 karakter | |
| DTL-001..005 | ServiceDetailTest | Detail: Total Servis, alasan hanya untuk Dibatalkan, alamat dan link peta hanya Home Service | |
| INV-001..005, 009..016 (006..008 dihapus 20 Nov, jangan dipakai ulang; INV-013 status pembayaran; INV-014..016 link invoice bertanda tangan, aturan 28) | ServiceInvoiceTest, ServiceInvoiceContentTest, ServiceInvoicePartTest, ServiceInvoiceLinkTest | Nomor invoice, PDF jadi, tombol Invoice hanya untuk Selesai, part terhapus tetap bisa dicetak | |
| TSJ-001..017 (004 dua kali) | ServiceItemTest | Baris jasa: salin nama dan harga, harga boleh diubah, harga negatif atau kosong ditolak, nota lama tidak ikut perubahan katalog, total jasa | aturan 6, 7 |
| SPF-001..015 | ServicePartTest | Baris part: qty tidak melebihi stok, harga satuan disalin dan terkunci, part ganda ditolak, subtotal dan grand total | aturan 6, 7 |
| HPU-001..007 | ServicePhoneTest | Satu HP satu servis aktif, +62 dan 08 dianggap sama, HP disimpan `08...` | aturan 4 |
| KNC-001..011 | ServiceLockTest | Servis final terkunci: tidak bisa diedit, 403 di Edit, klik baris ke Lihat | aturan 3 |
| FLW-001..006 | ServiceStatusFlowTest | Transisi status sah dan terlarang, pilihan status di form (FLW-006 sejak 17 Nov: Tambah servis hanya Dikonfirmasi, Dikerjakan, Selesai) | aturan 3, 20 |
| BTL-001..010 | Booking/CancelByCustomerTest | Pelanggan membatalkan booking Menunggu lewat Cek Booking dan Riwayat, alasan dari daftar atau kosong, Dikonfirmasi tidak bisa (link WA), HP salah atau milik orang lain tidak bisa, ikut batas percobaan, notifikasi owner, nomor bisa booking lagi | aturan 21 |
| MTR-001..004 | Services/ServiceMechanicTest | Isian Montir tersembunyi saat Menunggu, muncul mulai Dikonfirmasi, tersimpan, maksimal 255 karakter, tidak tampil di Cek Booking dan Riwayat | `mechanic_names` |
| TGL-001..006 | Services/ServiceDateFilterTest | Filter Tanggal (kalender rentang) tabel Servis: default hari ini, rentang besok, tanggal lebih dari 14 hari lalu, filter dihapus = semua, tab Menunggu tidak difilter, angka tab ikut filter kecuali Menunggu | aturan 22 |
| GMP-001..004 | Settings/WorkshopSettingTest | Baca koordinat dari link Google Maps (q=, @, !3d!4d, koordinat langsung, link pendek), link tanpa lokasi ditolak, nomor bengkel dipakai di footer dan tombol WA. TPG-009..012 sejak 20 Nov menguji link Maps dan nomor HP (bukan koordinat) | aturan 25 |
| SLG-001..006 | ServiceTypes/ServiceTypeSlugTest | Slug jasa otomatis dari nama, unik termasuk arsip, ikut berubah saat nama diubah, URL id lama dialihkan 301, kartu menaut ke slug, halaman 404 bergaya landing | aturan 26 |
| WAO-001..012 | WhatsApp/NotifyCustomerWhatsAppTest | WA otomatis: Dikonfirmasi (kode, jadwal), Selesai (total), Ditolak/Dibatalkan (alasan), status lain dan batal oleh pelanggan tidak dikirim, gagal kirim tercatat tanpa membatalkan status, panggilan Fonnte (URL, header, isian), jawaban gagal Fonnte, mode log tanpa token, link invoice di pesan Selesai, lokasi bengkel atau alamat di pesan Dikonfirmasi, status pembayaran di pesan Selesai | aturan 27, 28, 29 |
| SRC-001..010 | ServiceSourceTest | Asal servis: booking beranda online, Tambah servis admin, data pelanggan booking online terkunci dan tidak bisa dipaksa, jadwal terkunci (SRC-005 dibalik di hari yang sama) termasuk saat Dikerjakan, servis admin bebas diubah, Jasa dan Spare Part hanya saat Dikerjakan/Selesai, jasa lama tidak terhapus | aturan 20 |
| ANT-001..019 | ServiceQueueTest | Antrian: satu `in_progress` (penghalang), jadwal lebih awal hanya konfirmasi lewati (ANT-009 diubah 20 Nov, ANT-013..014), nomor antrean hari ini: urut jam, Selesai tetap dihitung, batal/menunggu tidak, sedang dikerjakan dan sisa, bukan hari ini null (ANT-015..019) | aturan 11, 22 |
| STK-001..008, 012..013 | ServiceStockTest | Stok berkurang saat Selesai, sekali saja, stok kurang membatalkan, modal konfirmasi | aturan 2 |
| STK-010..011 | ServiceStockFormTest | Stok kurang lewat form tidak error, batal atau tolak tidak mengurangi stok | aturan 2 |
| SUS-001..005 | ServiceStockUsageTest | Pemakaian tercatat di riwayat stok, `syncStock` dua kali tidak menggandakan | aturan 1, 2 |
| LNS-001..010, 014 | MarkServicePaidTest | Tandai Lunas: hanya Selesai, tidak dua kali, `paid_at` tidak mass-assignable, tombol, tampilan Lunas dan Belum Lunas, filter Pembayaran di tab Selesai tidak menyaring tab lain | aturan 13 |
| NAV-001..003 | ServiceNavigationBadgeTest | Badge navigasi menghitung status Menunggu, tanpa yang di-soft-delete | |
| PRC-001 | ServicePriceFormTest | Harga jasa tampil bulat saat edit | |

## Booking (`tests/Feature/Booking/`)

| Kode | Berkas | Yang dicek | Aturan |
| --- | --- | --- | --- |
| BKG-001..006 | CreateBookingTest | Booking baru Menunggu, HP `08...`, HP aktif ditolak, HP servis Selesai atau terhapus boleh lagi | aturan 4 |
| BKG-010..020 | BookingFormTest | Formulir tamu: kirim valid, wajib, honeypot, 5 kiriman per menit, pilih slot, koreksi tanggal, pesan HP aktif dengan link Cek Booking, Buat booking lain tetap menolak nomor yang sama | aturan 4 |
| BKA-001..007 | BookingAccountTest | Booking customer login: form terisi, `user_id` tersimpan, HP akun tersimpan atau tidak tertimpa, tamu `user_id` NULL | aturan 15 |
| CEK-001..016 | BookingStatusCheckTest | Cek Booking: cocok kode booking dan nomor (+62 = 08), pesan gagal sama, wajib isi, 5 per menit, soft delete tersembunyi, harga/total/alamat tidak bocor, nomor tidak valid, kode 6 angka unik, spasi di kode diabaikan, nama + HP saja ditolak, kode bukan fillable, kode dan link WA tampil setelah booking, alasan tampil untuk Dibatalkan/Ditolak dan tidak untuk status lain, nomor antrean hari ini (CEK-016) | aturan 12, 22 |
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
| RSN-001..006 | CancelReasonFormTest | Alasan wajib untuk Ditolak dan Dibatalkan, tersembunyi untuk status aktif (RSN-006 tombol cepat "Pelanggan tidak datang"; RSN-005 sejak 17 Nov lewat form ubah) | |
| NTF-001..005, 010..011 | BookingNotificationTest, BookingNotificationTypeTest | Notifikasi booking baru ke owner, isi memuat nama dan tipe, notifikasi booking baru dan pembatalan membawa link ke detail servis dan tertanda dibaca saat diklik | aturan 10, 36 |
| HPN-001..005 | Booking/PhoneNumberTest | Nomor HP: semua bentuk (+62, 62, 08, 8) dirapikan jadi 08..., batas 10-14 digit, booking dengan nomor tanpa 0 dan nomor ngawur, Cek Booking cocok walau ditulis beda, nomor bengkel di Jarak & Ongkos | aturan 35 |
| SED-001..002 | SeederHomeServiceTest | Seeder punya servis Home Service dengan koordinat | |

## Jarak, ongkos, pengaturan bengkel

| Kode | Berkas | Yang dicek | Aturan |
| --- | --- | --- | --- |
| BKD-001..003 | Distance/BookingDistanceTest | Jarak udara tersimpan untuk Home Service | aturan 8 |
| LIM-001..004 | Distance/BookingLimitTest | Batas jarak maksimal, pesan penolakan | aturan 8 |
| PRV-001..007 | Distance/BookingPreviewTest | Pratinjau jarak di formulir; kotak hijau dalam jangkauan, kotak merah di luar jangkauan dengan tombol Pilih Ke Bengkel, tanpa link WA (006..007) | aturan 40 |
| SET-001..002 | Distance/DistanceMethodSettingTest | Ubah jarak maksimal dan harga BBM | |
| DST-001..002 | Distance/DistanceServiceTest | Jarak Haversine | |
| SDR-001 | Distance/SeederDistanceTest | Seeder Home Service punya jarak | |
| HAV-001..005 | Unit/Distance/HaversineCalculatorTest | Rumus Haversine | |
| TRN-001..011 | Transport/TransportFeeTest | Ongkos PP, pembulatan Rp500, snapshot harga BBM, hitung ulang saat jarak berubah, Total tabel admin = jasa + part + ongkos, `-` kalau 0, urut Total | aturan 7, 8 |
| TPG-001..013 | Settings/WorkshopSettingTest | Halaman pengaturan bengkel: validasi jarak, BBM, koordinat | |

## Dashboard (`tests/Feature/Dashboard/`)

| Kode | Berkas | Yang dicek | Aturan |
| --- | --- | --- | --- |
| DSH-001..019 | DashboardPeriodTest | Filter periode, preset, rentang sendiri, pembanding | US-08 |
| KPI-001..008, 010..011 | KpiSummaryTest | Empat kartu KPI dan tren dibanding periode lalu; sejak 20 Nov kartu keempat Belum dibayar (KPI-008), Menunggu dihitung lewat pendingNow (KPI-007) | US-08, aturan 24 |
| MET-001..017 | ServiceMetricsTest | Hitungan booking, selesai, pendapatan (sejak 20 Nov dasar uang diterima: lunas menurut tanggal lunas, termasuk transport, belum dibayar terpisah, grafik per hari/bulan: MET-013..017), pembatalan, tren, komposisi, terlaris | US-08 |
| TRD-001..002 | TrendChartTest | Grafik pendapatan (sejak 20 Nov menggantikan grafik tren booking) | US-08, aturan 24 |
| HRI-001, 003 (002 dihapus 20 Nov) | Dashboard/TodayTest | Dasbor Hari ini: jadwal hari ini tanpa yang batal atau hari lain, dasbor memuat bagian Hari ini | aturan 24 |
| LAP-001..002, 004..008, 010, 012 (LAP-003 filter tipe, LAP-009 tabel piutang, LAP-011 grafik jasa/part paling menghasilkan dihapus 20-21 Nov, jangan dipakai ulang) | Reports/LaporanPendapatanTest | Laporan Pendapatan: halaman terbuka, hanya servis lunas di periode, ringkasan jasa+part+transport dan belum dibayar, unduh PDF, rekap per hari, minggu, dan bulan, jumlah rekap sama dengan ringkasan, kartu gaya dasbor, satuan rekap PDF otomatis | aturan 23, 30 |
| LSK-001..004, 006..009 (LSK-005 penanda hampir habis dihapus 21 Nov, jangan dipakai ulang) | Reports/LaporanStokTest | Laporan Stok (mutasi persediaan): halaman terbuka, stok awal dari riwayat sebelum periode dan masuk/keluar/penyesuaian hanya di periode, part baru di tengah periode, stok akhir sama dengan stok sebenarnya, part diarsip, kartu ringkasan (jumlah jenis barang), unduh PDF, cari dan urut tabel | aturan 31, 33 |
| CMP-001..005 | CompositionChartsTest | Donat status dan tipe, jasa dan part terlaris | US-08 |

## Jasa dan kategori

| Kode | Berkas | Yang dicek | Aturan |
| --- | --- | --- | --- |
| JSA-001..006, 014..016 | ServiceTypes/ServiceTypeResourceTest | Tambah dan ubah jasa, deskripsi dan foto opsional, `photo_url`. Hapus = arsip: foto tetap, hilang dari katalog dan form, servis aktif yang memakainya tetap bisa disimpan | US-07, aturan 17 |
| JSA-007..009, 013 | ServiceTypes/ServiceTypeSeederTest | Seeder 7 jasa berdeskripsi, dua kali jalan tidak menggandakan, foto tidak tertimpa, foto bawaan terpasang | |
| PRC-003 | ServiceTypes/ServiceTypePriceFormTest | Harga acuan tampil bulat saat edit | |
| KAT, TKT, EKT, HKT | Categories/CategoryResourceTest | Daftar, tambah, ubah, hapus kategori lewat modal. Kategori yang dipakai part tetap bisa dihapus | |

## Akun customer dan area customer

| Kode | Berkas | Yang dicek | Aturan |
| --- | --- | --- | --- |
| AUTH-001..024 (tanpa 002 dan 005) | Auth/CustomerAuthTest | Daftar, masuk, keluar, throttle, role tidak bisa dipilih, redirect owner dan customer, halaman dan navbar tamu, login satu pintu (tamu /admin ke /masuk, /admin/login 404) | aturan 14 |
| SEC-001..003 | Auth/PanelSecurityTest | Customer tidak bisa buka `/admin`, owner bisa, notifikasi hanya ke owner | aturan 10, 14 |
| RWY-001..023 | Riwayat/RiwayatTest | Riwayat milik sendiri, soft delete tersembunyi, urut tanggal dan Total (termasuk ongkos), paginasi 10/25/50, filter status, tipe, dan pembayaran, detail, alasan batal tampil di detail hanya untuk Dibatalkan/Ditolak (RWY-013 dibalik sejak D3, RWY-023), 404 milik orang lain, invoice hanya Selesai milik sendiri, owner dan tamu dialihkan | aturan 16 |
| PRF-001..007 | Profile/ProfilTest | Simpan nama dan HP (dinormalkan), HP opsional, email dan role tidak bisa diubah, owner dan tamu dialihkan | aturan 16 |

## Halaman publik dan unit

| Kode | Berkas | Yang dicek | Aturan |
| --- | --- | --- | --- |
| WAF-001..002 | Landing/WhatsAppFloatTest | Tombol WA melayang di beranda dan `/layanan`, tidak di `/masuk` dan `/daftar` | |
| WAL-001..004 | Unit/WhatsAppLinkTest | Tautan wa.me: format internasional, encode pesan | |
| (tanpa ID) | Unit/PhoneNumberTest | `PhoneNumber::normalize` ke `08...`, null dan kosong | |
| SED-001 | Seeders/SeederTest | `DatabaseSeeder` jalan tanpa error | |
| AKN-001..003 | Seeders/AccountSeederTest | Akun bawaan: pemilik dan 2 pelanggan demo, server menolak seeder tanpa OWNER_PASSWORD, server tanpa suku cadang dan servis contoh | aturan 37 |
| GRN-001..008 | SpareParts/WarrantyTest | Garansi suku cadang: disalin ke servis dan tidak ikut master, tidak bisa diisi manual, berlaku sejak lunas sampai akhir hari lunas + masa garansi, belum lunas belum berjalan, tanpa garansi, invoice dan ketentuan, servis batal, validasi form | aturan 38 |

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

**Belum ada tes (lihat `PLANNING.md` bagian A).**
- (Hapus jasa sudah dites 17 Nov: JSA-014..016.)
- (`RestoreAction` sparepart tidak dibangun, keputusan 17 Nov.)
- Opsional: `/layanan/{id}` 404, `/booking` 404.

**Sengaja tidak dites, cek manual (aturan "Kapan wajib tes", `PROJECT_RULES.md` bagian 10).** Isi beranda dan `/layanan`, tombol arah di peta, tabel Jasa, isi tampilan detail Riwayat, dropdown navbar, logo, kolom yang tampil per tab di tabel Servis. ID yang pernah dipakai untuk tes tampilan lalu dihapus: LNS-011..013, LGO-001..002 (jangan dipakai ulang).

## Tes yang dihapus 20 Nov 2026 (bersih-bersih, tes tampilan saja)

Dihapus karena hanya mengecek tampilan (aturan "kapan wajib tes" di PROJECT_RULES bagian 10). ID ini **jangan dipakai ulang**: ADM-001, ADM-002, ADR-011, BKT-004, FRM-001, FRM-009, DSH-012, KPI-011, TRD-001, TRD-002, CMP-001, CMP-002, CMP-005, HRI-003, MAP-001, MAP-002, MAP-004, LOC-001 dan LOC-002 di BookingLocateTest (LOC-001/002 di BookingLocationTest tetap), TIP-003, AUTH-019, AUTH-020, AUTH-021, RWY-009, WAF-001, WAF-002. Berkas BookingLocateTest, TrendChartTest, dan WhatsAppFloatTest ikut terhapus karena kosong.
