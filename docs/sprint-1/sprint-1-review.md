# CampusMarket — Sprint 1 Review

**Tanggal**: 4 Oktober 2026  
**Sprint**: Sprint 1 (Fondasi Arsitektur, Autentikasi Mahasiswa, Verifikasi Kampus & Profil)  
**Status**: SELESAI (DONE)

---

## 1. Ringkasan Pencapaian Sprint
Pada Sprint 1, tim berhasil mengimplementasikan seluruh kebutuhan fungsional inti yang ditargetkan dalam spesifikasi perangkat lunak untuk autentikasi mahasiswa dan verifikasi identitas kampus. Seluruh acceptance criteria yang disyaratkan telah dipenuhi dan dibuktikan dengan suite pengujian otomatis (automated test suite) yang lulus 100%.

---

## 2. Kebutuhan Fungsional yang Diselesaikan (Delivered Features)

| Kode SRS | Deskripsi Kebutuhan | Status | Bukti Implementasi & Verifikasi |
|---|---|---|---|
| **SRS-001** | Mahasiswa mendaftar menggunakan email universitas dan verifikasi status kemahasiswaan | **SELESAI** | Form registrasi mobile-first, Form Request `RegisterRequest`, `AuthController@register`, terverifikasi pada `RegistrationTest`. |
| **SRS-002** | Verifikasi domain email universitas dan integrasi database mahasiswa | **SELESAI** | Layanan `StudentVerificationService` dan provider `DatabaseStudentVerificationProvider`, validasi domain spesifik per universitas (`students.undip.ac.id`, `mail.ugm.ac.id`, dll), verifikasi NIM dan nama pangkalan akademik. |
| **SRS-003** | Pengguna dapat login/logout dengan opsi "Remember Me" | **SELESAI** | Form login mobile-first, `LoginRequest`, regenerasi session aman, cookie remember token Laravel, pengujian pada `AuthenticationTest`. |
| **SRS-004** | Profil mahasiswa menampilkan nama, universitas, jurusan, NIM, rating, dan edit profil terbatas | **SELESAI** | Halaman profil dan edit profil Blade Tailwind, upload avatar aman ke storage publik, penguncian data identitas akademik (*read-only*), pengujian pada `ProfileTest`. |

---

## 3. Demonstrasi Antarmuka Pengguna (UI Review)

### A. Landing & Onboarding
- [Insert Screenshot: Welcome Landing Page `01_welcome_landing.png`]
- Tampilan menyajikan tagline "Dari Mahasiswa, Untuk Mahasiswa", logo CampusMarket dengan penekanan warna teal, kartu nilai utama (Verifikasi Resmi, Aman & Terpercaya, Komunitas Kampus), serta tombol CTA "Masuk" dan "Daftar".

### B. Halaman Masuk (Login)
- [Insert Screenshot: Login Page `02_login.png`]
- Input email universitas, kata sandi dengan toggle visibilitas, checkbox "Ingat saya", tautan lupa password, dan tombol aksi "MASUK".

### C. Halaman Pendaftaran & Verifikasi Mahasiswa
- [Insert Screenshot: Registration Page `03_register.png` & Modal Verifikasi `04_verifikasi_mahasiswa.png`]
- Pilihan perguruan tinggi interaktif dengan petunjuk format email otomatis, input NIM, nama lengkap, jurusan, password, dan konfirmasi persetujuan ketentuan layanan.

### D. Notifikasi Verifikasi Email
- [Insert Screenshot: Email Verification Notice]
- Halaman edukatif yang menginstruksikan mahasiswa untuk memeriksa email resmi kampus guna mengeklik tautan aktivasi akun.

### E. Halaman Profil Mahasiswa & Pengaturan
- [Insert Screenshot: Profile View & Edit Form]
- Header profil bersih dengan avatar beresolusi tajam, badge "Mahasiswa Terverifikasi", rincian akademik (Universitas, Jurusan, NIM, Email), rating reputasi awal (default transparan: "Belum ada rating"), dan tombol aksi edit.
- Form edit dengan preview avatar interaktif (Alpine.js) dan penguncian kolom akademik demi menjaga integritas data.

---

## 4. Metrik Kualitas & Hasil Pengujian
- **Total Test Cases**: 37 Skenario
- **Total Assertions**: 106 Asersi
- **Tingkat Kelulusan**: 100% (0 Failure, 0 Error)
- **Framework Pengujian**: PHPUnit 11 / Laravel Testing Toolkit
- **Area Pengujian**:
  - `RegistrationTest`: 8 skenario pengujian pendaftaran dan validasi domain/NIM.
  - `EmailVerificationTest`: 4 skenario alur verifikasi email dan signed URLs.
  - `AuthenticationTest`: 5 skenario login kredensial, remember me, logout, dan pengalihan pengunjung.
  - `ProfileTest`: 6 skenario penampilan profil, proteksi pengunjung belum login/belum verifikasi, edit avatar/nama, proteksi kolom akademik, dan isolasi otorisasi.
  - `StudentVerificationServiceTest`: 7 skenario pengujian unit mendalam logika verifikasi database provider.

---

## 5. Kesimpulan Review
Sprint 1 telah berhasil diselesaikan dengan standar kode tingkat produksi (production-ready). Fondasi data dan autentikasi telah siap untuk mendukung pengembangan modul katalog produk dan transaksi pada Sprint 2.
