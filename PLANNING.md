# PLANNING: SIM Bengkel Cak Budi Dinamo

Catatan pekerjaan yang belum selesai, bug yang diketahui, dan keputusan terbuka. Aturan dan status fitur yang sudah jadi ada di `PROJECT_RULES.md`. Terakhir diperbarui: 9 November 2026 (malam, tahap 1 selesai).

Tanda: `[ ]` belum, `[~]` sebagian, `[x]` selesai.

## A. Utang tes (ditunda atas permintaan pemilik, wajib sebelum fitur dianggap selesai)

Penomoran lanjut dari ID terakhir di tiap modul. ID di bawah sementara.

**Jasa (`tests/Feature/ServiceTypes/`)**
- [ ] JSA-007..009 seeder: mengisi 8 jasa dengan deskripsi (angka 8, bukan 5), dijalankan dua kali tidak menggandakan, foto yang sudah diunggah tidak tertimpa. Kalau berkas tes dengan angka 5 sudah tersimpan, angkanya diganti jadi 8 (penyesuaian karena seeder berubah, bukan melemahkan tes).
- [ ] JSA-010..012 tabel: kolom foto dan deskripsi ada, deskripsi panjang dipotong satu baris, jasa tanpa deskripsi dan foto tetap tampil.
- [x] Harga tampil bulat di halaman edit (`mutateFormDataBeforeFill` di `EditServiceType`). Dicek PRC-003, lulus 9 Nov.
- [ ] Hapus jasa (setelah keputusan D1).

**Halaman publik**
- [ ] Beranda memuat jasa dari DB, tampil pesan kosong tanpa jasa, tombol "Lihat semua layanan" hanya muncul kalau jasa lebih dari 6.
- [ ] `/layanan`: kosong, dan paginasi 9 per halaman.
- [ ] `/layanan/{id}`: ada (tampil nama, harga, deskripsi), dan 404 kalau id tidak ada.
- [ ] `/booking` mengembalikan 404, formulir booking tetap tampil di `/`.
- [ ] Section peta memuat tombol arah.

**Cek Booking (aturan 12)**
- [ ] Nama dan nomor cocok: servis tampil.
- [ ] Nama salah: tidak tampil, pesan gagal sama.
- [ ] Nomor salah: tidak tampil, pesan gagal sama.
- [ ] Input kosong ditolak validasi.
- [ ] Percobaan ke-6 dalam semenit ditolak.
- [ ] Servis yang di-soft-delete tidak tampil.
- [ ] Harga, total, dan alamat tidak ikut tampil.

## B. Bug dan hal yang perlu dicek

- [x] **LOC-001.** Ekspektasi tes diubah ke "Pakai lokasi saya" (label view memang berubah).
- [x] **PRC-003** (`tests/Feature/ServiceTypePriceFormTest.php`). Target dipindah ke `EditServiceType`, assertion dan ID tetap. Tes ini menangkap bug asli (harga tampil 100x), sudah diperbaiki lewat `mutateFormDataBeforeFill`.
- [x] **Upload foto jasa gagal** (500 di `upload-file`). Penyebab: `upload_tmp_dir` di php.ini Laragon kosong (`;upload_tmp_dir =`), file temp upload tidak punya path, log: `Path must not be empty`. Perbaikan: buat folder `D:/Aplikasi/laragon/tmp`, set `upload_tmp_dir` ke sana, restart Laragon. Catatan: `php artisan pail` tidak jalan di Windows (butuh `pcntl`), baca `storage/logs/laravel.log`.
- [x] Sisa rujukan ke `/booking`: link navbar dan footer dibersihkan, tes BKG-010 dan TIP-001 pindah ke `get('/')`. Link mati `/#galeri` diganti ke `/#cek-booking`.
- [ ] Ganti `id="appointment"` di `hero.blade.php` jadi `id="booking"`, dan tombol "Buat Janji" di navbar ke `/#booking` (istilah `appointment` dibuang dari kode, lihat glossary `PROJECT_RULES.md` 4a). Cek tidak ada sisa: `Get-ChildItem resources\views -Recurse -Filter *.blade.php | Select-String -Pattern 'href="/booking|#galeri|#appointment'` (harus kosong).
- [ ] Istilah "Buat Janji" atau "Booking" untuk label UI: sengaja ditunda, putuskan belakangan. Sekarang tercampur (navbar "Buat Janji", tombol kirim "Booking Sekarang", section "Cek Booking", id hero `appointment`).
- [x] `service-card.blade.php` dicek: ada, props `name`, `description`, `price`, `photoUrl`, fallback inisial dan "Hubungi kami" jalan. URL foto dibentuk accessor `ServiceType::photoUrl()` (`Storage::disk('public')->url()`). Sisa: komentar di atas berkas masih bilang "tidak ada halaman detail per layanan", perbarui.
- [x] Section galeri: berkas tidak ada, tidak ada `@include` yang rusak, link galeri sudah diganti.
- [ ] Cek Booking bergantung pada asumsi: `PhoneNumber::normalize()` mengembalikan `null` untuk input tak valid, nomor di `services.phone` sudah dalam bentuk normal (termasuk dari `BookingForm`), `scheduled_at` di-cast `datetime`, `status` dan `booking_type` di-cast ke enum. Perlu dikonfirmasi lewat kode `BookingForm` dan model `Service`.
- [ ] Kompres `public/images/bengkel.png` kalau di atas sekitar 300 KB.
- [x] `php artisan test` penuh (9 Nov 2026, akhir tahap 1): **455 lulus, 0 gagal.**

