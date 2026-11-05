# PLANNING: SIM Bengkel Cak Budi Dinamo

Catatan pekerjaan yang belum selesai, bug yang diketahui, dan keputusan terbuka. Aturan dan status fitur yang sudah jadi ada di `PROJECT_RULES.md`. Terakhir diperbarui: 5 November 2026.

Tanda: `[ ]` belum, `[~]` sebagian, `[x]` selesai.

## A. Utang tes (ditunda atas permintaan pemilik, wajib sebelum fitur dianggap selesai)

Penomoran lanjut dari ID terakhir di tiap modul. ID di bawah sementara.

**Jasa (`tests/Feature/ServiceTypes/`)**
- [ ] JSA-007..009 seeder: mengisi 8 jasa dengan deskripsi (angka 8, bukan 5), dijalankan dua kali tidak menggandakan, foto yang sudah diunggah tidak tertimpa. Kalau berkas tes dengan angka 5 sudah tersimpan, angkanya diganti jadi 8 (penyesuaian karena seeder berubah, bukan melemahkan tes).
- [ ] JSA-010..012 tabel: kolom foto dan deskripsi ada, deskripsi panjang dipotong satu baris, jasa tanpa deskripsi dan foto tetap tampil.
- [ ] Harga tampil bulat di halaman edit (`mutateFormDataBeforeFill` di `EditServiceType`).
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

- [ ] **LOC-001 gagal.** Tes mencari teks "Lokasi saya", view `booking-form.blade.php` sekarang berisi "Pakai lokasi saya". Pilih: ubah ekspektasi tes (sah, tes lama tidak relevan) atau kembalikan label di view.
- [ ] **PRC-0xx gagal** (`tests/Feature/ServiceTypePriceFormTest.php`). Tes masih menunjuk `ManageServiceTypes` yang sudah dihapus. Pindahkan ke `CreateServiceType` dan `EditServiceType`. Assertion dan ID tidak diubah, hanya halaman target.
- [ ] **Upload foto jasa gagal** ("gagal diunggah") walau file 50 KB. Ukuran bukan penyebab. Yang dicek: `APP_URL` di `.env` sama dengan alamat yang dibuka, status request di DevTools (Network), ekstensi `fileinfo`, format file (hanya JPG dan PNG), dan apakah upload foto sparepart juga gagal.
- [ ] Sisa rujukan ke `/booking` setelah halamannya dihapus: navbar, footer, hero, notifikasi booking baru, dan tes yang memanggil `get('/booking')`. Jalankan `git grep -n "/booking" -- app resources routes tests`.
- [ ] Berkas `resources/views/components/service-card.blade.php` belum pernah dilihat. Pastikan ada dan menerima props `name`, `description`, `price`, `photoUrl`.
- [ ] Section galeri: berkas `gallery.blade.php` tidak terlihat di folder `sections` (screenshot 5 Nov). Cek apakah sudah dihapus atau include-nya masih ada di `welcome.blade.php`.
- [ ] Cek Booking bergantung pada asumsi: `PhoneNumber::normalize()` mengembalikan `null` untuk input tak valid, nomor di `services.phone` sudah dalam bentuk normal (termasuk dari `BookingForm`), `scheduled_at` di-cast `datetime`, `status` dan `booking_type` di-cast ke enum. Perlu dikonfirmasi lewat kode `BookingForm` dan model `Service`.
- [ ] Kompres `public/images/bengkel.png` kalau di atas sekitar 300 KB.
- [ ] Jalankan `php artisan test` penuh setelah semua perubahan beranda. Hasil terakhir yang diketahui: 453 lulus, 2 gagal (LOC-001, PRC-0xx).

## C. Fitur belum dikerjakan

- [ ] Rekap stok per periode (Iterasi 4). Belum diputuskan dibangun atau ditulis sebagai pengembangan lanjutan skripsi.
- [ ] Promo dikelola dari panel (sisa US-07). Layanan sudah lewat menu Jasa.
- [ ] Tombol cepat konfirmasi/tolak booking di tabel servis (opsional). Wajib memanggil `startBlockerMessage()` kalau mengubah status ke `in_progress`.
- [ ] Login pelanggan, lihat D2.
- [ ] Cek Booking: tampilkan alasan batal (D3).
- [ ] Booking yang terisi jasa pilihan dari halaman detail (D5).
- [ ] Slug untuk URL jasa (D4).
- [ ] Sinkronkan `PROJECT_RULES.md` setiap ada perubahan beranda atau aturan.

## D. Keputusan terbuka

**D1. Hapus jasa yang sudah dipakai di `service_items`.** Kolom `service_type_id` nullable dan nama tersimpan sebagai salinan di `service_items`, jadi menghapus tidak merusak riwayat. Pilihan: boleh dihapus (sekarang `DeleteAction` polos), atau diblokir kalau sudah dipakai.

**D2. Login pelanggan.** Bertentangan dengan bagian 7 `PROJECT_RULES.md` (di luar scope). Skripsi sudah punya Cek Booking untuk kebutuhan "lihat status". Kalau dibangun, bahan pertimbangan:
- Metode: email + password dengan nomor HP diisi saat daftar. Nomor HP + password tanpa verifikasi berisiko dipakai untuk mengintip booking orang lain. OTP WhatsApp di luar scope.
- Pakai tabel `customers` dan guard terpisah, jangan dicampur dengan `users`: `users` dipakai untuk `/admin` dan penerima notifikasi booking (aturan 10).
- `services` butuh kolom `customer_id` (nullable). Booking tamu lama tetap `null`. Perubahan skema: migrasi baru, atau edit migrasi lama dengan persetujuan pemilik.
- Tanpa paket tambahan: ditulis sendiri dengan Livewire, ikut pola `BookingForm`.
- Fitur setelah login, urut prioritas: riwayat booking dan status, form booking terisi otomatis, batalkan booking `pending` sendiri (lewat `allowedNext()`), unduh invoice PDF untuk servis `completed`, simpan lokasi home service (opsional).
- Wajib tes regresi untuk notifikasi (aturan 10) dan akses `/admin`.
- Alternatif: tulis sebagai pengembangan lanjutan di bab skripsi.

**D3. Alasan batal di Cek Booking.** `cancel_reason` mungkin catatan internal pemilik. Saat ini tidak ditampilkan. Putuskan perlu ditampilkan atau tidak.

**D4. Slug jasa.** Detail jasa sekarang `/layanan/{id}`. Slug butuh kolom baru lewat migrasi dan persetujuan.

**D5. Booking dari detail jasa.** Halaman detail tidak punya tombol booking (sengaja dihapus). Kalau mau tombol yang mengarah ke form di hero dan mengisi jasa otomatis, `BookingForm` perlu diubah.

**D6. Isi lama bagian 8 yang masih terbuka:** harga part satu `price` atau dibedakan beli dan jual; servis selesai tapi barang tidak diambil (`picked_up_at`); grace period no-show; kedaluwarsa sparepart (`expired_at`).

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
