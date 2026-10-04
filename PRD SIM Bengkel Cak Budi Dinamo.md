# PRD: Sistem Informasi Manajemen Bengkel Cak Budi Dinamo

**Versi:** 0.2 (draft) | **Metode:** Prototype, 4 iterasi | **Platform:** Web (Laravel + Filament + MySQL)

**Perubahan dari v0.1:** user story dipetakan ke iterasi, scope dipecah MVP dan setelah MVP, ditambah model data dan aturan bisnis, timeline per iterasi.

---

## 1. Ringkasan

Bengkel Cak Budi Dinamo (Pasuruan, >20 tahun) masih mencatat stok sparepart secara manual: disimpan di kardus berlabel spidol. Akibatnya pencarian part lama, stok habis tidak terpantau, dan servis tertunda. Pemilik bekerja sendirian, sehingga konsultasi online dan promosi tidak tertangani.

Produk ini adalah aplikasi web untuk **mengelola stok sparepart, jadwal servis, dan informasi layanan**. Pemilik login untuk mengelola semuanya. Pelanggan mengakses halaman publik tanpa login dan mengirim permintaan konsultasi atau jadwal lewat formulir.

## 2. Masalah

| # | Masalah | Dampak |
| --- | --- | --- |
| 1 | Stok dicatat manual, penataan kurang rapi | Cari part 10-15 menit per komponen |
| 2 | Stok habis tidak terpantau | 3-4 kasus/minggu servis tertunda 1-2 hari, beli mendadak ke supplier |
| 3 | Pemilik sendirian, tidak sempat balas konsultasi | Pelanggan online hilang, info biaya dan jadwal tidak transparan |
| 4 | Tidak ada kanal digital | Bengkel hanya dikenal lewat mulut ke mulut |
| 5 | Pencatatan pendapatan manual | Sulit rekap |

> Angka 10-15 menit dan 3-4 kasus/minggu perlu diukur ulang di lapangan (baseline) agar ada dasar yang bisa dipertanggungjawabkan.

## 3. Tujuan

1. Stok sparepart terpantau dan cepat dicari.
2. Jadwal servis teratur dan status pengerjaan jelas.
3. Info layanan, promo, dan konsultasi bisa diakses pelanggan secara online.
4. Pemilik bisa melihat rekap operasional dasar.

## 4. Pengguna

| Persona | Akses | Kebutuhan |
| --- | --- | --- |
| **Pemilik Bengkel** (Pak Budi, pemilik sekaligus montir) | Login | Kelola stok, jadwal, info layanan, tanggapi permintaan pelanggan. Antarmuka sederhana, bisa dipakai di ponsel. |
| **Pelanggan** (pemilik kendaraan, sopir luar kota, perusahaan armada) | Tamu, tanpa login | Lihat layanan dan promo, ajukan konsultasi atau jadwal tanpa datang. |

## 5. Rencana Iterasi

| Iterasi | Fokus | User story | Hasil |
| --- | --- | --- | --- |
| **1** | Sparepart + login | US-01, 02, 03, 04 | Pemilik bisa login, input, cari, dan lihat part hampir habis |
| **2** | Servis | US-05 | Pemilik bisa catat servis, ubah status, pakai part (stok berkurang otomatis) |
| **3** | Sisi publik | US-09, 10, 06, 07 | Halaman info, formulir pelanggan, konfirmasi/tolak permintaan, promo |
| **4** | Laporan | US-08 | Dashboard dan rekap |

**MVP = Iterasi 1 + 2** (sisi pemilik yang sudah bisa dipakai sehari-hari). Tiap iterasi diakhiri demo ke pemilik (tahap Customer Evaluation). Screenshot dan catatan revisi disimpan sebagai bahan Bab 4.

## 6. Kebutuhan Fungsional dan User Stories

### Pemilik

**US-01 Login** `[Iterasi 1]` Sebagai pemilik, saya ingin login agar data bengkel hanya bisa diakses saya.

- Hanya akun pemilik yang bisa masuk ke panel kelola.
- Kredensial salah menampilkan pesan error dan tidak memberi akses.
- Panel kelola tidak bisa dibuka tanpa login.

**US-02 Kelola data sparepart (CRUD)** `[Iterasi 1]` Sebagai pemilik, saya ingin menambah, melihat, mengubah, dan menghapus sparepart agar stok tercatat rapi.

- Data minimal: name, category, stock, min_stock, location, price.
- Hapus meminta konfirmasi dan memakai soft delete (riwayat servis tetap aman).
- Stok tidak boleh bernilai negatif.
- Perubahan langsung tampil di daftar.

**US-03 Cari sparepart** `[Iterasi 1]` Sebagai pemilik, saya ingin mencari sparepart berdasarkan nama atau kategori agar tidak lagi mencari di kardus.

- Hasil pencarian tampil dalam kurang dari 10 detik sejak kata kunci dimasukkan.
- Hasil menampilkan stok dan lokasi simpan.
- Kata kunci tidak ditemukan menampilkan pesan "tidak ditemukan".

