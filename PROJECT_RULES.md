# PROJECT_RULES: SIM Bengkel Cak Budi Dinamo

Konteks proyek skripsi untuk AI. Baca seluruh file sebelum menjawab. Bagian "Aturan wajib untuk AI" mengikat. Jika file ini bertentangan dengan kode di konteks, kode yang benar: sebutkan pertentangannya, jangan diam-diam memilih.

Daftar pekerjaan yang belum selesai, bug yang diketahui, dan keputusan terbuka ada di `PLANNING.md`. Baca juga file itu kalau tugasnya menyangkut pekerjaan lanjutan. Terakhir disinkronkan: 10 November 2026 (530 tes lulus).

## 1. Tujuan sistem

Aplikasi web bengkel Cak Budi Dinamo (Pasuruan): stok sparepart, servis, booking pelanggan, dan laporan.

Dua peran, satu tabel `users` (kolom `role`, enum `UserRole`):
- **owner** (pemilik): mengelola semuanya lewat panel Filament di `/admin`.
- **customer**: daftar dan login dengan email + password. Booking dengan nama dan HP terisi otomatis, melihat Riwayat Booking, mencetak invoice servis yang sudah selesai, dan mengubah Profil.
- **Tamu** (tanpa login) tetap bisa booking dan memakai Cek Status Booking.

Satu pintu login: `/masuk` untuk owner dan customer (owner diarahkan ke `/admin`, customer ke beranda). Login bawaan Filament sudah dicabut.

Halaman dan rute:
- Publik: beranda `/` (formulir booking di hero, plus Cek Status Booking), `/layanan` (daftar jasa), `/layanan/{id}` (detail jasa), `/daftar`, `/masuk`. Halaman `/booking` sudah dihapus.
- Area customer (wajib login, owner dilempar ke `/admin`): `/riwayat`, `/riwayat/{id}`, `/riwayat/{id}/invoice`, `/profil`. Keluar lewat `POST /keluar`.

Metode: prototype, 4 iterasi. Pengujian: Black Box.

## 2. Stack (dari composer.json)

- PHP ^8.3, Laravel ^13.17, MySQL
- **Filament ~5.0** (panel `admin`, terpasang v5.9.0). Halaman publik dan area customer: Blade + Livewire (`App\Livewire\BookingForm`, `App\Livewire\BookingStatusCheck`). Alpine bawaan Livewire dipakai di Blade (dropdown navbar, tombol lihat sandi).
- Paket: `barryvdh/laravel-dompdf` (invoice PDF), `laravel-lang/common` (terjemahan), `malzariey/filament-daterangepicker-filter`.
- Dev: Pest 4, Pint, Pail. Plugin Livewire untuk Pest **tidak terpasang**: tes Livewire memakai `Livewire\Livewire::test()`. `php artisan pail` tidak jalan di Windows, baca `storage/logs/laravel.log`.
- Panel: tema Vite kustom, warna utama Blue, `darkMode(false)`, **tanpa `login()`** (belum login diarahkan ke rute `login` = `/masuk`), `databaseNotifications()`, `databaseTransactions()`, menu pengguna punya "Kembali ke Beranda". `canAccessPanel()` hanya `owner`.
- Grup navigasi: Operasional, Katalog, Pengaturan.
- Locale `id`. Nama tabel/kolom/model Inggris, label ke pengguna Indonesia.

## 3. Status fitur (berdasarkan kode)

**Iterasi 1: Sparepart + login**
- [x] US-01 Login pemilik (lewat `/masuk`, owner langsung ke `/admin`)
- [x] US-02 CRUD sparepart (SoftDeletes, kode unik, foto, kategori)
- [x] US-03 Pencarian + filter kategori (`SparePartsTable.php`: kolom kode, nama, kategori, lokasi bisa dicari; filter kategori multi-pilih; tes SSP-001..003, FSP-001..003)
- [x] US-04 Penanda dan urutan stok menipis (`SparePartsTable.php`: baris merah saat stok <= min_stock, urut paling kritis di atas; tes LSP-001..003)

**Iterasi 2: Servis**
- [x] US-05 Kelola servis: buat/ubah, status, jasa (service_items), part (service_parts), filter, invoice PDF, kurangi stok saat selesai, antrian (aturan 11), Tandai Lunas (aturan 13)

**Iterasi 3: Sisi publik**
- [x] US-09 Halaman publik `/`: section hero (berisi formulir booking), layanan (dari DB), benefit, cek booking, peta, navbar, footer, tombol WhatsApp. Section testimoni sudah dicabut dari beranda (datanya dummy dan tidak ada fitur ulasan).
- [x] US-10 Booking: formulir Livewire `BookingForm` di hero beranda (`CreateBooking`, slot jam, anti-ganda, notifikasi ke pemilik). Customer login: nama dan HP terisi dari akun, `user_id` tersimpan.
- [x] US-06 Pemilik konfirmasi/tolak booking (lewat form ubah servis; tombol cepat di tabel belum ada, opsional)
- [~] US-07 Kelola info layanan dan promo dari panel: **layanan sudah** (menu Jasa: nama, harga acuan, deskripsi, foto; beranda, `/layanan`, `/layanan/{id}` membaca dari DB). **Promo belum ada.**

