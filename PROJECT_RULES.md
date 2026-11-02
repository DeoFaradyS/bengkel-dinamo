# PROJECT_RULES: SIM Bengkel Cak Budi Dinamo

Konteks proyek skripsi untuk AI. Baca seluruh file sebelum menjawab. Bagian "Aturan wajib untuk AI" mengikat. Jika file ini bertentangan dengan kode di konteks, kode yang benar: sebutkan pertentangannya, jangan diam-diam memilih.

## 1. Tujuan sistem

Aplikasi web bengkel Cak Budi Dinamo (Pasuruan): stok sparepart, servis, booking pelanggan, dan laporan. Pemilik (satu-satunya pengguna login) mengelola semuanya lewat panel Filament di `/admin`. Pelanggan (tamu, tanpa login) memakai halaman publik `/` dan formulir booking `/booking`.

Metode: prototype, 4 iterasi. Pengujian: Black Box.

## 2. Stack (dari composer.json)

- PHP ^8.3, Laravel ^13.17, MySQL
- **Filament ~5.0** (panel `admin`). Halaman publik: Blade + Livewire (`App\Livewire\BookingForm`).
- Paket: `barryvdh/laravel-dompdf` (invoice PDF), `laravel-lang/common` (terjemahan), `malzariey/filament-daterangepicker-filter`.
- Dev: Pest 4, Pint, Pail.
- Panel: tema Vite kustom, warna utama Blue, `darkMode(false)`, `login()` aktif, `databaseNotifications()`, `databaseTransactions()`.
- Grup navigasi: Operasional, Katalog, Pengaturan.
- Locale `id`. Nama tabel/kolom/model Inggris, label ke pemilik Indonesia.

## 3. Status fitur (berdasarkan kode)

**Iterasi 1: Sparepart + login**
- [x] US-01 Login pemilik (`->login()` aktif)
- [x] US-02 CRUD sparepart (SoftDeletes, kode unik, foto, kategori)
- [ ] US-03 Pencarian + filter kategori: perlu dicek di `SparePartsTable.php`
- [ ] US-04 Penanda dan urutan stok menipis: perlu dicek di `SparePartsTable.php`

**Iterasi 2: Servis**
- [x] US-05 Kelola servis: buat/ubah, status, jasa (service_items), part (service_parts), filter, invoice PDF, kurangi stok saat selesai

**Iterasi 3: Sisi publik**
- [x] US-09 Halaman publik `/` (hero, layanan, galeri, testimoni, peta, footer, tombol WhatsApp)
- [x] US-10 Booking `/booking` (Livewire + `CreateBooking`, slot jam, anti-ganda, notifikasi ke pemilik)
- [ ] US-06 Pemilik konfirmasi/tolak booking: status `pending → confirmed/rejected` ada di enum; perlu dicek UI-nya
- [ ] US-07 Kelola info layanan dan promo dari panel: belum ada (halaman publik masih Blade statis)

**Iterasi 4: Laporan**
- [x] US-08 Dashboard: KPI, tren, top part, top layanan, tipe, status (`app/Support/Dashboard/*`, `app/Filament/Widgets/*`)
- [ ] Rekap stok per periode: perlu dicek

**Fitur tambahan di luar PRD awal (sudah ada):** home service dengan peta, jarak udara (Haversine) dan ongkos transport; riwayat stok (`stock_movements`); master kategori dan jenis layanan; pengaturan bengkel (`workshop_settings`); kedaluwarsa booking otomatis; halaman `JarakOngkos`.

**Sedang dikerjakan:** (kosong)
**Catatan terakhir:** ORS dibuang, jarak hanya Haversine (jarak udara). Kolom `distance_air_km`, `distance_road_km`, `distance_method`, `distance_estimated` dihapus, sisa `distance_km`. Migrasi lama diedit langsung karena masih tahap dev.

## 4. Model data (sumber kebenaran: file migrasi di konteks)

| Tabel | Isi penting |
| --- | --- |
| `users` | name, email, password |
| `categories` | name, description |
| `spare_parts` | code (unik), name, photo, category_id (FK nullable), stock, min_stock (unsigned, default 1), location, price, soft delete |
| `services` | customer_name, phone, complaint, scheduled_at, status, booking_type, latitude, longitude, address, cancel_reason, stock_synced_at, distance_km, fuel_price, transport_fee, soft delete. **Tidak ada** `source`, `labor_cost`, `notes`. |
| `service_types` | name, default_price |
| `service_items` | service_id, service_type_id (nullable), name, price (jasa per baris) |
| `service_parts` | service_id, spare_part_id, quantity, unit_price. Unik (service_id, spare_part_id). Tanpa timestamps. |
| `stock_movements` | spare_part_id, type, quantity (bertanda), stock_after, note, reason, service_id, user_id |
| `workshop_settings` | latitude, longitude, max_distance_km, fuel_price |distance_method |
| `notifications`, `cache`, `jobs` | bawaan Laravel |