**US-04 Peringatan stok menipis** `[Iterasi 1]` Sebagai pemilik, saya ingin diberi tanda saat stok di bawah batas minimum agar bisa beli sebelum habis.

- Part dengan `stock <= min_stock` ditandai di daftar.
- Ada filter "stok menipis".
- Batas minimum bisa diatur per sparepart.

**US-05 Kelola jadwal dan status servis** `[Iterasi 2]` Sebagai pemilik, saya ingin mencatat dan memantau servis agar pengerjaan teratur.

- Status: `pending`, `confirmed`, `in_progress`, `completed`, `rejected`.
- Servis mencatat part yang dipakai (quantity, unit_price disalin dari harga part saat itu).
- Stok berkurang otomatis. Jumlah melebihi stok ditolak dengan pesan jelas.
- Daftar servis bisa difilter per status.

**US-06 Tanggapi permintaan pelanggan** `[Iterasi 3]` Sebagai pemilik, saya ingin menerima, menolak, atau mengubah jam permintaan jadwal agar sesuai kapasitas saya.

- Permintaan dari formulir muncul dengan status `pending` dan source `form`.
- Pemilik bisa konfirmasi, tolak, atau ubah jam.
- Perubahan status tersimpan dan terlihat di daftar.

**US-07 Kelola info layanan dan promo** `[Iterasi 3]` Sebagai pemilik, saya ingin mengubah info layanan dan promo agar pelanggan melihat info terbaru.

- Perubahan langsung tampil di halaman publik.

**US-08 Laporan operasional** `[Iterasi 4]` Sebagai pemilik, saya ingin melihat rekap servis dan stok agar bisa memantau usaha.

- Rekap servis per periode (minggu/bulan).
- Rekap stok saat ini.

### Pelanggan (tamu)

**US-09 Lihat info layanan** `[Iterasi 3]` Sebagai pelanggan, saya ingin melihat jenis layanan, jam operasional, kontak, dan promo tanpa datang ke bengkel.

- Halaman bisa dibuka tanpa login.
- Tampilan nyaman di ponsel dan desktop.

**US-10 Kirim permintaan konsultasi atau jadwal** `[Iterasi 3]` Sebagai pelanggan, saya ingin mengisi formulir agar bisa konsultasi atau minta jadwal tanpa membuat akun.

- Isian wajib: nama, nomor HP, keluhan. Opsional: jenis kendaraan/alat, tanggal dan jam yang diinginkan.
- Isian wajib kosong atau nomor HP tidak valid ditolak dengan pesan jelas.
- Setelah kirim, pelanggan melihat konfirmasi bahwa permintaan diterima dan menunggu konfirmasi pemilik.
- Formulir dilindungi dari spam.

## 7. Model Data

4 tabel untuk MVP, sengaja kecil dan siap berkembang.

| Tabel | Kolom utama |
| --- | --- |
| `users` | id, name, email, password |
| `spare_parts` | id, name, category, stock, min_stock, location, price, soft delete |
| `services` | id, customer_name, phone, complaint, scheduled_at, status, source (`owner`/`form`), labor_cost, notes, soft delete |
| `service_parts` | id, service_id (FK), spare_part_id (FK), quantity, unit_price |

Relasi: `service_parts.service_id > services.id`, `service_parts.spare_part_id > spare_parts.id`.

**ERD (format Eraser.io, bisa langsung di-paste):**

```
users [icon: user, color: blue] {
  id bigint pk
  name varchar
  email varchar
  password varchar
  created_at timestamp
  updated_at timestamp
}

spare_parts [icon: box, color: orange] {
  id bigint pk
  name varchar
  category varchar
  stock int
  min_stock int
  location varchar
  price decimal
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp
}

services [icon: tool, color: green] {
  id bigint pk
  customer_name varchar
  phone varchar
  complaint text
  scheduled_at datetime
  status enum(pending, confirmed, in_progress, completed, rejected)
  source enum(owner, form)
  labor_cost decimal
  notes text
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp
}

service_parts [icon: list, color: purple] {
  id bigint pk
  service_id bigint fk
  spare_part_id bigint fk
  quantity int
  unit_price decimal
}

service_parts.service_id > services.id
service_parts.spare_part_id > spare_parts.id
```

**Disiapkan untuk nanti:** `status` dan `source` agar formulir publik tinggal insert tanpa ubah tabel. **Ditunda sampai dibutuhkan:** `customers`, `stock_movements`, `suppliers`, `purchases`, `promos`/`pages`.

## 8. Aturan Bisnis

1. Stok tidak boleh negatif.
2. Alur status: `pending → confirmed → in_progress → completed`, atau `pending → rejected`.
3. `unit_price` di `service_parts` disalin dari `spare_parts.price` saat part dipakai.
4. Biaya servis = `labor_cost` (standar saat ini Rp50.000) + total part.
5. Part yang dihapus memakai soft delete.
6. *(Perlu konfirmasi pemilik)* Stok berkurang saat part ditambahkan ke servis, atau saat servis `completed`?

