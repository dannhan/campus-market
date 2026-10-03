# CampusMarket — Sprint 1 Development Git Plan

Dokumen ini memetakan riwayat commit logis yang direkomendasikan untuk pengembangan Sprint 1 CampusMarket:

---

## Urutan Commit Logis (Logical Commit Breakdown)

1. **`chore: initialize laravel 12 project and environment configuration`**
   - Inisialisasi struktur aplikasi Laravel 12.
   - Konfigurasi `.env.example`, SQLite database, package JSON, Tailwind CSS v4, dan Alpine.js.

2. **`feat: create database migrations for universities, students, and user profiles`**
   - Migrasi tabel `universities`.
   - Migrasi tabel `students` sebagai pangkalan registri akademik.
   - Migrasi penambahan `student_id`, `avatar`, dan kolom rating ke tabel `users`.

3. **`feat: add university, student, and user models with seeders and factories`**
   - Implementasi model `University`, `Student`, dan `User` dengan relasi Eloquent.
   - Pembuatan `UniversityFactory`, `StudentFactory`, dan `UserFactory`.
   - Penambahan seeder deterministik untuk UNDIP, UGM, ITB, dan UI beserta data mahasiswa aktif.

4. **`feat: implement student verification service architecture`**
   - Pembuatan kontrak `StudentVerificationProviderInterface` dan DTO `StudentVerificationResult`.
   - Implementasi `DatabaseStudentVerificationProvider` untuk validasi domain, NIM, nama, status aktif, dan pencegahan akun ganda.
   - Pembuatan `StudentVerificationService` dan registrasi singleton di `AppServiceProvider`.

5. **`feat: implement student registration with academic verification`**
   - Pembuatan Form Request `RegisterRequest`.
   - Implementasi metode registrasi pada `AuthController`.
   - Pembuatan antarmuka pendaftaran mobile-first (`register.blade.php`) terinspirasi mockup Carousell.

6. **`feat: implement email verification flow and signed urls`**
   - Implementasi interface `MustVerifyEmail` pada `User`.
   - Pembuatan `EmailVerificationController` untuk penanganan notice, signed link verification, dan resend link.
   - Tampilan notifikasi verifikasi email (`verify-email.blade.php`).

7. **`feat: implement authentication with remember me and logout`**
   - Pembuatan Form Request `LoginRequest`.
   - Implementasi login dengan regenerasi sesi dan dukungan remember-me token.
   - Implementasi logout dengan invalidasi sesi dan regenerasi token CSRF.
   - Tampilan login mobile-first (`login.blade.php`).

8. **`feat: implement student profile view and secure profile editing`**
   - Pembuatan `ProfileController` dan `UserPolicy`.
   - Tampilan profil mahasiswa dengan badge verifikasi dan data akademik lengkap (`profile/show.blade.php`).
   - Tampilan edit profil dengan upload avatar aman dan penguncian kolom akademik (`profile/edit.blade.php`).

9. **`feat: build landing page and layout navigation shell`**
   - Pembuatan layout responsif mobile (`layouts/app.blade.php`) dengan bottom bar navigasi visual.
   - Pembuatan halaman selamat datang (`welcome.blade.php`) sesuai mockup Carousell.

10. **`test: add comprehensive automated feature and unit tests`**
    - `RegistrationTest`: 8 skenario pengujian pendaftaran dan validasi domain/NIM.
    - `EmailVerificationTest`: 4 skenario alur verifikasi email dan signed link.
    - `AuthenticationTest`: 5 skenario login, remember me, proteksi auth, dan logout.
    - `ProfileTest`: 6 skenario penampilan profil, upload avatar, proteksi identitas, dan otorisasi.
    - `StudentVerificationServiceTest`: 7 skenario pengujian logika verifikasi.

11. **`docs: add sprint 1 architecture, review, retrospective, and progress report`**
    - Dokumentasi `docs/architecture/sprint-1-architecture.md`.
    - Dokumentasi `docs/sprint-1/sprint-1-review.md`.
    - Dokumentasi `docs/sprint-1/sprint-1-retrospective.md`.
    - Dokumen laporan `docs/sprint-1/progress-report-4.md`.
    - Pembaruan dokumen panduan `README.md`.
