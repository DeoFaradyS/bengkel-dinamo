# PLANNING: SIM Bengkel Cak Budi Dinamo

Catatan pekerjaan yang belum selesai, bug yang diketahui, dan keputusan terbuka. Aturan dan status fitur yang sudah jadi ada di `PROJECT_RULES.md`. Terakhir diperbarui: 10 November 2026 (siang, tahap 3 selesai, 530 tes hijau).

Tanda: `[ ]` belum, `[~]` sebagian, `[x]` selesai.

## A. Utang tes (ditunda atas permintaan pemilik, wajib sebelum fitur dianggap selesai)

Penomoran lanjut dari ID terakhir di tiap modul. ID di bawah sementara. Disaring 17 Nov 2026 dengan aturan "Kapan wajib tes" (`PROJECT_RULES.md` bagian 10): item tampilan ditandai **[manual]**, cukup dicek manual saat demo, tidak ditulis tesnya.

**Jasa (`tests/Feature/ServiceTypes/`)**
- [x] JSA-007..009 seeder (ada di `ServiceTypeSeederTest.php`, plus JSA-013 foto bawaan; dicek lulus 10 Nov): mengisi 7 jasa dengan deskripsi, dijalankan dua kali tidak menggandakan, foto yang sudah diunggah tidak tertimpa. Kalau berkas tes dengan angka 5 sudah tersimpan, angkanya diganti jadi 7 (penyesuaian karena seeder berubah, bukan melemahkan tes).
- [x] [manual] Tabel Jasa: kolom foto dan deskripsi ada, deskripsi panjang dipotong satu baris, jasa tanpa deskripsi dan foto tetap tampil. JSA-010..012 tidak dipakai.
- [x] Harga tampil bulat di halaman edit (`mutateFormDataBeforeFill` di `EditServiceType`). Dicek PRC-003, lulus 9 Nov.
- [x] Hapus jasa = arsip (D1, 17 Nov 2026): JSA-014..016, TSJ-010 disesuaikan.

**Halaman publik**
- [x] [manual] Beranda memuat jasa dari DB, pesan kosong tanpa jasa, tombol "Lihat semua layanan" hanya kalau jasa lebih dari 6.
- [x] [manual] `/layanan`: kosong, dan paginasi 9 per halaman.
- [x] [manual] Section peta memuat tombol arah.
- [ ] Opsional (perilaku rute, tidak mendesak): `/layanan/{id}` 404 kalau id tidak ada, `/booking` 404.

**Cek Booking (aturan 12).** Selesai 17 Nov 2026: CEK-001..013 di `tests/Feature/Booking/BookingStatusCheckTest.php`. Sejak 17 Nov cek pakai kode booking + nomor HP (masukan dospem: nama + HP kurang aman). Kolom baru `services.booking_code` lewat migrasi `2026_11_17_110000_add_booking_code_to_services_table` (servis lama ikut diberi kode; ERD perlu ditambah kolom ini).
- [x] Kode dan nomor cocok: servis tampil, `+62` sama dengan `08` (CEK-001).
- [x] Kode salah atau nomor salah: tidak tampil, pesan gagal sama (CEK-002, CEK-003).
- [x] Kode 6 angka unik tiap servis (CEK-009), kode dengan spasi tetap cocok (CEK-010), nama + HP tanpa kode tidak membuka booking (CEK-011), kode tidak bisa diisi dari input (CEK-012), setelah booking kode dan link WA tampil (CEK-013).
- [x] Input kosong ditolak validasi (CEK-004).
- [x] Percobaan ke-6 dalam semenit ditolak (CEK-005).
- [x] Servis yang di-soft-delete tidak tampil (CEK-006).
- [x] Harga, total, dan alamat tidak ikut tampil (CEK-007). Alasan batal sejak D3 tampil untuk Dibatalkan dan Ditolak (CEK-014), tidak untuk status lain (CEK-015).
- [x] Nomor tidak valid (`abc`) tidak mencocokkan servis apa pun (CEK-008).

