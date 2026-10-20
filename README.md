# CampusMarket — Marketplace Mahasiswa

> **"Dari Mahasiswa, Untuk Mahasiswa"**

CampusMarket adalah platform marketplace yang dirancang khusus untuk civitas akademika dan mahasiswa. Platform ini memfasilitasi transaksi jual-beli kebutuhan perkuliahan (buku teks, perlengkapan laboratorium, gadget, dan kebutuhan kampus lainnya) dengan lingkungan yang aman dan terpercaya melalui verifikasi status kemahasiswaan aktif berbasis email resmi universitas dan pangkalan data akademik.

---

## 1. Lingkup Pengembangan: Sprint 1 & Sprint 2

Pada **Sprint 1**, fokus implementasi adalah meletakkan fondasi arsitektur aplikasi, autentikasi aman, verifikasi kemahasiswaan otomatis, dan manajemen profil mahasiswa:

- **SRS-001**: Mahasiswa mendaftar menggunakan email universitas dan memverifikasi status kemahasiswaan.
- **SRS-002**: Sistem memverifikasi domain email universitas dan mengintegrasikan dengan database mahasiswa untuk autentikasi.
- **SRS-003**: Pengguna dapat login/logout dengan opsi "Remember Me" untuk kemudahan akses.
- **SRS-004**: Profil mahasiswa menampilkan nama, universitas, jurusan, NIM, email, badge verifikasi, rating penjual/pembeli, dan edit profil terbatas.

Pada **Sprint 2**, aplikasi berkembang menjadi marketplace yang fungsional untuk etalase barang dan negosiasi harga:

- **SRS-005**: Pencarian produk berdasarkan nama/deskripsi, kategori, universitas penjual, dan kisaran harga.
- **SRS-006**: Filter berdasarkan kondisi barang (baru/seperti baru/bekas) dan jenis transaksi (jual/sewa).
- **SRS-007**: Halaman detail produk dengan galeri foto, deskripsi, harga, kondisi, lokasi penjual, dan profil penjual.
- **SRS-008**: Mahasiswa menambahkan produk untuk dijual dengan upload hingga 5 foto dan deskripsi lengkap, termasuk edit dan hapus listing milik sendiri.
- **SRS-009**: Kategori khusus seperti "Buku per Semester", "Perlengkapan Lab", "Gadget Second", dan "Jasa Mahasiswa".
- **SRS-010**: Tawar-menawar (bargaining) antara pembeli dan penjual: ajukan tawaran, tolak, tawaran balasan (counter), terima, dan batalkan; penawaran yang diterima menandai produk terjual.

*Fitur di luar Sprint 2 (keranjang, checkout, escrow payment, pengiriman, chat, review transaksi, dan dashboard admin) ditangguhkan ke Sprint 3 dan seterusnya.*

---

## 2. Kebutuhan Sistem (System Requirements)

- **PHP**: ^8.2 (Direkomendasikan PHP 8.4)
- **Ekstensi PHP**: `pdo_sqlite`, `sqlite3`, `curl`, `dom`, `fileinfo`, `mbstring`, `openssl`, `xml`, `zip`
- **Ekstensi PHP opsional**: `gd` (membuat foto placeholder pada `php artisan demo:seed` dan menjalankan pengujian unggah gambar)
- **Composer**: ^2.2+
- **Node.js**: ^18.x / ^20.x / ^22.x
- **NPM**: ^9.x / ^10.x
- **Database**: SQLite (untuk pengembangan lokal) atau MySQL/PostgreSQL

---

## 3. Panduan Instalasi & Pengaturan Lingkungan

### Langkah 1: Kloning & Masuk ke Direktori Repositori
```bash
git clone <repository_url> campus-market
cd campus-market
```

### Langkah 2: Instal Dependensi Backend (Composer)
```bash
composer install
```

### Langkah 3: Konfigurasi File Environment (`.env`)
Salin file `.env.example` menjadi `.env` dan hasilkan Application Key:
```bash
cp .env.example .env
php artisan key:generate
```

Pastikan variabel penting pada `.env` telah disesuaikan:
```env
APP_NAME=CampusMarket
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite # atau kosongkan untuk path default

MAIL_MAILER=log
FILESYSTEM_DISK=public
```

### Langkah 4: Inisialisasi Database SQLite
Buat file database SQLite jika belum ada:
```bash
touch database/database.sqlite
```

Jalankan migrasi dan seeder database:
```bash
php artisan migrate --seed
```

### Langkah 5: Storage Link (Penyimpanan Avatar Mahasiswa)
Hubungkan direktori publik ke direktori storage:
```bash
php artisan storage:link
```

### Langkah 6: Kompilasi Aset Frontend (Tailwind CSS v4 & Alpine.js)
```bash
npm install
npm run build
```
*(Untuk mode pengembangan dengan hot reload, jalankan `npm run dev`)*

---

## 4. Menjalankan Aplikasi

Jalankan development server Laravel:
```bash
php artisan serve
```
Akses aplikasi melalui peramban: `http://localhost:8000`