**Iterasi 4: Laporan**
- [x] US-08 Dashboard: KPI, tren, top part, top layanan, tipe, status (`app/Support/Dashboard/*`, `app/Filament/Widgets/*`)
- [ ] Rekap stok per periode: belum ada. Yang ada baru riwayat per sparepart (`StockMovementsRelationManager`), bukan rekap seluruh stok per periode.

**Akun customer (tambahan di luar PRD awal, selesai 9 sampai 10 Nov 2026)**
- [x] Daftar dan login (`RegisterController`, `LoginController`, throttle daftar 10 per menit, masuk 5 per menit). Tes AUTH-001..020.
- [x] Keamanan panel (hanya owner), notifikasi booking hanya ke owner. Tes SEC-001..003.
- [x] Riwayat Booking, detail, Cetak Invoice (`RiwayatController`). Tes RWY-001..018.
- [x] Profil (`ProfilController`). Tes PRF-001..007.
- [x] Tandai Lunas di panel (`MarkServicePaid`). Tes LNS-001..010.
- [ ] Panel sparepart: `RestoreAction` dan `TrashedFilter` (lihat `PLANNING.md`).

**Fitur tambahan di luar PRD awal (sudah ada):** home service dengan peta, jarak udara (Haversine) dan ongkos transport; riwayat stok (`stock_movements`) per sparepart di halaman ubah sparepart; master kategori dan jenis layanan; pengaturan bengkel (`workshop_settings`); kedaluwarsa booking otomatis; halaman `JarakOngkos`; antrian servis (aturan 11); Cek Status Booking di beranda (aturan 12); halaman publik daftar dan detail layanan; akun customer (di atas).

**Sedang dikerjakan:** (kosong)

**Catatan perubahan**
- 5 Nov 2026: `service_types` ditambah `description` dan `photo`. Form Jasa pindah dari modal ke halaman sendiri. Halaman `/booking` dihapus, testimoni dicabut, Cek Booking ditambah, peta disederhanakan. ORS dibuang, jarak hanya Haversine.
- 8 Nov 2026: ERD direview dan disepakati.
- 9 Nov 2026: ERD diterapkan ke migrasi (tahap 1 dan 2), tanpa tabel `statuses`. Login dan register customer, keamanan panel. Seeder jasa jadi 7.
- 10 Nov 2026: area customer (layout, dropdown navbar, Riwayat, detail, invoice, Profil), booking login, satu pintu login `/masuk`, Tandai Lunas. 530 tes lulus. Bagian 4 dan 4a disinkronkan dengan migrasi.

## 4. Model data (sumber kebenaran: file migrasi di konteks)

ERD final, sudah diterapkan ke migrasi (edit migrasi lama + `migrate:fresh --seed`, tahap dev). Kolom bernilai tetap (`status`, `role`, `booking_type`, `type`, `reason`) disimpan `varchar` dan dijaga enum PHP + cast di model. Kolom uang `decimal(12,2)`. Koordinat `decimal(10,7)`.

| Tabel | Isi penting |
| --- | --- |
| `users` | name, email (unik, dipakai login), email_verified_at, password, `role` (enum `UserRole`: owner, customer; default customer; **tidak di `$fillable`**), `phone` (nullable, dinormalkan; profil saja), remember_token |
| `categories` | name, description |
| `spare_parts` | code (unik), name, photo, category_id (nullable, SET NULL), stock (cache, diubah hanya lewat `addStock()`), min_stock (default 1), `storage_location`, price, soft delete (= arsip) |
| `services` | `user_id` (nullable, SET NULL; NULL = tamu atau walk-in), customer_name, `customer_phone` (salinan, dinormalkan), complaint, scheduled_at, status (default `pending`), booking_type, latitude, longitude, `address_note` (patokan lokasi), distance_km `decimal(8,3)`, fuel_price (nullable), transport_fee (default 0), cancel_reason, `stock_deducted_at`, `mechanic_names` (catatan bebas, bukan relasi), `paid_at` (NULL = belum lunas), soft delete. Index `(status, scheduled_at)` dan `(user_id, scheduled_at)`. **Tidak ada** `source`, `labor_cost`, `notes`, `completed_at`. |
| `service_types` | name, default_price, description (text, nullable), photo (string, nullable; disk `public`, folder `service-types`) |
| `service_items` | service_id (CASCADE), service_type_id (nullable, SET NULL), name (salinan nama jasa), price (jasa per baris, salinan, boleh diubah) |
| `service_parts` | service_id (CASCADE), spare_part_id (RESTRICT), quantity, unit_price (salinan dari `spare_parts.price`, tidak bisa diubah). Unik (service_id, spare_part_id). Punya timestamps. |
| `stock_movements` | spare_part_id (RESTRICT), type, `quantity_change` (bertanda: + masuk, - keluar), stock_after, reason, note (nullable), service_id (nullable, SET NULL), user_id (wajib, RESTRICT). Index `(spare_part_id, created_at)`. |
| `workshop_settings` | latitude, longitude, max_distance_km, fuel_price |
| `notifications`, `cache`, `jobs` | bawaan Laravel |