**Area customer (10 Nov 2026)**
- [x] Riwayat: filter Tipe (RWY-019) dan Pembayaran (RWY-020), urut Total termasuk ongkos (RWY-021), per halaman 25 dan 50, nilai lain jatuh ke 10 (RWY-022). Selesai 17 Nov 2026.
- [x] [manual] Detail `/riwayat/{id}`: rincian jasa dan part, ringkasan biaya, badge Lunas dan Belum Lunas. Invoice hanya Selesai milik sendiri sudah dicek RWY-016..018.
- [x] Login satu pintu: tamu ke `/admin` diarahkan ke `/masuk` (AUTH-022), `/admin/login` 404 (AUTH-023), owner login lalu bisa buka `/admin` (AUTH-024). Selesai 17 Nov 2026.
- [x] [manual] Navbar: dropdown owner berisi menu panel, dropdown customer berisi Riwayat Booking dan Profil, tamu melihat Masuk dan Daftar.
- [x] Booking login: BKA-001..007 di `tests/Feature/Booking/BookingAccountTest.php`.

## B. Bug dan hal yang perlu dicek

- [x] **LOC-001.** Ekspektasi tes diubah ke "Pakai lokasi saya" (label view memang berubah).
- [x] **PRC-003** (`tests/Feature/ServiceTypePriceFormTest.php`). Target dipindah ke `EditServiceType`, assertion dan ID tetap. Tes ini menangkap bug asli (harga tampil 100x), sudah diperbaiki lewat `mutateFormDataBeforeFill`.
- [x] **Upload foto jasa gagal** (500 di `upload-file`). Penyebab: `upload_tmp_dir` di php.ini Laragon kosong (`;upload_tmp_dir =`), file temp upload tidak punya path, log: `Path must not be empty`. Perbaikan: buat folder `D:/Aplikasi/laragon/tmp`, set `upload_tmp_dir` ke sana, restart Laragon. Catatan: `php artisan pail` tidak jalan di Windows (butuh `pcntl`), baca `storage/logs/laravel.log`.
- [x] Sisa rujukan ke `/booking`: link navbar dan footer dibersihkan, tes BKG-010 dan TIP-001 pindah ke `get('/')`. Link mati `/#galeri` diganti ke `/#cek-booking`.
- [x] Ganti `id="appointment"` di `hero.blade.php` jadi `id="booking"`. Tombol "Buat booking" di Riwayat dan link lain sudah memakai `/#booking`, jadi anchor ini mati sampai id diganti. Dump HTML tes 10 Nov masih menunjukkan `id="appointment"`. Tombol "Buat Janji" di navbar sudah tidak ada. Cek tidak ada sisa: `Get-ChildItem resources,tests,app -Recurse | Select-String -Pattern 'appointment|href="/booking|#galeri'` (harus kosong).
- [x] Istilah label UI: diputuskan "Booking" (17 Nov 2026). Tombol "Buat booking" di Riwayat diganti "Booking Sekarang", sama dengan tombol kirim di beranda. Tidak ada lagi "Buat Janji" di tampilan.
- [x] `service-card.blade.php` dicek: ada, props `name`, `description`, `price`, `photoUrl`, fallback inisial dan "Hubungi kami" jalan. URL foto dibentuk accessor `ServiceType::photoUrl()` (`Storage::disk('public')->url()`). Sisa: komentar di atas berkas masih bilang "tidak ada halaman detail per layanan", perbarui.
- [x] Section galeri: berkas tidak ada, tidak ada `@include` yang rusak, link galeri sudah diganti.
- [x] Cek Booking: `BookingStatusCheck` memakai `$phone ? ... : collect()`, jadi nomor kosong hasil `normalize()` tidak mencocokkan servis apa pun. Dicek 17 Nov 2026.
- [x] `ServicesTable.php` (panel Servis): aksi `tandai_lunas` memanggil `Notification::make()`, tapi berkas itu tidak punya `use Filament\Notifications\Notification;` (`ViewService.php` punya). Aksi dari tabel bisa error "class not found". Cek manual lalu tambah tes.
- [x] Kolom Total di tabel admin tidak menghitung `transport_fee`, sedangkan detail admin dan area customer menghitungnya. Total di `/riwayat` sudah ikut detail (jasa + part + transport). Diputuskan 17 Nov 2026: disamakan (aturan 7), tes TRN-009..011.
- [ ] Cast `Service`: `fuel_price` dan `transport_fee` bertipe `integer` padahal kolom `decimal(12,2)`, dan `saving` memakai `(int)`. Aman selama harga BBM dan ongkos bulat. Cek kalau suatu saat ada pecahan.
- [x] Kompres foto beranda: `bengkel.png` 2,6 MB diganti `bengkel.webp` 1400px, 149 KB (17 Nov 2026).
- [x] `php artisan test` penuh. Riwayat hasil: 455 (9 Nov), 480 (login), 494 (riwayat), 503 dan 506 (Tandai Lunas), 514 (area customer), **530 lulus, 0 gagal (10 Nov 2026, siang).**