> **Catatan upload:** perintah `php artisan serve` pada proyek ini menjalankan PHP dengan `upload_max_filesize=8M` dan `post_max_size=40M`, sehingga foto produk hingga 5MB dan avatar hingga 2MB dapat diunggah tanpa error 413. Jika aplikasi dijalankan melalui Apache/Nginx/FPM, samakan batas tersebut pada konfigurasi PHP.

---

## 5. Menjalankan Pengujian Otomatis (Automated Tests)

Semua kebutuhan Sprint 1 dan Sprint 2 diuji secara menyeluruh menggunakan PHPUnit:
```bash
php artisan test
```

Untuk melihat laporan pengujian detail per skenario:
```bash
php artisan test --testdox
```

Cakupan pengujian (83 skenario lulus, 349 asersi):
- **Registrasi**: Validasi domain universitas, pencocokan data NIM pangkalan, penolakan format email tidak valid, deteksi duplikasi email, konfirmasi password, kewajiban menyetujui ketentuan.
- **Verifikasi Email**: Pengiriman event verifikasi, validasi signature URL hash, proteksi rute middleware `verified`, pencegahan verifikasi ganda, kirim ulang tautan.
- **Autentikasi**: Login berhasil, penolakan kredensial salah, pengaktifan sesi "Remember Me", proteksi pengunjung anonim, logout dan regenerasi sesi/CSRF.
- **Profil**: Tampilan data mahasiswa lengkap, pengalihan pengguna belum verifikasi, edit nama tampilan dan upload avatar, proteksi data identitas akademik agar tidak dapat diubah sembarangan, isolasi otorisasi akun.
- **StudentVerificationService**: Unit testing lengkap pencocokan NIM, domain, toleransi nama, dan pangkalan universitas lain.
- **Manajemen Produk**: Publikasi listing dengan foto (maks. 5), validasi harga/kondisi/kategori, edit dan hapus listing oleh pemilik, penolakan akses non-pemilik, penghapusan berkas foto fisik.
- **Etalase & Pencarian**: Penjelajahan produk aktif, pencarian nama/deskripsi, filter kategori, universitas penjual, kondisi, jenis transaksi, rentang harga, pengurutan harga, dan pengabaian nilai filter tidak valid.
- **Tawar-Menawar**: Pengajuan penawaran, larangan menawar produk sendiri, larangan penawaran ganda yang menunggu respons, terima (produk menjadi terjual dan penawaran bersaing ditolak), tolak, tawaran balasan bertingkat (counter thread), pembatalan oleh pemberi tawaran, dan proteksi respons pihak luar.

---

## 6. Arsitektur Verifikasi Mahasiswa (Student Verification Architecture)

Untuk mematuhi prinsip *separation of concerns*, pangkalan data kemahasiswaan dipisahkan secara struktural dari akun pengguna:

```
[University] 1 ──── * [Student (Registry Pangkalan Kampus)] 1 ──── 0..1 [User (Akun CampusMarket)]
```

- **Tabel `universities`**: Menyimpan daftar perguruan tinggi resmi dan domain email yang sah (contoh: `students.undip.ac.id`, `mail.ugm.ac.id`).
- **Tabel `students`**: Berfungsi sebagai *mock registry / academic database* kampus (NIM, nama lengkap resmi, jurusan, email kampus, status aktif).
- **Tabel `users`**: Menyimpan kredensial akun akses CampusMarket yang merujuk pada `student_id`.

### Alur Kerja Layanan (`StudentVerificationService`)
1. Pengguna memasukkan data pendaftaran di form `/register`.
2. Controller mendelegasikan pengecekan ke `StudentVerificationService` (menggunakan implementasi `DatabaseStudentVerificationProvider`).
3. Layanan memverifikasi:
   - Apakah domain email cocok dengan domain resmi universitas yang dipilih.
   - Apakah NIM terdaftar di pangkalan data universitas terkait.
   - Apakah nama yang diinput konsisten dengan nama resmi di database kampus.
   - Apakah email yang diinput sesuai dengan catatan kemahasiswaan NIM tersebut.
   - Apakah mahasiswa berstatus aktif dan belum memiliki akun CampusMarket.
4. Jika valid, akun `User` dibuat, event `Registered` dipicu, dan email verifikasi dikirimkan.

Desain berbasis interface (`StudentVerificationProviderInterface`) memungkinkan penyedia verifikasi di kemudian hari diganti dengan API SIAKAD kampus atau SSO OAuth universitas tanpa mengubah kode controller.

---

## 7. Kredensial Pengujian Pengembangan (Deterministic Test Credentials)

Seeder menyediakan data siap pakai untuk pengujian:

| No | Universitas | NIM | Nama Mahasiswa | Email Universitas | Status Akun | Password |
|---|---|---|---|---|---|---|
| 1 | Universitas Diponegoro | `24060122130001` | Budi Santoso | `budi.santoso@students.undip.ac.id` | **Terverifikasi (Aktif)** | `Password123!` |
| 2 | Universitas Diponegoro | `24060122130002` | Siti Rahmawati | `siti.rahmawati@students.undip.ac.id` | **Belum Verifikasi Email** | `Password123!` |
| 3 | Universitas Gadjah Mada | `22/498766/SV/54322` | Fajar Nugroho | `fajar.nugroho@mail.ugm.ac.id` | **Terverifikasi (Aktif)** | `Password123!` |