Relasi utama: `Service hasMany ServiceItem`, `Service hasMany ServicePart`, `Service belongsTo User`, `SparePart hasMany StockMovement`, `SparePart belongsTo Category`, `ServicePart belongsTo SparePart` (withTrashed).

Model `Service`:
- `$fillable`: user_id, customer_name, customer_phone, complaint, scheduled_at, status, cancel_reason, booking_type, latitude, longitude, address_note, distance_km, fuel_price, transport_fee, mechanic_names. `paid_at` dan `stock_deducted_at` **tidak** di `$fillable` (diisi `forceFill`).
- Cast: `status` (enum), `booking_type` (enum), `scheduled_at`, `stock_deducted_at`, `paid_at` (datetime), latitude dan longitude dan distance_km (float), fuel_price dan transport_fee (integer, lihat `PLANNING.md` B).
- Accessor: `labor_total`, `parts_total`, `grand_total`. Method: `syncStock()`, `startBlockerMessage()`, `invoiceNumber()`, `invoicePdf()`, `withDistance()`, `transportFee()`.

Model `ServiceType`: `$fillable` = name, default_price, description, photo. Cast `default_price` = `decimal:2`. Accessor `photoUrl()` dan `photo_url` (URL foto di disk `public`, null kalau belum ada foto).

Enum:
- `ServiceStatus` (implements HasColor, HasIcon, HasLabel). Label dan warna: pending Menunggu (gray), confirmed Dikonfirmasi (info), in_progress Dikerjakan (warning), completed Selesai (success), cancelled Dibatalkan (gray), rejected Ditolak (danger), expired Kedaluwarsa (gray). Method `activeValues()`, `isFinal()`, `allowedNext()`, `canMoveTo()`.
- `UserRole` (owner, customer), `BookingType` (workshop, home_service), `StockMovementType` (opening, purchase, usage, correction), `StockReason`.

Config yang dipakai kode tapi tidak ikut konteks: `config/booking.php`, `config/transport.php`.

Helper: `App\Support\PhoneNumber::normalize(?string)` mengubah `+62...` dan `62...` jadi `0...`, membuang non-angka. **Input tak valid menghasilkan string kosong, bukan `null`** (hanya `null` masuk yang menghasilkan `null`). Kode yang memakai hasilnya wajib menangani string kosong.

## 4a. Keputusan desain ERD dan glossary (diterapkan 9 sampai 10 Nov 2026)

**Keputusan: tabel `statuses` TIDAK dibuat.** Status tetap kolom `services.status` (varchar) yang dicast ke enum `ServiceStatus`. Alasan: 7 status tetap, logikanya ada di kode (antrian, kedaluwarsa, lunas), jadi tabel kedua hanya menggandakan enum. Jangan membuatnya tanpa persetujuan. Tabel `statuses` dicatat sebagai pengembangan lanjutan.

**Aturan `onDelete`**

| Relasi | Aturan |
| --- | --- |
| `services.user_id` | SET NULL |
| `spare_parts.category_id` | SET NULL |
| `stock_movements.spare_part_id` | RESTRICT |
| `stock_movements.service_id` | SET NULL |
| `stock_movements.user_id` | RESTRICT |
| `service_items.service_id` | CASCADE |
| `service_items.service_type_id` | SET NULL |
| `service_parts.service_id` | CASCADE |
| `service_parts.spare_part_id` | RESTRICT |

**Rename kolom (selesai):** `services.phone` jadi `customer_phone`, `services.address` jadi `address_note`, `services.stock_synced_at` jadi `stock_deducted_at`, `stock_movements.quantity` jadi `quantity_change`, `spare_parts.location` jadi `storage_location`. `cancel_reason` tidak diganti nama: dipakai untuk Ditolak dan Dibatalkan. Tidak ada kolom `completed_at`: periode servis selesai di laporan memakai `scheduled_at`, bukan `updated_at` (karena "Tandai Lunas" ikut mengubah `updated_at`).

**Index:** tidak ada index di `role` dan `booking_type` (hanya 2 nilai). Index `customer_phone` sengaja ditunda. Index tunggal `scheduled_at` di `services` tidak ada di ERD tapi dibiarkan.

**Seeder**
- Data wajib, jalan di semua environment: `OwnerSeeder` (akun pemilik, isi lewat `OWNER_EMAIL` dan `OWNER_PASSWORD`), `WorkshopSettingSeeder`, `CategorySeeder`, `ServiceTypeSeeder` (7 jasa).
- Data demo, jalan di semua environment **kecuali production**: `DemoCustomerSeeder`, `SparePartSeeder`, `ServiceSeeder` (servis di tiap status, selesai lunas dan belum lunas, `mechanic_names` terisi sebagian, tiga servis milik customer demo). Alasan: empat tes seeder (SED/SDR) butuh data demo. Lihat `PLANNING.md` D7.
- Seeder yang memakai `addStock()` atau `syncStock()` wajib memanggil `Auth::setUser(owner)` dulu (`stock_movements.user_id` wajib dan diambil dari `auth()->id()`).
- `WithoutModelEvents` tidak dipakai di `DatabaseSeeder` dan `WorkshopSettingSeeder`, karena event `Service::saving` menghitung ongkos dan menyalin harga BBM untuk Home Service.
- `SparePartSeeder` membuat part dengan stok 0 lalu mencatat stok awal lewat `addStock()` tipe `Opening`, hanya untuk part baru (dijalankan dua kali tidak menggandakan stok).
- Akun lama `test@example.com` sudah diganti akun pemilik.