## C. Fitur belum dikerjakan

- [ ] Rekap stok per periode (Iterasi 4). Belum diputuskan dibangun atau ditulis sebagai pengembangan lanjutan skripsi.
- [ ] Promo dikelola dari panel (sisa US-07). Layanan sudah lewat menu Jasa.
- [x] Asal servis dan hak ubah admin, tahap 1 (17 Nov 2026, aturan 20): kolom `services.source` lewat migrasi `2026_11_17_120000_add_source_to_services_table` (ERD perlu ditambah kolom ini; keputusan lama "tanpa `source`" dicabut karena perlu membedakan booking online dan servis buatan admin), data pelanggan booking online dikunci termasuk jadwal (awalnya jadwal boleh digeser saat Menunggu/Dikonfirmasi, diputuskan ulang di hari yang sama), Jasa dan Spare Part hanya saat Dikerjakan/Selesai, status awal Tambah servis hanya Dikonfirmasi/Dikerjakan/Selesai, kolom Asal di tabel dan detail. Tes SRC-001..010; FLW-006, STK-006, RSN-005 disesuaikan.
- [x] Pelanggan membatalkan booking sendiri selama Menunggu, di Cek Booking dan detail Riwayat, alasan opsional, notifikasi ke owner (17 Nov 2026, aturan 21, BTL-001..010). Ganti jadwal = batalkan lalu booking ulang; ubah jadwal mandiri jadi pengembangan lanjutan.
- [ ] Tahap 2: tombol aksi status di halaman detail servis (Konfirmasi, Tolak + alasan, Mulai kerjakan, Selesaikan, Batalkan + alasan), pilihan status dicabut dari form ubah, klik baris tabel membuka detail. Menggantikan butir tombol cepat di bawah.
- [ ] Tombol cepat konfirmasi/tolak booking di tabel servis (opsional). Wajib memanggil `startBlockerMessage()` kalau mengubah status ke `in_progress`.
- [x] Cek Booking: tampilkan alasan batal (D3, 17 Nov 2026).
- [ ] Booking yang terisi jasa pilihan dari halaman detail (D5).
- [ ] Slug untuk URL jasa (D4).
- [ ] Invoice PDF memuat status Lunas atau Belum Lunas (opsional).
- [ ] Profil: ganti password (opsional, belum diminta).
- [ ] Sinkronkan `PROJECT_RULES.md` setiap ada perubahan beranda atau aturan.
- [x] Tabel Servis: kolom Total dan Status bersyarat per tab (D9). Selesai 17 Nov 2026. Tampil atau sembunyi kolom dicek manual (aturan tes bagian 10).