Relasi utama: `Service hasMany ServiceItem`, `Service hasMany ServicePart`, `SparePart hasMany StockMovement`, `SparePart belongsTo Category`, `ServicePart belongsTo SparePart` (withTrashed).

Enum: `ServiceStatus` (pending, confirmed, in_progress, completed, cancelled, rejected, expired), `BookingType` (workshop, home_service), `DistanceMethod` (haversine, ors), `StockMovementType` (opening, purchase, usage, correction), `StockReason`.

Config yang dipakai kode tapi tidak ikut konteks: `config/booking.php`, `config/transport.php`.

## 5. Aturan bisnis (sesuai kode saat ini)

1. Stok tidak boleh negatif: unsigned di DB dan `SparePart::addStock()` menolak hasil di bawah 0. Semua perubahan stok lewat `addStock()` (mencatat `stock_movements`, memakai `lockForUpdate`).
2. **Stok servis berkurang saat status menjadi `completed`**, lewat `Service::syncStock()`, sekali saja (dijaga `stock_synced_at`). Stok kurang membatalkan penyelesaian dan menampilkan notifikasi. Ini menggantikan aturan lama "berkurang saat part ditambahkan".
3. Alur status (`ServiceStatus::allowedNext`): pending → confirmed/rejected/cancelled; confirmed → in_progress/cancelled; in_progress → completed/cancelled. `completed`, `cancelled`, `rejected`, `expired` adalah final. Status aktif: pending, confirmed, in_progress.
4. Satu nomor HP hanya boleh punya satu servis aktif. Satu slot jam hanya untuk satu booking (dikunci dengan `Cache::lock`).
5. Booking pending otomatis menjadi `expired` lewat `bookings:expire` (batas waktu di `config/booking.php`).
6. `unit_price` di `service_parts` disalin dari `spare_parts.price` saat part dipakai.
7. Total servis: `grand_total = labor_total + parts_total + transport_fee`. `labor_total` = jumlah `service_items.price`. `parts_total` = jumlah `quantity * unit_price`.
8. Ongkos transport hanya untuk `home_service`: dihitung dari jarak udara (Haversine), harga BBM, dan `config/transport.*`. Di luar `max_distance_km` booking ditolak.
9. Hapus sparepart memakai soft delete. Kode part diberi akhiran `-DEL-{id}` agar kode bisa dipakai ulang.
10. Notifikasi booking baru dikirim ke semua `User` lewat database notification.

## 6. UX dan tampilan

- Antarmuka sederhana untuk pemilik yang terbiasa kerja manual, bisa dipakai di ponsel.
- Form tambah/ubah: tombol "Simpan" dan "Kembali", tanpa "buat & buat lainnya" (`$canCreateAnother = false`), setelah simpan kembali ke daftar.
- Menyelesaikan servis meminta konfirmasi modal ("Stok akan dikurangi dan servis tidak bisa diubah lagi").
- Harga format Rupiah.
- Halaman publik mobile-first. Tombol publik boleh berlabel "Booking Servis".

## 7. Di luar scope

Login pelanggan, pembayaran online, banyak role (montir/kasir), aplikasi mobile native, notifikasi WhatsApp/SMS otomatis (tombol tautan WhatsApp boleh), pemulihan data terhapus lewat antarmuka.

## 8. Belum diputuskan

- Harga part: satu `price`, belum dibedakan beli dan jual.
- Pelanggan perlu melihat status servisnya sendiri atau tidak.
- Halaman info layanan/promo dikelola dari panel (US-07) atau tetap statis.

## 9. Aturan wajib untuk AI