**Glossary (satu istilah, satu arti)**

| Konsep | Istilah di kode dan DB | Teks di layar |
| --- | --- | --- |
| Satu pekerjaan servis | `Service` (tabel `services`) | Servis |
| Aksi pelanggan memesan | `booking` | "Booking" atau "Buat Janji" (belum diputuskan) |
| Jenis layanan (katalog) | `ServiceType` (tabel `service_types`) | Jasa |
| Booking belum dikonfirmasi | status `pending` | Menunggu |
| Pelanggan yang punya akun | `User` dengan `role` = `customer` | Pelanggan |

Istilah `appointment` tidak dipakai di kode. `id="appointment"` di `hero.blade.php` harus diganti `booking` (belum dikerjakan, lihat `PLANNING.md` B), dan link ke form booking memakai `/#booking`.

Tidak dibuat (pengembangan lanjutan): tabel `statuses`, tabel `customers`, OTP, tabel `payments`, `service_status_histories`, tabel montir dan pivot, batch sparepart.

## 5. Aturan bisnis (sesuai kode saat ini)

1. Stok tidak boleh negatif: unsigned di DB dan `SparePart::addStock()` menolak hasil di bawah 0. Semua perubahan stok lewat `addStock()` (mencatat `stock_movements`, memakai `lockForUpdate`).
2. **Stok servis berkurang saat status menjadi `completed`**, lewat `Service::syncStock()`, sekali saja (dijaga `stock_deducted_at`). Stok kurang membatalkan penyelesaian dan menampilkan notifikasi. Ini menggantikan aturan lama "berkurang saat part ditambahkan".
3. Alur status (`ServiceStatus::allowedNext`): pending → confirmed/rejected/cancelled; confirmed → in_progress/cancelled; in_progress → completed/cancelled. `completed`, `cancelled`, `rejected`, `expired` adalah final. Status aktif: pending, confirmed, in_progress.
4. Satu nomor HP hanya boleh punya satu servis aktif. Satu slot jam hanya untuk satu booking (dikunci dengan `Cache::lock`).
5. Booking pending otomatis menjadi `expired` lewat `bookings:expire` (batas waktu di `config/booking.php`).
6. `unit_price` di `service_parts` disalin dari `spare_parts.price` saat part dipakai, dan tidak bisa diubah: field harga di form read-only dan hook model `ServicePart` (`creating` dan `updating`) mengabaikan input harga. Harga jasa (`service_items.price`) tetap boleh diubah.
7. Total servis: `grand_total = labor_total + parts_total + transport_fee`. `labor_total` = jumlah `service_items.price`. `parts_total` = jumlah `quantity * unit_price`. Catatan: kolom Total di tabel admin belum menghitung `transport_fee` (lihat `PLANNING.md` B).
8. Ongkos transport hanya untuk `home_service`: dihitung dari jarak udara (Haversine), harga BBM, dan `config/transport.*`. Di luar `max_distance_km` booking ditolak.
9. Hapus sparepart memakai soft delete (arsip). Kode part diberi akhiran `-DEL-{id}` agar kode bisa dipakai ulang. **Pertentangan yang belum diselesaikan:** ERD final menyebut kode tetap terkunci walau diarsipkan, dan `PLANNING.md` masih menyisakan pesan validasi untuk kode yang dipakai part terarsip. Cek kode `SparePart` sebelum mengandalkan salah satunya, dan sebutkan pertentangannya.
10. Notifikasi booking baru dikirim lewat database notification **hanya ke `User` ber-`role` owner** (bukan semua user). Tes penerima notifikasi memakai state `UserFactory::owner()`.
11. Antrian servis: hanya satu servis `in_progress` sekali waktu. Servis hanya boleh pindah ke `in_progress` (atau dibuat langsung berstatus itu) kalau tidak ada servis `in_progress` lain dan tidak ada servis `confirmed` yang antre lebih dulu. Urutan antre: `scheduled_at`, lalu `created_at`, lalu `id`, berlaku sama untuk booking aplikasi dan walk-in. Booking `pending` tidak menghalangi. Servis yang dibatalkan, diselesaikan, atau dikonfirmasi/ditolak tidak terkena aturan ini. Pengecekan lewat `Service::startBlockerMessage()`. Kalau terblokir, form buat dan ubah servis menampilkan modal `queueBlocked`. Tes: ANT-001 sampai ANT-012 di `tests/Feature/Services/ServiceQueueTest.php`. Fitur apa pun yang mengubah status ke `in_progress` (termasuk tombol cepat di tabel) wajib memanggil `startBlockerMessage()`.
12. Cek Status Booking (`App\Livewire\BookingStatusCheck`, section `cek-booking` di beranda, untuk tamu): pelanggan memasukkan nama dan nomor HP. Servis hanya ditemukan kalau **keduanya cocok** (nomor dinormalkan lewat `PhoneNumber::normalize`, nama dibandingkan tanpa beda huruf besar kecil dan spasi tepi). Pesan gagal sama untuk nomor salah dan nama salah. Batas 5 percobaan per menit per IP (`RateLimiter`). Maksimal 3 servis terbaru ditampilkan. Yang tampil: status, tipe, jadwal, keluhan. **Tidak** tampil: harga, total, alamat, alasan batal. Servis yang sudah di-soft-delete tidak tampil. Aturan ini hanya untuk Cek Booking publik, bukan untuk area customer (aturan 16).
13. **Pembayaran tunai.** Aksi "Tandai Lunas" (`App\Actions\MarkServicePaid`) hanya untuk servis `completed` dengan `paid_at` NULL, mengisi `paid_at`. Tersedia di tabel dan halaman View servis, terpisah dari form ubah (servis `completed` terkunci). Tidak ada pembatalan lunas. Badge Pembayaran (Lunas, Belum Lunas) tampil di tabel, filter, infolist, Riwayat, dan detail Riwayat. Invoice PDF belum memuat status lunas. Tanpa tabel `payments` dan tanpa payment gateway. DB tidak menjaga "hanya untuk selesai", jadi dijaga di aksi dan tes.
14. **Akses dan login.** Email + password, nomor HP bukan kredensial, tanpa OTP. Satu pintu login `/masuk` (rute `login`). Owner setelah login ke `/admin`, customer ke beranda atau tujuan sebelumnya. Customer tidak boleh masuk `/admin` (`canAccessPanel()` hanya `owner`, hasilnya 403). `role` tidak boleh diubah lewat input form. Pendaftaran selalu membuat `customer` (default DB) dengan `phone` kosong.
15. **Booking dari akun.** `user_id` terisi dari `Auth::id()`. Nama dan HP terisi dari akun tapi bisa diubah (booking untuk orang lain) dan tetap disalin ke `services`. Nomor HP dinormalkan lewat `PhoneNumber::normalize` sebelum disimpan, termasuk `users.phone`. Setelah booking pertama berhasil, kalau `users.phone` masih kosong maka nomor disimpan ke akun (`forceFill`). Booking tamu lama tidak digabung ke akun baru: riwayat akun mulai kosong. Tamu tetap bisa booking dengan `user_id` NULL.
16. **Area customer** (`RiwayatController`, `ProfilController`):
    - Riwayat hanya memuat servis milik `Auth::id()`. Servis orang lain atau yang sudah di-soft-delete: 404. Owner yang membuka rute ini dilempar ke `/admin`.
    - Tabel mengikuti tabel admin tanpa kolom Pelanggan dan No. HP: No, Tanggal & Jam, Tipe, Status, Pembayaran, Total, Aksi. Filter Tipe, Status, Pembayaran. Urut Tanggal atau Total. Per halaman 10, 25, 50 (nilai lain jatuh ke 10). Total di sini = jasa + part + transport.
    - Detail mengikuti infolist admin: Data Servis, Jasa, Spare Part, Ringkasan biaya. Harga tampil untuk pelanggan login (keputusan menunggu persetujuan pemilik, `PLANNING.md` D8). **Tidak tampil:** `cancel_reason`, `mechanic_names`, catatan internal, data stok.
    - Cetak Invoice (`/riwayat/{id}/invoice`): PDF inline, hanya servis `completed` milik sendiri, selain itu 404.
    - Profil: nama (wajib, maks 255) dan nomor HP (opsional, dinormalkan, format `08` + 8 sampai 12 angka). Email tampil terkunci dan tidak diterima dari input. Nomor HP wajib baru saat booking.