## D. Keputusan terbuka

**D1. Hapus jasa yang sudah dipakai di `service_items`. DIPUTUSKAN DAN SELESAI (17 Nov 2026):** soft delete (arsip), sama seperti sparepart, tanpa tombol Pulihkan. Kolom `service_types.deleted_at` lewat migrasi baru `2026_11_17_100000_add_soft_deletes_to_service_types_table` (ERD perlu ditambah kolom ini). Catatan awal: Kolom `service_type_id` nullable dan nama tersimpan sebagai salinan di `service_items`, jadi menghapus tidak merusak riwayat. Pilihan: boleh dihapus (sekarang `DeleteAction` polos), atau diblokir kalau sudah dipakai.

**D2. Login pelanggan. DIPUTUSKAN (8 Nov 2026) dan SELESAI (10 Nov 2026).** Email + password, tanpa OTP. Pakai tabel `users` dengan kolom `role`, bukan tabel `customers` dan bukan guard terpisah. `services.user_id` nullable (NULL = tamu). Satu pintu login `/masuk` untuk owner dan customer (login bawaan Filament dicabut). Rincian di `PROJECT_RULES.md`.

**D3. Alasan batal di Cek Booking dan area customer. CEK BOOKING DIPUTUSKAN DAN SELESAI (17 Nov 2026):** alasan tampil di kartu Cek Booking untuk Dibatalkan dan Ditolak, form admin diberi keterangan "Alasan ini terlihat oleh pelanggan di Cek Booking." Detail Riwayat customer juga menampilkannya (RWY-013 dibalik, RWY-023 untuk status lain); tabel Riwayat tidak. Catatan awal: `cancel_reason` mungkin catatan internal pemilik. Saat ini tidak ditampilkan di Cek Booking, Riwayat, maupun detail. Putuskan perlu ditampilkan atau tidak.

**D4. Slug jasa.** Detail jasa sekarang `/layanan/{id}`. Slug butuh kolom baru lewat migrasi dan persetujuan.

**D5. Booking dari detail jasa.** Halaman detail tidak punya tombol booking (sengaja dihapus). Kalau mau tombol yang mengarah ke form di hero dan mengisi jasa otomatis, `BookingForm` perlu diubah.

**D6. Isi lama bagian 8 yang masih terbuka:** harga part satu `price` atau dibedakan beli dan jual; servis selesai tapi barang tidak diambil (`picked_up_at`); grace period no-show; kedaluwarsa sparepart (`expired_at`).

**D7. Data demo di staging.** Seeder demo sekarang jalan di semua environment kecuali production. Kalau nanti ada staging dan datanya tidak boleh berisi demo, putuskan cara memisahkan (misalnya env khusus atau flag di `.env`).

**D8. Harga tampil untuk pelanggan login (BARU, 10 Nov 2026).** Tabel dan detail `/riwayat` disamakan dengan panel admin atas permintaan pemilik, jadi menampilkan harga jasa, harga part, transport, total, dan status lunas. Aturan 12 hanya melarang harga di Cek Booking publik (tanpa login). Masih tersembunyi dari pelanggan: nama montir, catatan internal, data stok. Perlu persetujuan pemilik bengkel dan dospem bahwa pelanggan login boleh melihat harga.

**D9. Kolom Total dan Status di tabel Servis admin per tab. DIPUTUSKAN DAN SELESAI (17 Nov 2026):** Total = jasa + part + ongkos (Home Service Dikonfirmasi menampilkan ongkos), `-` kalau 0. Usul awal:
Sekarang Total tampil di semua tab dan menunjukkan Rp 0 untuk Menunggu, padahal harga belum dihitung. Kolom Status juga tampil di semua tab, padahal tab sudah memfilter status.
Usul:
- Total: tampil di Dikonfirmasi, Dikerjakan, Selesai, dan Semua. Di baris yang belum punya harga tampil `-`, bukan Rp 0. Tab Menunggu, Ditolak, Kedaluwarsa, Dibatalkan: kolom disembunyikan.
- Dikonfirmasi: Home Service sudah punya ongkir dari booking. Pilihan: tampilkan sebagai estimasi, atau `-` sampai ada item jasa.
- Status: tampil hanya di tab Semua, sama seperti kolom Pembayaran di tab Selesai (aturan 13).
- Area customer `/riwayat` tidak berubah, karena tidak punya tab.
Terkait: B, Total tabel admin belum hitung `transport_fee`. Putuskan bersama.

