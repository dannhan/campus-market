# CampusMarket — Progress Report 4

**Proyek**: CampusMarket  
**Tagline**: "Dari Mahasiswa, Untuk Mahasiswa"  
**Sprint**: Sprint 1  
**Tanggal**: 4 Oktober 2026  
**Penulis**: Ahmad Danun Hanani (Lead Software Engineer)

---

## 1. Tujuan Sprint
Tujuan utama Sprint 1 adalah membangun fondasi arsitektur sistem CampusMarket yang tangguh dan siap pakai untuk lingkungan produksi lokal, mencakup:
- Pangkalan data perguruan tinggi dan catatan kemahasiswaan.
- Mekanisme registrasi akun dengan validasi identitas ganda (domain email resmi dan pangkalan data kemahasiswaan kampus).
- Alur verifikasi email standar menggunakan tanda tangan digital (*Signed URLs*).
- Autentikasi sesi aman (Login, Logout, dan Remember Me).
- Halaman profil mahasiswa terverifikasi dengan pemisahan atribut yang dapat diubah dan atribut akademik terkunci.

---

## 2. Requirement yang Diimplementasikan (Pemetaan SRS)

Implementasi Sprint 1 secara penuh memenuhi 4 butir spesifikasi kebutuhan perangkat lunak (Software Requirements Specification):

| ID Kebutuhan | Deskripsi SRS | Status Implementasi |
|---|---|---|
| **SRS-001** | The system must allow students to register using a valid university email and verify student status. | **SELESAI (100%)** |
| **SRS-002** | The system must verify university email domain and integrate with a student database for authentication. | **SELESAI (100%)** |
| **SRS-003** | The system must allow users to login/logout with a "Remember Me" option. | **SELESAI (100%)** |
| **SRS-004** | The system must provide a student profile displaying: name, university, major, rating as seller/buyer. | **SELESAI (100%)** |

*Catatan: Fitur Sprint 2 (katalog produk, tawar-menawar, chat, pembayaran) secara ketat tidak disertakan pada sprint ini.*

---

## 3. Arsitektur Sistem
Aplikasi dibangun di atas framework Laravel 12 dengan memanfaatkan pola arsitektur *Domain-Driven & Service-Oriented*:
- **Model**: `University`, `Student`, `User`.
- **Layanan Verifikasi**: `StudentVerificationService` mengimplementasikan `StudentVerificationProviderInterface` dengan penyedia konkret `DatabaseStudentVerificationProvider`.
- **Form Request & Kebijakan Otorisasi**: `RegisterRequest`, `LoginRequest`, `UpdateProfileRequest`, dan `UserPolicy`.
- **Presentasi Frontend**: Blade Templates bergaya mobile-first (`max-w-md mx-auto`) dengan Tailwind CSS v4 dan interaktivitas ringan via Alpine.js.

---

## 4. Database
Skema database SQLite terdiri dari migrasi terstruktur:
1. `universities`: Menyimpan data kampus (`name`, `slug`, `email_domain`).
2. `students`: Bertindak sebagai pangkalan registri data kemahasiswaan resmi (`university_id`, `student_number`, `full_name`, `major`, `university_email`, `is_active`).
3. `users`: Akun otentikasi CampusMarket yang ditautkan ke `student_id`, kolom avatar, serta kolom rating penjual/pembeli masa depan.

Relasi yang terjalin:
```
University (1) ───< Student (*) ───< User (0..1)
```

---

## 5. Implementasi Registrasi (SRS-001)
- Rute: `GET /register` dan `POST /register`.
- Controller: `AuthController@showRegister` dan `AuthController@register`.
- Mahasiswa menginput nama lengkap, memilih universitas dari dropdown dinamis, menginput NIM, jurusan, email kampus, password, dan konfirmasi password.
- Terdapat kotak centang persetujuan syarat dan ketentuan layanan.
- Menggunakan `RegisterRequest` untuk validasi format awal, dilanjutkan dengan pendelegasian ke modul verifikasi kemahasiswaan.

---

## 6. Verifikasi Mahasiswa (SRS-002)
- Modul `StudentVerificationService` memverifikasi 6 aturan validasi ketat:
  1. Universitas yang dipilih valid dan aktif.
  2. Domain email mahasiswa berakhiran sesuai `email_domain` pada tabel universitas terkait (misal: `@students.undip.ac.id`, `@mail.ugm.ac.id`).
  3. NIM mahasiswa tercatat pada pangkalan data kampus yang bersangkutan.
  4. Email yang diinput sesuai dengan email pangkalan kemahasiswaan NIM tersebut.
  5. Nama yang diinput konsisten dengan nama terdaftar (toleransi kemiripan $\ge 85\%$).
  6. Mahasiswa berstatus aktif dan belum pernah mendaftarkan akun CampusMarket sebelumnya.