17. **Sparepart diarsipkan, bukan dihapus.** Soft delete = arsip. Butuh lagi: pulihkan lewat `RestoreAction` dan `TrashedFilter` di panel (cek apakah sudah ada dengan `git grep`), jangan buat baru. Kalau kode ditolak karena dipakai part yang diarsipkan, tampilkan pesan yang menyarankan memulihkan (lihat aturan 9).
18. **Kedaluwarsa atau rusak** ditangani lewat Ubah Stok dengan alasan "Kedaluwarsa" atau "Rusak/Karatan", tanpa kolom `expired_at`. Batch dan FEFO = pengembangan lanjutan.
19. **Stok awal seeder** harus lewat `addStock()` dengan user pemilik (`stock_movements.user_id` wajib terisi).

## 6. UX dan tampilan

- Antarmuka sederhana untuk pemilik yang terbiasa kerja manual, bisa dipakai di ponsel.
- Form tambah/ubah: tombol "Simpan" dan "Kembali", tanpa "buat & buat lainnya" (`$canCreateAnother = false`), setelah simpan kembali ke daftar.
- Form Jasa dan Sparepart memakai layout dua kolom: kiri (2/3) satu card informasi, kanan (1/3) card foto. Form Jasa: baris pertama nama jasa dan harga acuan sejajar, baris kedua deskripsi selebar card. Tambah dan ubah Jasa lewat **halaman sendiri**, bukan modal.
- Tabel Jasa: kolom foto, nama, harga acuan, deskripsi (dipotong satu baris, tooltip menampilkan teks penuh).
- Menyelesaikan servis meminta konfirmasi modal ("Stok akan dikurangi dan servis tidak bisa diubah lagi").
- Harga format Rupiah.
- Halaman publik mobile-first. Tombol publik boleh berlabel "Booking Servis".
- **Area customer:** satu tempat dengan beranda, jangan terasa seperti aplikasi lain. Layout `layouts/customer.blade.php` memakai navbar dan footer beranda (`sections.navbar`, `sections.footer`, `sections.wa-float`) plus sidebar kiri (avatar huruf awal, nama, label "Pelanggan", menu Riwayat Booking dan Profil). Sidebar: kartu putih, garis tipis `border-neutral-200`, tanpa shadow, tinggi minimal setinggi layar; di HP jadi baris menu yang bisa digeser. Menu aktif `bg-red-50 text-red-600`. Menu tidak memuat "Booking Baru" dan "Keluar" (booking langsung ke beranda, Keluar ada di dropdown navbar).
- Tabel dan detail area customer mengikuti pola Filament (kartu putih, header abu muda, baris bergaris, hover) tapi bergaya brand beranda (merah, sudut `rounded-[10px]`, font beranda). Warna badge dibaca dari `ServiceStatus::getColor()` lewat komponen `x-status-badge` (`gray`, `info`, `warning`, `success`, `danger`). Paginasi memakai `pagination.filament`.
- Navbar login: desktop berupa avatar huruf awal + nama depan + panah, klik buka dropdown (nama dan email, menu, Keluar; tutup saat klik luar atau Esc). HP: daftar biasa di menu lipat, tanpa dropdown. Owner melihat menu panel (Dashboard, Servis, Sparepart, Jasa, Kategori, Jarak Ongkos) yang langsung ke halaman panel, tanpa Riwayat dan Profil. Customer melihat Riwayat Booking dan Profil. Tamu melihat tombol Masuk (outline) dan Daftar (solid gelap), tinggi `h-11`, huruf kapital lewat CSS, di HP dua kolom selebar menu. Tautan internal navbar pakai `route(..., absolute: false)` supaya relatif.
- Form Profil: layout kartu lebar penuh (header, dua kolom isian, footer Batal dan Simpan). Email tampil dengan gaya disabled (`disabled:bg-neutral-100 disabled:text-neutral-500`) dan ikon gembok.
- Kelas Tailwind baru butuh `npm run dev` jalan atau `npm run build`.