## E. Selesai (ringkas)

- [x] `service_types` ditambah `description` dan `photo` (migrasi lama diedit, tahap dev).
- [x] Form Jasa pindah ke halaman sendiri (`ListServiceTypes`, `CreateServiceType`, `EditServiceType`), layout dua kolom ikut sparepart. `ManageServiceTypes` dihapus.
- [x] Tabel Jasa: foto, nama, harga acuan, deskripsi dipotong satu baris.
- [x] Seeder jasa jadi 7 (2 jasa baru: nama dan harga tebakan, perlu dicek pemilik: Ganti bendix, Ganti magnetic switch; Bubut komutator dibuang 10 Nov 2026).
- [x] Beranda membaca jasa dari DB, halaman `/layanan` dan `/layanan/{id}`.
- [x] Halaman `/booking` dihapus (formulir ada di hero).
- [x] Section testimoni dicabut dari beranda (berkas masih ada).
- [x] Section Cek Booking ditambah.
- [x] Section peta disederhanakan: peta + tombol arah.
- [x] Foto hero diganti `public/images/bengkel.png`.
- [x] Tes JSA-001..006 lulus.
- [x] Upload foto jasa beres (php.ini), link navbar dan footer dibersihkan, 4 tes usang disesuaikan, harga edit tampil bulat.
- [x] ERD baru diterapkan ke migrasi, tahap 1 dan 2 selesai (9 Nov 2026). Lihat bagian F.
- [x] Login dan register customer, keamanan panel (`canAccessPanel` hanya owner), Tandai Lunas (9 sampai 10 Nov 2026).
- [x] **Area customer (10 Nov 2026):**
  - Layout `layouts/customer.blade.php`: navbar dan footer beranda, sidebar kiri (Riwayat Booking, Profil) berstyle brand, garis tipis tanpa shadow, tinggi minimal setinggi layar. Di HP sidebar jadi baris menu yang bisa digeser.
  - Navbar: customer dan owner login melihat avatar huruf awal dan nama depan dengan dropdown. Owner: Dashboard, Servis, Sparepart, Jasa, Kategori, Jarak Ongkos (langsung ke panel). Customer: Riwayat Booking, Profil. Keluar ada di dropdown. Tamu: tombol Masuk (outline) dan Daftar (solid), sama tinggi dan huruf kapital. Di HP semuanya jadi daftar biasa di menu lipat.
  - Satu pintu login `/masuk`: `->login()` dicabut dari `AdminPanelProvider`. Owner login langsung ke `/admin`. Owner yang membuka `/riwayat` atau `/profil` diarahkan ke `/admin`. Panel punya menu "Kembali ke Beranda" di menu pengguna.
  - Riwayat `/riwayat` ala tabel admin: No, Tanggal & Jam, Tipe, Status, Pembayaran, Total, Aksi. Filter Tipe, Status, Pembayaran. Urut Tanggal dan Total. Per halaman 10, 25, 50. Paginasi `pagination.filament`. Komponen `x-status-badge` membaca warna dari enum.
  - Detail `/riwayat/{id}`: Data Servis, Jasa, Spare Part, Ringkasan biaya. Hanya milik sendiri, selain itu 404. Tombol Cetak Invoice (`/riwayat/{id}/invoice`, PDF, hanya servis Selesai milik sendiri).
  - Profil `/profil`: nama dan nomor HP bisa diubah, email tampil terkunci. Nomor HP opsional (wajib saat booking), dinormalkan lewat `PhoneNumber::normalize`.
  - Booking login: `BookingForm` mengisi nama dan HP dari akun, `CreateBooking` menyimpan `user_id`, HP akun yang kosong tersimpan setelah booking pertama (`forceFill`). Tes ditulis pemilik.
  - Tes: RWY-001 dan RWY-006 disesuaikan (keluhan pindah ke detail), RWY-009 lewat tautan relatif `route(..., absolute: false)`, RWY-010..018 baru, PRF-001..007 baru.