- Jika lolos, akun dibuat dan dikirimkan email bertanda tangan digital (*signed verification link*).

---

## 7. Login & Logout (SRS-003)
- Rute: `GET /login`, `POST /login`, `POST /logout`.
- Menggunakan `LoginRequest` untuk validasi format email kampus dan password.
- Menjalankan regenerasi sesi (`session()->regenerate()`) untuk mencegah serangan *session fixation*.
- Logout menginvaliasi sesi dan meregenerasi token CSRF secara aman.

---

## 8. Remember Me (SRS-003)
- Checkbox "Ingat saya" pada form login meneruskan nilai boolean ke `Auth::attempt($credentials, $remember)`.
- Mengaktifkan cookie sesi jangka panjang (`remember_token`) bawaan Laravel yang terenkripsi aman.

---

## 9. Profil Mahasiswa (SRS-004)
- Rute: `GET /profile`, `GET /profile/edit`, `PATCH /profile`.
- Tampilan menyajikan:
  - Foto profil mahasiswa (atau inisial dinamis bergaya teal/mint).
  - Badge "Mahasiswa Terverifikasi" berwarna hijau/teal.
  - Nama universitas, jurusan, NIM, dan email resmi mahasiswa.
  - Rating penjual dan pembeli (default pada Sprint 1: *"Belum ada rating"* dengan visual bintang abu-abu).
  - Tombol aksi menuju Edit Profil dan Logout.
- **Edit Profil & Keamanan Data**:
  - Mahasiswa hanya dapat mengedit Nama Tampilan dan Foto Profil (Avatar) yang diunggah ke storage publik dengan validasi tipe gambar dan ukuran maksimal 2MB.
  - Data identitas akademik (NIM, Jurusan, Universitas, Email Resmi) dikunci (*read-only*) dan dilindungi secara server-side agar tidak dapat diubah tanpa prosedur re-verifikasi.

---

## 10. Testing
Pengujian otomatis dilakukan menggunakan PHPUnit dengan total **37 test cases** dan **106 assertions** dengan tingkat kelulusan 100%:
- `RegistrationTest`: 8 tests (pendaftaran berhasil, tolak NIM palsu, tolak domain asing, tolak akun ganda, validasi input).
- `EmailVerificationTest`: 4 tests (pengiriman email verifikasi, validasi tautan URL bertanda tangan, penanganan akun yang sudah terverifikasi, kirim ulang tautan).
- `AuthenticationTest`: 5 tests (login sukses, tolak password salah, remember me token, proteksi rute profil dari tamu, alur logout).
- `ProfileTest`: 6 tests (tampilan profil terverifikasi, pengalihan pengguna belum login / belum verifikasi email, upload avatar & edit nama, pencegahan modifikasi atribut akademik, isolasi otorisasi).
- `StudentVerificationServiceTest`: 7 tests (unit testing provider verifikasi basis data).

---

## 11. Hasil Implementasi
Seluruh komponen Sprint 1 telah selesai dibuat, diintegrasikan, dan diuji secara menyeluruh. Antarmuka aplikasi telah diuji menggunakan server lokal dan mematuhi estetika desain mobile-first yang bersih sesuai inspirasi Carousell.

---

## 12. Kendala yang Dihadapi & Solusi
1. **Pemisahan SQLite Foreign Keys saat Rollback**:
   - *Kendala*: Sintaks SQLite menolak penghapusan constraint index tertentu saat rollback migrasi berantai.
   - *Solusi*: Mengatur urutan drop constraint pada migrasi `users` secara eksplisit sebelum drop kolom foreign key.
2. **Pengisian Kolom `email_verified_at` pada Mass Assignment**:
   - *Kendala*: Factory/Seeder pengujian memerlukan penyetelan status email verifikasi secara langsung.
   - *Solusi*: Menambahkan `email_verified_at` ke dalam properti `$fillable` pada model `User`.

---

## 13. Rencana Sprint Berikutnya (Sprint 2)
Pada Sprint 2, pengembangan akan beralih ke transaksi dan etalase barang:
1. Skema database kategori produk (`categories`), produk (`products`), dan gambar produk (`product_images`).
2. Fitur unggah produk dan manajemen listing barang mahasiswa (buku, alat praktikum, perlengkapan kos).
3. Pencarian produk, filter berdasarkan universitas terdekat, dan filter kategori.
4. Fitur tawar-menawar harga (bargaining flow) antar mahasiswa.