## 7. Di luar scope

Pembayaran non-tunai dan payment gateway, OTP (SMS atau WhatsApp), tabel `customers`, histori perubahan status, akun dan role montir atau kasir, aplikasi mobile native, notifikasi WhatsApp/SMS otomatis (tombol tautan WhatsApp boleh), pemulihan data terhapus lewat antarmuka.

## 8. Belum diputuskan

Rincian dan usulan ada di `PLANNING.md`. Ringkasan:

- Harga part: satu `price`, belum dibedakan beli dan jual.
- Jasa yang sudah dipakai di `service_items`: boleh dihapus atau diblokir.
- Alasan batal (`cancel_reason`) ditampilkan ke pelanggan (Cek Booking dan area customer) atau tidak.
- **Harga tampil untuk pelanggan login** (Riwayat dan detail): sudah dibangun atas permintaan pemilik, menunggu persetujuan pemilik bengkel dan dospem (D8).
- Halaman detail jasa: perlu slug atau tetap memakai id; perlu tombol booking yang mengisi jasa otomatis atau tidak.
- Promo dikelola dari panel (sisa US-07) atau tetap statis.
- Label UI "Buat Janji" atau "Booking".
- Servis selesai tapi barang tidak diambil (`picked_up_at`) dan konfirmasi pelanggan akan datang: ditunda, maksudnya belum jelas.
- Grace period no-show: sementara pemilik membatalkan manual.
- Kedaluwarsa sparepart (`expired_at`): opsional.
- Rekap stok per periode: belum diputuskan dibangun atau ditulis sebagai pengembangan lanjutan.

Sudah diputuskan (jangan dibuka lagi tanpa alasan baru): login customer dibangun dengan email + password, tanpa OTP dan tanpa tabel `customers`, satu pintu login `/masuk` (lihat aturan 14). Tamu tetap melihat status lewat Cek Booking (aturan 12). Pembayaran cuma tunai, dicatat lewat `paid_at` (aturan 13). Tanpa tabel `statuses`. Testimoni dicabut. Alamat dan jam buka tidak ditampilkan di section peta.

## 9. Aturan wajib untuk AI