## G. Target sebelum bimbingan (dicatat 9 Nov 2026, diperbarui 10 Nov 2026)

Daftar dari pemilik. Per 10 Nov 2026 (siang): tahap 1 sampai 3 selesai, 530 tes hijau. Sisa tahap 4 (dokumen dan tes). Bagian F di bawah adalah rinciannya.

- [x] Login dan register customer (email + password). Rinci: bagian F tahap 3.
- [x] Booking untuk customer yang login (form terisi otomatis, `services.user_id` terisi).
- [x] Riwayat booking untuk customer yang login ("Riwayat Booking", tabel, detail, invoice) dan halaman Profil.
- [~] ERD diimplementasikan ke migrasi (bagian F). Tahap 1 sampai 3 selesai. Sisa: tahap 4 (dokumen, ERD di Eraser).
- [~] Saran dospem yang sudah masuk rencana ERD (`mechanic_names`, `paid_at`) sudah diimplementasikan (kolom, seeder, aksi Tandai Lunas). Sisa: siapkan daftarnya untuk ditunjukkan saat bimbingan.
- [x] Rapikan dan organisasi tes (`tests/README.md` dibuat 10 Nov 2026; sisa: ID kembar dan judul usang, lihat catatan di README): tiap berkas dan ID tes punya penjelasan satu baris "mengetes apa". Usul: tabel `tests/README.md` berisi kode modul (BKG, LOC, TIP, PRC, JSA, ANT, AUTH, SEC, LNS, RWY, PRF, dst), berkas, dan aturan bisnis yang dicek.
- [x] Perbarui laporan skripsi: banyak perubahan (landing, jasa, ERD, login customer, area customer, antrian, Cek Booking, Tandai Lunas).
- [ ] Notifikasi WhatsApp ke pelanggan: opsional, dikerjakan belakangan kalau sempat.
- [ ] Siapkan bahan bimbingan: demo (beranda, `/layanan`, Cek Booking, upload foto jasa, login customer, booking login, Riwayat dan detail, Cetak Invoice, Profil, Tandai Lunas di panel), angka tes (585), daftar migrasi yang berubah, keputusan yang butuh jawaban (D1, D3, D8).

## F. Rencana perubahan database dan fitur turunannya (diperbarui 10 Nov 2026, siang)

ERD final dan seluruh keputusannya ada di `PROJECT_RULES.md` bagian 4 dan 4a. Cara: edit migrasi lama + `migrate:fresh --seed`. Branch kerja: `feat/erd-baru`. Baseline sebelum mulai: commit dengan 455 tes hijau (di `main`).

**Keputusan 9 Nov 2026:** tabel `statuses` tidak dibuat (status = kolom `services.status` varchar + enum `ServiceStatus`). Label: Menunggu, Dikonfirmasi, Dikerjakan, Selesai, Dibatalkan, Ditolak, Kedaluwarsa.