- Pakai sintaks **Filament 5**. Jika ragu soal API Filament 5, katakan ragu. Jangan mengarang kelas atau namespace, dan jangan memakai sintaks v3 atau v4 tanpa memastikan.
- Jangan menebak nama tabel atau kolom. Ambil dari migrasi dan model di konteks. Info kurang: tanya dulu.
- Semua perubahan stok harus lewat `SparePart::addStock()` atau `Service::syncStock()`. Jangan `update` kolom `stock` langsung.
- Logika status mengikuti `ServiceStatus::allowedNext()`. Jangan menambah status atau transisi tanpa persetujuan.
- Jangan mengubah versi Laravel/Filament, jangan menyarankan upgrade, jangan menambah paket tanpa bertanya.
- Jangan mengubah struktur tabel yang sudah ada tanpa persetujuan. Perubahan skema lewat migrasi baru. Pengecualian: selama proyek masih tahap dev (belum ada data produksi), pemilik boleh memutuskan mengedit migrasi lama lalu `php artisan migrate:fresh --seed`. Tanya dulu dan tunggu persetujuan, jangan memutuskan sendiri.
- Ikuti pola `app/Filament/Resources` (folder `Pages`, `Schemas`, `Tables`, `RelationManagers`, `Actions`), pola `app/Support` dan `app/Actions`.
- Pesan error dan label untuk pengguna berbahasa Indonesia.
- Jawaban kode: file lengkap dengan path-nya. Perubahan kecil: bagian yang berubah plus 1-2 baris konteks.
- Jangan menghapus komentar yang sudah ada di file yang diedit.
- Setiap perubahan atau penambahan kode wajib disertai Feature Test (Pest), dalam jawaban yang sama. Tes baru untuk perilaku baru, dan tes lama yang terdampak ikut disesuaikan. Jangan memberi kode tanpa tes.
- Jangan membuat tes lulus dengan melemahkan assertion atau menghapus tes. Kalau aturan bisnis berubah dan tes lama jadi tidak relevan, katakan dengan jelas tes mana yang diganti dan alasannya.

## 10. Pengujian (Feature Test)

Tujuan: setiap perubahan langsung ketahuan kalau merusak fitur lain, tanpa mengetes manual berulang kali.

- Framework: Pest 4. Lokasi: `tests/Feature/{Modul}/`, contoh `tests/Feature/Services/ServicePartTest.php`.
- Nama tes memakai ID kasus uji, contoh `SPF-013: subtotal = qty x harga satuan`. ID ini dipakai juga di tabel pengujian Black Box. Jangan mengganti ID tes yang sudah ada. ID baru melanjutkan nomor terakhir di modul itu.
- Aturan bisnis di bagian 5 diuji di level model atau action (cepat dan stabil). Alur form Filament diuji lewat Livewire hanya untuk perilaku yang memang ada di form.
- Gunakan factory yang sudah ada. Kalau factory belum ada, buat dulu, jangan mengisi kolom manual berulang.
- Fitur baru: minimal satu tes jalur normal, satu tes jalur gagal (validasi atau aturan bisnis ditolak), dan satu tes batas (stok 0, status final, nomor HP ganda, dan sejenisnya).
- Perbaikan bug: tulis tes yang gagal karena bug itu lebih dulu, lalu perbaiki kodenya.
- Sebelum dianggap selesai, seluruh tes harus lulus: `php artisan test` (atau `./vendor/bin/pest`). Laporkan hasilnya, contoh "469 lulus".
- Perubahan di `Service::syncStock()`, `SparePart::addStock()`, `ServiceStatus::allowedNext()`, atau hitungan total wajib punya tes regresi, karena area ini menyentuh banyak fitur.

11. Antrian servis: hanya satu servis `in_progress` sekali waktu. Servis hanya boleh pindah ke `in_progress` (atau dibuat langsung berstatus itu) kalau tidak ada servis `in_progress` lain dan tidak ada servis `confirmed` yang antre lebih dulu. Urutan antre: `scheduled_at`, lalu `created_at`, lalu `id`, berlaku sama untuk booking aplikasi dan walk-in. Booking `pending` tidak menghalangi. Servis yang dibatalkan, diselesaikan, atau dikonfirmasi/ditolak tidak terkena aturan ini. Pengecekan lewat `Service::startBlockerMessage()`. Kalau terblokir, form buat dan ubah servis menampilkan modal `queueBlocked`. Tes: ANT-001 sampai ANT-012 di `tests/Feature/Services/ServiceQueueTest.php`.

## Halaman publik (landing page `/`)

Satu halaman, dipecah jadi section Blade karena animasi dan gambar.
`resources/views/welcome.blade.php` extends `layouts.booking` dan
meng-include berurutan:
hero, services, benefis, gallery, testimonials, map.
Navbar dan footer ada di `sections/navbar.blade.php` dan
`sections/footer.blade.php`.
Formulir booking: Livewire `BookingForm` di `/booking`
(`livewire/booking-form.blade.php`).

Aturan:
- Konten section (teks layanan, testimoni, harga) masih statis di Blade.
- Jangan menggabung section jadi satu file.
- Jangan menghapus animasi atau kelas animasi yang sudah ada.
- Isi section TIDAK ikut konteks. Kalau tugasnya mengubah section
  tertentu, minta pengguna mengirim file section itu.