# CampusMarket — Marketplace Mahasiswa

> **"Dari Mahasiswa, Untuk Mahasiswa"**

CampusMarket adalah platform marketplace yang dirancang khusus untuk civitas akademika dan mahasiswa. Platform ini memfasilitasi transaksi jual-beli kebutuhan perkuliahan (buku teks, perlengkapan laboratorium, gadget, dan kebutuhan kampus lainnya) dengan lingkungan yang aman dan terpercaya melalui verifikasi status kemahasiswaan aktif berbasis email resmi universitas dan pangkalan data akademik.

---

## 1. Lingkup Pengembangan: Sprint 1

Pada **Sprint 1**, fokus implementasi adalah meletakkan fondasi arsitektur aplikasi, autentikasi aman, verifikasi kemahasiswaan otomatis, dan manajemen profil mahasiswa:

- **SRS-001**: Mahasiswa mendaftar menggunakan email universitas dan memverifikasi status kemahasiswaan.
- **SRS-002**: Sistem memverifikasi domain email universitas dan mengintegrasikan dengan database mahasiswa untuk autentikasi.
- **SRS-003**: Pengguna dapat login/logout dengan opsi "Remember Me" untuk kemudahan akses.
- **SRS-004**: Profil mahasiswa menampilkan nama, universitas, jurusan, NIM, email, badge verifikasi, rating penjual/pembeli, dan edit profil terbatas.

*Fitur di luar Sprint 1 (katalog produk, tawar-menawar, keranjang, checkout, escrow payment, pengiriman, chat, dan review transaksi) ditangguhkan ke Sprint 2 dan seterusnya.*

---

## 2. Kebutuhan Sistem (System Requirements)

- **PHP**: ^8.2 (Direkomendasikan PHP 8.4)
- **Ekstensi PHP**: `pdo_sqlite`, `sqlite3`, `curl`, `dom`, `fileinfo`, `mbstring`, `openssl`, `xml`, `zip`
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

---

## 5. Menjalankan Pengujian Otomatis (Automated Tests)

Semua kebutuhan Sprint 1 diuji secara menyeluruh menggunakan PHPUnit:
```bash
php artisan test
```

Untuk melihat laporan pengujian detail per skenario:
```bash
php artisan test --testdox
```

Cakupan pengujian (37 skenario lulus, 106 asersi):
- **Registrasi**: Validasi domain universitas, pencocokan data NIM pangkalan, penolakan format email tidak valid, deteksi duplikasi email, konfirmasi password, kewajiban menyetujui ketentuan.
- **Verifikasi Email**: Pengiriman event verifikasi, validasi signature URL hash, proteksi rute middleware `verified`, pencegahan verifikasi ganda, kirim ulang tautan.
- **Autentikasi**: Login berhasil, penolakan kredensial salah, pengaktifan sesi "Remember Me", proteksi pengunjung anonim, logout dan regenerasi sesi/CSRF.
- **Profil**: Tampilan data mahasiswa lengkap, pengalihan pengguna belum verifikasi, edit nama tampilan dan upload avatar, proteksi data identitas akademik agar tidak dapat diubah sembarangan, isolasi otorisasi akun.
- **StudentVerificationService**: Unit testing lengkap pencocokan NIM, domain, toleransi nama, dan pangkalan universitas lain.

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

## 9. Batasan & Rencana Sprint 2 (Known Limitations)

- Fitur marketplace seperti etalase produk, katalog pencarian, tawar-menawar, chat, dan pembayaran escrow belum diaktifkan pada Sprint 1.
- Rating penjual dan pembeli pada profil diset pada status awal `"Belum ada rating"` dan akan terintegrasi saat transaksi tuntas diimplementasikan pada sprint berikutnya.
- Penggantian data identitas akademik inti (NIM, universitas, jurusan, email resmi) dikunci demi integritas verifikasi.