**Keputusan dan temuan 9 Nov 2026 (malam):**
- Migrasi dan seeder `statuses` dibuang. `services.status` string default `pending`, index `(status, scheduled_at)`.
- Data demo seeder (`DemoCustomerSeeder`, `SparePartSeeder`, `ServiceSeeder`) jalan di semua environment kecuali production (sebelumnya hanya local), karena 4 tes seeder butuh data demo. Lihat D7.
- Seeder yang memakai `addStock()` atau `syncStock()` harus memanggil `Auth::setUser(owner)` dulu, karena `stock_movements.user_id` wajib dan diambil dari `auth()->id()`.
- `WithoutModelEvents` dibuang dari `DatabaseSeeder` dan `WorkshopSettingSeeder`, karena event `Service::saving` menghitung ongkos dan menyalin harga BBM untuk Home Service.
- `SparePartSeeder` membuat part dengan stok 0 lalu mencatat stok awal lewat `addStock()` tipe `Opening`, hanya untuk part baru (dijalankan dua kali tidak menggandakan stok).
- `ServiceSeeder`: servis selesai ada yang lunas dan belum lunas (`paid_at` lewat `forceFill`, tidak di `$fillable`), `mechanic_names` terisi sebagian, tiga servis milik customer demo (`user_id`).
- Model `Service`: `user_id` dan `mechanic_names` masuk `$fillable`, `paid_at` cast datetime, relasi `user()`.
- `ServicePart`: `$timestamps = false` dibuang supaya `created_at` dan `updated_at` terisi.
- `stock_movements`: `spare_part_id` jadi RESTRICT (sebelumnya CASCADE, riwayat bisa hilang), `user_id` wajib + RESTRICT, index `(spare_part_id, created_at)`. Blok isi stok awal dari data lama di migrasi dibuang.
- `workshop_settings.fuel_price` jadi `decimal(12,2)` (sebelumnya integer).
- Index `role` di `users` dibuang.
- Tes disesuaikan, assertion tidak dilemahkan: CMP-001 pakai `ServiceStatus::Pending->getLabel()` (label berubah); 3 berkas tes stok (`ServiceStockUsageTest`, `Services/ServiceStockTest`, `SpareParts/StockMovementTest`) ditambah `actingAs` karena `user_id` wajib; judul tes dan komentar bertuliskan "Menunggu Konfirmasi" dirapikan. Empat tes seeder (SED/SDR) hijau tanpa diubah.
- Catatan: index tunggal `scheduled_at` di `services` tidak ada di ERD, dibiarkan dulu.

**Tahap 1: struktur database. SELESAI (455 tes hijau)**
- [x] `users`: `role` (default customer, tidak di `$fillable`), `phone`, index `role` dibuang.
- [x] `services`: `user_id` (nullOnDelete), `mechanic_names`, `paid_at`, index `(status, scheduled_at)` dan `(user_id, scheduled_at)`, index `booking_type` dibuang, koordinat `decimal(10,7)`.
- [x] `service_parts`: timestamps.
- [x] Kolom uang semua `decimal(12,2)`. `workshop_settings` lat/lng `decimal(10,7)`.
- [x] Aturan `onDelete` semua FK sesuai ERD. `stock_movements`: index `(spare_part_id, created_at)`.
- [x] Enum `UserRole` (owner, customer), cast di model `User`. Label `ServiceStatus` nama singkat.
- [x] Teks status lama dirapikan.
- [x] Seeder: `OwnerSeeder`, `DemoCustomerSeeder`, `DatabaseSeeder`, `SparePartSeeder`, `ServiceSeeder`.
- [x] Tes yang terdampak disesuaikan, alasan dicatat.

**Tahap 2: rename kolom. SELESAI**
- [x] `services.phone` jadi `customer_phone`
- [x] `services.address` jadi `address_note`
- [x] `services.stock_synced_at` jadi `stock_deducted_at`
- [x] `stock_movements.quantity` jadi `quantity_change`
- [x] `spare_parts.location` jadi `storage_location`
- [x] Cek `cancel_reason`: dipakai untuk Ditolak dan Dibatalkan, nama dipertahankan sesuai ERD final.
- [x] Cek `ServiceMetrics`: periode pakai `scheduled_at`, tidak ada `updated_at`.