- Pakai sintaks **Filament 5**. Jika ragu soal API Filament 5, katakan ragu. Jangan mengarang kelas atau namespace, dan jangan memakai sintaks v3 atau v4 tanpa memastikan.
- Jangan menebak nama tabel atau kolom. Ambil dari migrasi dan model di konteks. Info kurang: tanya dulu.
- Jangan mengarang data bisnis (alamat, jam buka, harga, testimoni, nama jasa). Data yang belum diketahui ditanyakan atau dibiarkan kosong.
- Semua perubahan stok harus lewat `SparePart::addStock()` atau `Service::syncStock()`. Jangan `update` kolom `stock` langsung.
- Logika status mengikuti `ServiceStatus::allowedNext()`. Jangan menambah status atau transisi tanpa persetujuan. Status dirujuk lewat enum `ServiceStatus`, tidak ada tabel `statuses`.
- Jangan mengubah versi Laravel/Filament, jangan menyarankan upgrade, jangan menambah paket tanpa bertanya.
- Jangan mengubah struktur tabel yang sudah ada tanpa persetujuan. Perubahan skema lewat migrasi baru. Pengecualian: selama proyek masih tahap dev (belum ada data produksi), pemilik boleh memutuskan mengedit migrasi lama lalu `php artisan migrate:fresh --seed`. Tanya dulu dan tunggu persetujuan, jangan memutuskan sendiri.
- Ikuti pola `app/Filament/Resources` (folder `Pages`, `Schemas`, `Tables`, `RelationManagers`, `Actions`), pola `app/Support` dan `app/Actions`.
- Pesan error dan label untuk pengguna berbahasa Indonesia.
- Jawaban kode: file lengkap dengan path-nya. Perubahan kecil: bagian yang berubah plus 1-2 baris konteks.
- Jangan menghapus komentar yang sudah ada di file yang diedit, kecuali pemilik minta merapikan komentar. Komentar menjelaskan kenapa, bukan apa; singkat dan berbahasa Indonesia.
- Setiap perubahan atau penambahan kode wajib disertai Feature Test (Pest), dalam jawaban yang sama. Tes baru untuk perilaku baru, dan tes lama yang terdampak ikut disesuaikan. Jangan memberi kode tanpa tes. (Kalau pemilik secara eksplisit menunda tes, catat sebagai utang di `PLANNING.md`, jangan dilupakan.)
- Jangan membuat tes lulus dengan melemahkan assertion atau menghapus tes. Kalau aturan bisnis berubah dan tes lama jadi tidak relevan, katakan dengan jelas tes mana yang diganti dan alasannya.
- Penamaan mengikuti glossary di bagian 4a (booking = aksi pelanggan, service = datanya, jangan pakai istilah `appointment`). Pakai nama kolom final dari bagian 4.
- Kolom jenis (`role`, `booking_type`, `type`, `reason`) memakai `varchar` + enum PHP. Jangan membuat enum DB atau tabel master tanpa persetujuan.
- Data internal pemilik (`cancel_reason`, `mechanic_names`, catatan internal, data stok) tidak boleh tampil ke pelanggan, baik tamu maupun login, tanpa persetujuan.
- `PhoneNumber::normalize()` mengembalikan string kosong untuk input tak valid. Jangan menulis kode yang menganggap hasilnya `null`.
- Tes yang butuh tamu wajib memanggil `Auth::logout()` dan `app('auth')->forgetGuards()` di `beforeEach`, karena `tests/Pest.php` login satu user acak sebelum tiap tes Feature.
- Kalau mengubah halaman publik atau area customer, perbarui juga `PROJECT_RULES.md` dan `PLANNING.md` (PLANNING butuh satu baris di bagian E).

## 10. Pengujian (Feature Test)

Tujuan: setiap perubahan langsung ketahuan kalau merusak fitur lain, tanpa mengetes manual berulang kali.

- Framework: Pest 4. Lokasi: `tests/Feature/{Modul}/`, contoh `tests/Feature/Services/ServicePartTest.php`.
- Tes Livewire memakai `Livewire\Livewire::test(...)`. Fungsi `Pest\Livewire\livewire()` tidak tersedia karena plugin-nya tidak terpasang.
- Nama tes memakai ID kasus uji, contoh `SPF-013: subtotal = qty x harga satuan`. ID ini dipakai juga di tabel pengujian Black Box. Jangan mengganti ID tes yang sudah ada. ID baru melanjutkan nomor terakhir di modul itu.
- ID yang sudah dipakai: SSP, FSP, LSP (sparepart), SPF (service parts), ANT (antrian), LOC (lokasi booking), BKG (booking), TIP (tipe booking), CMP, PRC (harga jasa), JSA-001..009 dan JSA-013 (resource dan seeder Jasa, di `tests/Feature/ServiceTypes/`, lulus; JSA-010..012 dicadangkan untuk tabel Jasa, belum ditulis), SED dan SDR (seeder), AUTH-001..021 (`tests/Feature/Auth/CustomerAuthTest.php`), SEC-001..003 (`tests/Feature/Auth/PanelSecurityTest.php`), LNS-001..010 (Tandai Lunas), RWY-001..018 (`tests/Feature/Riwayat/RiwayatTest.php`), PRF-001..007 (`tests/Feature/Profile/ProfilTest.php`). BKA-001..007 (booking login, `tests/Feature/Booking/BookingAccountTest.php`). Peta lengkap kode, berkas, dan aturan yang dicek ada di `tests/README.md`, termasuk daftar ID kembar (LOC, STK, FRM, SED, TSJ-004). ID baru untuk modul itu melanjutkan nomor terbesar dari kedua berkas.
- Aturan bisnis di bagian 5 diuji di level model atau action (cepat dan stabil). Alur form Filament diuji lewat Livewire hanya untuk perilaku yang memang ada di form.
- Gunakan factory yang sudah ada. Kalau factory belum ada, buat dulu, jangan mengisi kolom manual berulang. `UserFactory` punya state `owner()`.
- Fitur baru: minimal satu tes jalur normal, satu tes jalur gagal (validasi atau aturan bisnis ditolak), dan satu tes batas (stok 0, status final, nomor HP ganda, dan sejenisnya).
- Perbaikan bug: tulis tes yang gagal karena bug itu lebih dulu, lalu perbaiki kodenya.
- Sebelum dianggap selesai, seluruh tes harus lulus: `php artisan test` (atau `./vendor/bin/pest`). Laporkan hasilnya, contoh "530 lulus". **Hasil terakhir yang diketahui: 530 lulus, 0 gagal (10 Nov 2026).**
- Perubahan di `Service::syncStock()`, `SparePart::addStock()`, `ServiceStatus::allowedNext()`, `Service::startBlockerMessage()`, atau hitungan total wajib punya tes regresi, karena area ini menyentuh banyak fitur.
- Label yang tampil di tabel ikut jadi teks yang dicari tes. Kalau label atau kolom pindah halaman (contoh: keluhan pindah dari tabel Riwayat ke detail), pindahkan assertion ke halaman barunya dan catat alasannya. Label status juga muncul di dropdown filter, jadi untuk memeriksa isi tabel gunakan `assertViewHas` atau data, bukan teks label.