## C. Fitur belum dikerjakan

- [ ] Rekap stok per periode (Iterasi 4). Belum diputuskan dibangun atau ditulis sebagai pengembangan lanjutan skripsi.
- [ ] Promo dikelola dari panel (sisa US-07). Layanan sudah lewat menu Jasa.
- [ ] Tombol cepat konfirmasi/tolak booking di tabel servis (opsional). Wajib memanggil `startBlockerMessage()` kalau mengubah status ke `in_progress`.
- [ ] Login pelanggan, lihat bagian F.
- [ ] Cek Booking: tampilkan alasan batal (D3).
- [ ] Booking yang terisi jasa pilihan dari halaman detail (D5).
- [ ] Slug untuk URL jasa (D4).
- [ ] Sinkronkan `PROJECT_RULES.md` setiap ada perubahan beranda atau aturan.

## D. Keputusan terbuka

**D1. Hapus jasa yang sudah dipakai di `service_items`.** Kolom `service_type_id` nullable dan nama tersimpan sebagai salinan di `service_items`, jadi menghapus tidak merusak riwayat. Pilihan: boleh dihapus (sekarang `DeleteAction` polos), atau diblokir kalau sudah dipakai.

**D2. Login pelanggan. DIPUTUSKAN (8 Nov 2026): dibangun.** Email + password, tanpa OTP. Pakai tabel `users` dengan kolom `role`, bukan tabel `customers` dan bukan guard terpisah. `services.user_id` nullable (NULL = tamu). Rincian di `PROJECT_RULES.md` bagian 4a dan bagian F di bawah.

**D3. Alasan batal di Cek Booking.** `cancel_reason` mungkin catatan internal pemilik. Saat ini tidak ditampilkan. Putuskan perlu ditampilkan atau tidak.

**D4. Slug jasa.** Detail jasa sekarang `/layanan/{id}`. Slug butuh kolom baru lewat migrasi dan persetujuan.

**D5. Booking dari detail jasa.** Halaman detail tidak punya tombol booking (sengaja dihapus). Kalau mau tombol yang mengarah ke form di hero dan mengisi jasa otomatis, `BookingForm` perlu diubah.

**D6. Isi lama bagian 8 yang masih terbuka:** harga part satu `price` atau dibedakan beli dan jual; servis selesai tapi barang tidak diambil (`picked_up_at`); grace period no-show; kedaluwarsa sparepart (`expired_at`).

**D7. Data demo di staging.** Seeder demo sekarang jalan di semua environment kecuali production. Kalau nanti ada staging dan datanya tidak boleh berisi demo, putuskan cara memisahkan (misalnya env khusus atau flag di `.env`).


## E. Selesai (ringkas)

- [x] `service_types` ditambah `description` dan `photo` (migrasi lama diedit, tahap dev).
- [x] Form Jasa pindah ke halaman sendiri (`ListServiceTypes`, `CreateServiceType`, `EditServiceType`), layout dua kolom ikut sparepart. `ManageServiceTypes` dihapus.
- [x] Tabel Jasa: foto, nama, harga acuan, deskripsi dipotong satu baris.
- [x] Seeder jasa jadi 8 (3 jasa baru: nama dan harga tebakan, perlu dicek pemilik: Bubut komutator, Ganti bendix, Ganti magnetic switch).
- [x] Beranda membaca jasa dari DB, halaman `/layanan` dan `/layanan/{id}`.
- [x] Halaman `/booking` dihapus (formulir ada di hero).
- [x] Section testimoni dicabut dari beranda (berkas masih ada).
- [x] Section Cek Booking ditambah.
- [x] Section peta disederhanakan: peta + tombol arah.
- [x] Foto hero diganti `public/images/bengkel.png`.
- [x] Tes JSA-001..006 lulus.
- [x] Upload foto jasa beres (php.ini), link navbar dan footer dibersihkan, 4 tes usang disesuaikan, harga edit tampil bulat.

## G. Target sebelum bimbingan (dicatat 9 Nov 2026)

Daftar dari pemilik. Per 9 Nov 2026 (malam): tahap 1 (struktur database, enum, seeder) selesai. Rute customer dan fitur login belum ada. Bagian F di bawah adalah rinciannya.