### Data Mahasiswa Siap Registrasi Baru (Belum Memiliki Akun):
- **UNDIP**: NIM `24060122130003`, Nama `Ahmad Pratama`, Jurusan `Teknik Elektro`, Email `ahmad.pratama@students.undip.ac.id`
- **UNDIP**: NIM `24060122130004`, Nama `Rina Melati`, Jurusan `Manajemen`, Email `rina.melati@students.undip.ac.id`
- **UGM**: NIM `22/498765/TK/54321`, Nama `Dewi Lestari`, Jurusan `Teknik Industri`, Email `dewi.lestari@mail.ugm.ac.id`
- **ITB**: NIM `13522001`, Nama `Reza Firmansyah`, Jurusan `Teknik Informatika`, Email `reza.firmansyah@mahasiswa.itb.ac.id`
- **UI**: NIM `2206812345`, Nama `Nadia Putri`, Jurusan `Ilmu Komputer`, Email `nadia.putri@ui.ac.id`

### Data Demo Marketplace (Seeder)

Untuk mengisi etalase dengan data contoh yang lebih ramai (akun penjual, listing produk, dan foto placeholder):

```bash
php artisan demo:seed
```

Perintah tersebut membuat:

- **8 akun demo terverifikasi** (password `Password123!`) dengan email berawalan `demo.` dan NIM pada rentang `9900000001`–`9900000008`.
- **24 produk** yang tersebar di seluruh kategori, dengan campuran status jual/sewa, kondisi barang, serta status `active`/`sold`/`withdrawn`.
- **48 foto placeholder** hasil generate GD di `storage/app/public/products/demo/` (1–3 foto per produk).

| Email | Nama | Universitas | NIM |
|---|---|---|---|
| `demo.raka@students.undip.ac.id` | Raka Pratama | Universitas Diponegoro | `9900000001` |
| `demo.maya@students.undip.ac.id` | Maya Kusuma | Universitas Diponegoro | `9900000002` |
| `demo.bagas@mail.ugm.ac.id` | Bagas Saputra | Universitas Gadjah Mada | `9900000003` |
| `demo.tari@mail.ugm.ac.id` | Tari Wulandari | Universitas Gadjah Mada | `9900000004` |
| `demo.yoga@mahasiswa.itb.ac.id` | Yoga Ramadhan | Institut Teknologi Bandung | `9900000005` |
| `demo.sinta@mahasiswa.itb.ac.id` | Sinta Maharani | Institut Teknologi Bandung | `9900000006` |
| `demo.aldi@ui.ac.id` | Aldi Firmansyah | Universitas Indonesia | `9900000007` |
| `demo.putri@ui.ac.id` | Putri Anjani | Universitas Indonesia | `9900000008` |

Seeder ini **tidak** dipanggil oleh `php artisan migrate --seed`, sehingga data demo tidak masuk ke database produksi. Perintah aman dijalankan berulang kali (idempotent): data diperbarui berdasarkan email/NIM/slug, dan foto tidak diduplikasi.

Untuk menghapus seluruh data demo beserta file fotonya:

```bash
# Lihat ringkasan data yang akan dihapus tanpa mengubah apa pun
php artisan demo:clean --dry-run

# Hapus akun demo, mahasiswa demo, produk, foto, dan avatar demo
php artisan demo:clean
```

Cleanup hanya menyasar data dengan email berawalan `demo.`; universitas, kategori, akun pengguna asli, dan produk non-demo tidak tersentuh. Pastikan `php artisan storage:link` sudah dijalankan agar foto demo tampil di peramban.

---

## 8. Pemeriksaan Email Verifikasi Lokal (Development Email)

Driver mail bawaan diatur ke `MAIL_MAILER=log`. Setiap kali registrasi atau permintaan kirim ulang tautan verifikasi dilakukan:
1. Buka file log: `storage/logs/laravel.log`
2. Cari baris berisi tautan verifikasi bertanda tangan digital:
   ```text
   http://localhost:8000/email/verify/1/4c7625...
   ```
3. Buka tautan tersebut di peramban untuk memverifikasi akun secara instan.

---

## 9. Batasan & Rencana Sprint 3 (Known Limitations)

- Fitur marketplace inti (etalase, pencarian, listing, tawar-menawar) telah aktif pada Sprint 2; keranjang, checkout, escrow payment, pengiriman, dan chat belum diimplementasikan.
- Rating penjual dan pembeli pada profil diset pada status awal `"Belum ada rating"` dan akan terintegrasi saat review transaksi diimplementasikan pada sprint berikutnya.
- Penggantian data identitas akademik inti (NIM, universitas, jurusan, email resmi) dikunci demi integritas verifikasi.
- Negosiasi tawar-menawar bersifat thread harga sederhana; chat bebas dan notifikasi push menyusul pada sprint berikutnya (SRS-016, SRS-022).