## 11. Halaman publik dan area customer

Layout:
- `layouts/booking.blade.php`: halaman publik (navbar, footer, tombol WhatsApp). Dipakai beranda dan `/layanan`.
- `layouts/auth.blade.php`: halaman `/masuk` dan `/daftar` (kartu tengah, link kembali ke beranda).
- `layouts/customer.blade.php`: area customer (navbar dan footer beranda + sidebar kiri).

Beranda `/`: `resources/views/welcome.blade.php` extends `layouts.booking` dan meng-include section dari `resources/views/sections/`. Berkas yang ada: `hero`, `services`, `benefis`, `cek-booking`, `map`, `navbar`, `navbar-auth` (bagian kanan navbar: dropdown akun atau tombol Masuk dan Daftar), `footer`, `wa-float`, dan `testimonials` (berkas masih ada tapi **tidak di-include**). Urutan include: lihat `welcome.blade.php`.

- `hero`: formulir booking (Livewire `BookingForm`, view `livewire/booking-form.blade.php`) dan foto bengkel (`public/images/bengkel.png`). Id section masih `appointment`, harus jadi `booking` (`PLANNING.md` B).
- `services`: membaca `$services` (maksimal 6) dan `$totalCount` dari route `/`. Kartu memakai komponen `<x-service-card>` dan menaut ke `/layanan/{id}`. Tombol "Lihat semua layanan" muncul kalau `$totalCount` lebih besar dari jumlah yang tampil.
- `cek-booking`: Livewire `BookingStatusCheck` (aturan 12).
- `map`: iframe Google Maps dengan koordinat bengkel dan tombol "Buka di Google Maps". Tanpa judul, alamat, atau jam buka.
- Menu navbar: Layanan (`/#layanan`), Lokasi (`/#lokasi`), Cek Booking (`/#cek-booking`). Tombol "Buat Janji" dan menu Galeri sudah tidak ada.
- Halaman lain: `/layanan` (daftar jasa, 9 per halaman, `resources/views/layanan/index.blade.php`) dan `/layanan/{id}` (detail jasa, `resources/views/layanan/show.blade.php`). Halaman detail tidak punya tombol booking.

Area customer: view `riwayat.blade.php`, `riwayat-detail.blade.php`, `profil.blade.php`, komponen `components/status-badge.blade.php` (props `status`, atau `label` dan `color`), paginasi `pagination/filament.blade.php`. Perilaku dan aturannya di bagian 5 aturan 16 dan bagian 6.

Rute (`routes/web.php`): `/`, `/layanan`, `/layanan/{serviceType}`, `/daftar` (GET dan POST), `/masuk` (GET dan POST), `POST /keluar`, `/riwayat`, `/riwayat/{id}`, `/riwayat/{id}/invoice`, `/profil` (GET dan PUT). Rute `daftar` dan `masuk` hanya untuk tamu (`guest`), rute area customer wajib `auth`.

Aturan:
- Konten section selain `services` (teks benefit, harga di benefit, dan sejenisnya) masih statis di Blade.
- Jangan menggabung section jadi satu file.
- Jangan menghapus animasi atau kelas animasi yang sudah ada.
- Isi section TIDAK ikut konteks. Kalau tugasnya mengubah section tertentu, minta pengguna mengirim file section itu.
- Jangan menaruh data karangan (testimoni, ulasan, alamat, jam buka) di halaman publik.
- Di Blade, jangan mencampur `@php(...)` satu baris dengan blok `@php ... @endphp` dalam satu berkas (Blade salah membaca dan memberi ParseError). Pakai satu blok `@php`.