**Tahap 3: fitur. SELESAI, kecuali satu item**
- [x] Login dan register customer (email + password, `role` customer). Controller, rute, view, navbar, tes AUTH-001..020 selesai (480 tes hijau, 9 Nov 2026).
  - Catatan tes: `tests/Pest.php` login satu user acak sebelum tiap tes Feature. Tes yang butuh tamu wajib `Auth::logout()` + `app('auth')->forgetGuards()` di `beforeEach` (dipakai `CustomerAuthTest` dan `ProfilTest`).
- [x] Booking untuk customer login (`user_id` terisi, form terisi otomatis, HP akun tersimpan setelah booking pertama). 530 tes hijau (10 Nov 2026).
- [x] Riwayat (`/riwayat`, tabel ala admin dengan filter dan urut, 10 per halaman). Tes RWY-001..018.
- [x] Detail booking `/riwayat/{id}` dan Cetak Invoice `/riwayat/{id}/invoice`. RWY-011..018. Harga tampil untuk pelanggan, lihat D8.
- [x] Halaman Profil `/profil` (nama, HP opsional, email terkunci). Tes PRF-001..007.
- [x] Layout area customer, dropdown navbar, tombol Masuk dan Daftar, satu pintu login `/masuk`, menu "Kembali ke Beranda" di panel (10 Nov 2026).
- [x] Keamanan panel: `canAccessPanel()` hanya `owner`, notifikasi booking hanya ke `owner`. SEC-001..003, 458 tes hijau (9 Nov 2026). `UserFactory` punya state `owner()`: tes penerima notifikasi wajib pakai itu.
- [x] Aksi "Tandai Lunas" (hanya servis selesai, `paid_at` NULL). Selesai: `MarkServicePaid`, aksi di tabel dan halaman View, badge Pembayaran, filter, tes LNS-001..007 (503 tes hijau, 10 Nov 2026). Status Lunas dan Dibayar pada tampil di infolist (LNS-008..010, 506 tes hijau). Invoice belum memuat status lunas (opsional, bagian C). Lihat juga B: import `Notification` di `ServicesTable.php`.
- [x] Panel sparepart: `RestoreAction` dan `TrashedFilter` TIDAK dibangun (keputusan pemilik 17 Nov 2026). Kode part terhapus sudah dibebaskan otomatis (`-DEL-{id}`, HSP-002).

**Tahap 4: dokumen dan tes**
- [x] Rapikan tes: `tests/README.md` (selesai 10 Nov 2026) berisi kode modul (BKG, LOC, TIP, PRC, JSA, ANT, AUTH, SEC, LNS, RWY, PRF, SED/SDR, CMP, dst), berkas, dan aturan bisnis yang dicek.
- [x] Pindahkan 4a ke bagian 4 `PROJECT_RULES.md` setelah diterapkan, perbarui bagian 1, 3, 10 (dikerjakan 10 Nov 2026). Bagian 4a sekarang berisi keputusan desain dan glossary.
- [x] Sinkronkan ERD di Eraser dengan versi final. Perbarui laporan skripsi.

**Rapikan kode (bertahap, tiap langkah satu commit, tes hijau):** komentar singkat yang menjelaskan kenapa; logika yang menumpuk di model atau Filament dipindah ke `app/Actions` atau `app/Support`; enum dan konstanta menggantikan string mentah. Hanya bagian yang disentuh, sisanya dicatat.

**Tes wajib:** kolom baru di `services`, Tandai Lunas, akses `/admin`, notifikasi (regresi), login, booking login, riwayat, profil. Tes tampilan boleh jadi utang di bagian A.

**Pengembangan lanjutan (tulis di skripsi, tidak dikerjakan):** tabel `statuses` (kalau status perlu diatur dari panel), `customers` dengan verifikasi, histori status, tabel montir dan pivot, pembayaran non-tunai, batch/FEFO sparepart, rekap stok per periode.