## 9. Metrik Keberhasilan

| Metrik | Kondisi awal | Target | Cara ukur |
| --- | --- | --- | --- |
| Waktu cari sparepart | 10-15 menit | \< 10 detik | Time-motion study, 5-10 percobaan sebelum dan sesudah |
| Servis tertunda karena stok habis | 3-4 kasus/minggu | Turun (angka disepakati dengan pemilik) | Catatan servis |
| Fungsi utama lolos Black Box | - | 100% test case utama lulus | Tabel test case |
| Kesesuaian dengan kebutuhan pemilik | - | Pemilik menyatakan prototype tiap iterasi sesuai | Evaluasi per iterasi |

## 10. Scope

**MVP (Iterasi 1-2):**

- Login pemilik
- CRUD sparepart, pencarian, peringatan stok menipis
- Jadwal dan status servis, pemakaian part dengan stok otomatis
- Tampilan responsif

**Setelah MVP (Iterasi 3-4):**

- Halaman publik info layanan dan promo
- Formulir konsultasi/jadwal dan antrean konfirmasi
- Laporan dan dashboard

**Tidak masuk:**

- Akun atau login pelanggan
- Pembayaran online (bengkel masih tunai)
- Banyak role (montir, kasir)
- Aplikasi mobile native
- Notifikasi otomatis WhatsApp/SMS
- Pengujian selain Black Box (white box, beban)

## 11. Pertimbangan Teknis

- **Stack:** Laravel, Filament (panel pemilik), Blade/Livewire (halaman publik), MySQL, VS Code.
- **Arsitektur:** browser → server Laravel → MySQL.
- **Keamanan:** autentikasi hanya untuk pemilik; validasi input dan proteksi spam pada formulir publik.
- **Kinerja:** CRUD dan pencarian responsif.
- **Kompatibilitas:** browser modern di desktop dan ponsel.
- **Konvensi:** nama tabel, kolom, dan model berbahasa Inggris. Label tampilan ke pemilik berbahasa Indonesia.
- **Data awal:** daftar stok dan layanan dari pemilik (foto kardus atau daftar part paling sering dipakai).

## 12. Desain dan UX

- Antarmuka sederhana untuk pemilik yang terbiasa kerja manual.
- Panel pemilik memakai komponen Filament. Rancangan Figma disesuaikan atau perbedaannya dijelaskan di laporan.
- Halaman publik mobile-first.
- Desain diperbaiki tiap iterasi berdasarkan masukan pemilik.

## 13. Timeline

**Deadline sidang: belum diketahui.** Isi setelah ada kepastian.

| Tahap | Jadwal |
| --- | --- |
| Persiapan (baseline waktu cari part, tanya pemilik, data awal stok, setup repo) | *isi* |
| Iterasi 1: Sparepart + login, lalu demo | *isi* |
| Iterasi 2: Servis, lalu demo | *isi* |
| Iterasi 3: Sisi publik, lalu demo | *isi* |
| Iterasi 4: Laporan, lalu demo | *isi* |
| Testing (Black Box + time-motion "sesudah") | *isi* |
| Penyusunan laporan | *isi* |

## 14. Risiko dan Mitigasi

| Risiko | Mitigasi |
| --- | --- |
| Pemilik sibuk, demo terlambat | Jadwal demo singkat dan tetap, sesuaikan jam bengkel |
| Pemilik belum terbiasa sistem digital | Antarmuka sederhana, pendampingan saat uji coba |
| Data awal stok tidak lengkap | Input bertahap, mulai dari part yang paling sering dipakai |
| Baseline waktu tidak sempat diukur sebelum sistem dipakai | Ukur di persiapan, sebelum iterasi 1 |
| Formulir publik disalahgunakan (spam) | Captcha dan validasi input |
| Scope melebar karena revisi tiap iterasi | Catat permintaan baru sebagai iterasi berikutnya |
| Waktu tidak cukup untuk iterasi 3-4 | MVP (iterasi 1-2) sudah bisa dipakai dan diuji, 3-4 jadi tambahan |

## 15. Asumsi dan Dependensi

- Pemilik dan pelanggan punya perangkat dan koneksi internet.
- Pemilik menyediakan data awal stok dan layanan.
- Pemilik bersedia ikut demo tiap iterasi.
- Hosting atau lingkungan uji tersedia.

## 16. Pertanyaan Terbuka

1. Deadline sidang dan pengumpulan? (menentukan timeline)
2. Harga part: satu harga saja, atau beda harga beli dan jual?
3. Batas minimum stok: pemilik atur per part, atau ada nilai default?
4. Stok berkurang saat part ditambahkan ke servis, atau saat servis selesai?
5. Laporan perlu mencakup pendapatan (jasa + komponen), atau hanya servis dan stok?
6. Target penurunan servis tertunda yang realistis?
7. Pelanggan perlu melihat status servisnya sendiri, atau cukup status permintaan awal?