- [ ] Login dan register customer (email + password). Rinci: bagian F tahap 3.
- [ ] Booking untuk customer yang login (form terisi otomatis, `services.user_id` terisi).
- [ ] Riwayat booking untuk customer yang login ("Riwayat Saya").
- [~] ERD diimplementasikan ke migrasi (bagian F). Status 9 Nov 2026 (malam): tahap 1 (struktur database) selesai dan semua tes hijau. Sisa: tahap 2 (rename kolom), tahap 3 (fitur), tahap 4 (dokumen).
- [ ] Saran dospem yang sudah masuk rencana ERD (misal `mechanic_names`, `paid_at`) diimplementasikan, lalu siapkan daftar untuk ditunjukkan saat bimbingan.
- [ ] Rapikan dan organisasi tes: tiap berkas dan ID tes punya penjelasan satu baris "mengetes apa". Usul: tabel `tests/README.md` berisi kode modul (BKG, LOC, TIP, PRC, JSA, ANT, dst), berkas, dan aturan bisnis yang dicek.
- [ ] Perbarui laporan skripsi: banyak perubahan (landing, jasa, ERD, login customer, antrian, Cek Booking).
- [ ] Notifikasi WhatsApp ke pelanggan: opsional, dikerjakan belakangan kalau sempat.
- [ ] Siapkan bahan bimbingan: demo (beranda, `/layanan`, Cek Booking, upload foto jasa, login customer), angka tes, daftar migrasi yang berubah, keputusan yang butuh jawaban (D1, D3).

## F. Rencana perubahan database dan fitur turunannya (diperbarui 9 Nov 2026, malam)

ERD final dan seluruh keputusannya ada di `PROJECT_RULES.md` bagian 4a. Cara: edit migrasi lama + `migrate:fresh --seed`. Branch kerja: `feat/erd-baru`. Baseline sebelum mulai: commit dengan 455 tes hijau (di `main`).

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

**Tahap 2: rename kolom (satu rename per commit, cari-ganti di `app`, `resources`, `database`, `tests`, lalu `php artisan test`)**
- [ ] `services.phone` jadi `customer_phone`
- [ ] `services.address` jadi `address_note`
- [ ] `services.stock_synced_at` jadi `stock_deducted_at`
- [ ] `stock_movements.quantity` jadi `quantity_change`
- [ ] `spare_parts.location` jadi `storage_location`
- [ ] Cek `cancel_reason`: `git grep -n "cancel_reason" -- app`. Ganti jadi `status_note` hanya kalau juga dipakai untuk penolakan.
- [ ] Cek `ServiceMetrics`: `git grep -n "updated_at" -- app`. Periode servis selesai memakai `scheduled_at`, bukan `updated_at`.

**Tahap 3: fitur**
- [ ] Login dan register customer (email + password, `role` customer).
- [ ] Booking untuk customer login (`user_id` terisi, form terisi otomatis).
- [ ] Riwayat Saya.
- [ ] Keamanan panel: `canAccessPanel()` hanya `owner`, notifikasi booking hanya ke `owner`.
- [ ] Aksi "Tandai Lunas" (hanya servis selesai, `paid_at` NULL), badge Lunas/Belum Lunas, filter, invoice.
- [ ] Panel sparepart: `RestoreAction` dan `TrashedFilter` (cek dengan `git grep -n "TrashedFilter\|RestoreAction" -- app`), pesan validasi kode yang dipakai part yang diarsipkan.

**Tahap 4: dokumen dan tes**
- [ ] Rapikan tes: `tests/README.md` berisi kode modul (BKG, LOC, TIP, PRC, JSA, ANT, dst), berkas, dan aturan bisnis yang dicek.
- [ ] Pindahkan 4a ke bagian 4 `PROJECT_RULES.md` setelah diterapkan, perbarui bagian 1, 3, 10.
- [ ] Sinkronkan ERD di Eraser dengan versi final. Perbarui laporan skripsi.

**Rapikan kode (bertahap, tiap langkah satu commit, tes hijau):** komentar singkat yang menjelaskan kenapa; logika yang menumpuk di model atau Filament dipindah ke `app/Actions` atau `app/Support`; enum dan konstanta menggantikan string mentah. Hanya bagian yang disentuh, sisanya dicatat.

**Tes wajib:** kolom baru di `services`, Tandai Lunas, akses `/admin`, notifikasi (regresi), login, booking login, riwayat. Tes tampilan boleh jadi utang di bagian A.

**Pengembangan lanjutan (tulis di skripsi, tidak dikerjakan):** tabel `statuses` (kalau status perlu diatur dari panel), `customers` dengan verifikasi, histori status, tabel montir dan pivot, pembayaran non-tunai, batch/FEFO sparepart, rekap stok per periode.