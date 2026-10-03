# CampusMarket — Sprint 1 Architecture Documentation

## 1. Ikhtisar Arsitektur
Arsitektur CampusMarket pada Sprint 1 dirancang untuk memberikan fondasi yang kuat, aman, dan modular dengan pemisahan tanggung jawab (*separation of concerns*) yang jelas. Fokus utama Sprint 1 adalah subsistem identitas, autentikasi sesi, verifikasi status kemahasiswaan berbasis registri akademik, dan manajemen profil mahasiswa.

---

## 2. Model Entitas & Hubungan Database

```
+------------------------------------+
|            universities            |
+------------------------------------+
| id (PK)                            |
| name : string                      |
| slug : string (unique)             |
| email_domain : string              |
| created_at, updated_at             |
+-----------------+------------------+
                  | 1
                  |
                  | hasMany
                  |
                  | *
+-----------------+------------------+
|              students              |
+------------------------------------+
| id (PK)                            |
| university_id : FK -> universities |
| student_number : string (NIM)      |
| full_name : string                 |
| major : string                     |
| university_email : string (unique) |
| is_active : boolean                |
| created_at, updated_at             |
| UNIQUE(university_id, student_no)  |
+-----------------+------------------+
                  | 1
                  |
                  | hasOne
                  |
                  | 0..1
+-----------------+------------------+
|               users                |
+------------------------------------+
| id (PK)                            |
| student_id : FK -> students (null) |
| name : string                      |
| email : string (unique)            |
| password : string (hashed)         |
| email_verified_at : timestamp      |
| avatar : string (nullable)         |
| remember_token : string            |
| seller_rating : decimal (nullable) |
| buyer_rating : decimal (nullable)  |
| seller_review_count : unsignedInt  |
| buyer_review_count : unsignedInt   |
| created_at, updated_at             |
+------------------------------------+
```

### Prinsip Pemisahan Student vs User
- **Student (Pangkalan Registri Akademik)**: Merepresentasikan catatan resmi kemahasiswaan universitas. Model ini tidak dapat diubah oleh pengguna dan berfungsi sebagai sumber kebenaran (*single source of truth*) verifikasi.
- **User (Akun CampusMarket)**: Merepresentasikan entitas pengguna yang login ke dalam marketplace. Akun ini memiliki tautan opsional/eksklusif `student_id` ke tabel `students`, memungkinkan pengguna login, mengunggah foto profil, dan mengubah nama tampilan.

---

## 3. Komponen Layanan Verifikasi Mahasiswa

```
                +-----------------------------------------+
                |           RegisterController            |
                +--------------------+--------------------+
                                     |
                                     | calls
                                     v
                +-----------------------------------------+
                |        StudentVerificationService       |
                +--------------------+--------------------+
                                     |
                                     | delegates to
                                     v
         +-------------------------------------------------------+
         |     <<interface>>                                     |
         |     StudentVerificationProviderInterface              |
         +---------------------------+---------------------------+
                                     |
                                     | implements
                                     v
         +-------------------------------------------------------+
         |        DatabaseStudentVerificationProvider            |
         +-------------------------------------------------------+
           1. Validasi Domain: email ends with @univ.domain
           2. Pencarian NIM: students table by university_id + NIM
           3. Pemeriksaan Status: student.is_active == true
           4. Pencocokan Email: input email == student.email
           5. Konsistensi Nama: toleransi kesamaan >= 85%
           6. Cek Duplikasi: student belum terdaftar di users
```

Desain ini memastikan controller bebas dari logika bisnis verifikasi kampus. Di masa mendatang, `DatabaseStudentVerificationProvider` dapat digantikan dengan `SiakadApiVerificationProvider` atau `CampusSsoProvider` melalui konfigurasi Service Container pada `AppServiceProvider`.

---

## 4. Alur Permintaan Utama (Major Request Flows)

### A. Alur Pendaftaran (Registration Flow)
1. Calon pengguna membuka `GET /register`.
2. Pengguna mengisi form pendaftaran: Nama Lengkap, Pilihan Universitas, NIM, Jurusan, Email Universitas, Password, Konfirmasi Password, serta mencentang persetujuan ketentuan.
3. Form Request `RegisterRequest` memvalidasi kelengkapan format input.
4. `AuthController@register` memanggil `StudentVerificationService::verifyByUniversityId(...)`.
5. Jika verifikasi gagal, sistem kembali dengan pesan kesalahan spesifik (domain salah, NIM tidak cocok, dsb.).
6. Jika verifikasi berhasil:
   - Dibuat record baru pada tabel `users` dengan `student_id` yang terikat.
   - Dipicu event `Illuminate\Auth\Events\Registered`.
   - Laravel mengirimkan email verifikasi signed URL ke email kampus pengguna.
   - Pengguna diautentikasi dan dialihkan ke `GET /email/verify`.

### B. Alur Verifikasi Email (Email Verification Flow)
1. Pengguna membuka tautan aktivasi bertanda tangan digital yang dikirimkan ke kotak masuk email kampus: `GET /email/verify/{id}/{hash}`.
2. Middleware `signed` memverifikasi integritas URL dan masa berlaku token.
3. Controller memanggil `$request->fulfill()`, yang mengisi kolom `email_verified_at` dengan waktu saat ini dan memicu event `Verified`.
4. Pengguna dialihkan ke halaman profil `/profile` dengan status berhasil.

### C. Alur Autentikasi & Sesi (Login, Logout, Remember Me)
1. Pengguna membuka `GET /login`.
2. Form Request `LoginRequest` memvalidasi input email dan password.
3. `Auth::attempt(['email' => ..., 'password' => ...], $remember)` dieksekusi:
   - Jika kredensial cocok, sesi lama diregenerasi (`$request->session()->regenerate()`) untuk mencegah session fixation.
   - Jika opsi "Ingat saya" dicentang, cookie `remember_token` bertanda tangan diterbitkan dengan masa berlaku panjang.
4. Pengguna dialihkan ke rute yang dituju sebelumnya (`intended`) atau halaman profil `/profile`.
5. Rute `POST /logout` menghapus sesi, menginvaliasi session store, dan meregenerasi token CSRF.

### D. Alur Profil Mahasiswa (Profile & Edit Profile Flow)
1. Pengunjung anonim yang mencoba mengakses `GET /profile` otomatis dialihkan ke `/login`.
2. Pengguna yang belum memverifikasi email otomatis dialihkan ke `/email/verify` oleh middleware `verified`.
3. Pengguna yang terverifikasi melihat data profil lengkap yang di-*eager load* (`$user->load('student.university')`):
   - Foto avatar (atau inisial dinamis)
   - Badge "Mahasiswa Terverifikasi"
   - Nama universitas, jurusan, NIM, dan email resmi
   - Rating penjual dan pembeli (default: "Belum ada rating" pada Sprint 1)
   - Tanggal bergabung
4. Pada halaman `GET /profile/edit`:
   - Pengguna hanya diperbolehkan mengubah Nama Tampilan dan Foto Profil (Avatar).
   - Kolom identitas akademik (NIM, universitas, jurusan, email kampus) ditampilkan terkunci (*disabled / read-only*).
   - Pada `PATCH /profile`, controller memverifikasi otorisasi via `Gate::authorize('update', $user)`, memproses upload file gambar yang aman ke disk publik (`storage/app/public/avatars`), dan menyimpan perubahan.

---

## 5. Mockup / Wireframe Mapping
- Halaman Selamat Datang / Onboarding: `01_welcome_landing.png` -> `resources/views/welcome.blade.php`
- Halaman Masuk: `02_login.png` -> `resources/views/auth/login.blade.php`
- Halaman Pendaftaran: `03_register.png` & `04_verifikasi_mahasiswa.png` -> `resources/views/auth/register.blade.php`
- Halaman Notifikasi Verifikasi Email: `resources/views/auth/verify-email.blade.php`
- Halaman Profil Saya: Wireframe Carousell Clean UI -> `resources/views/profile/show.blade.php`
- Halaman Pengaturan / Edit Profil: `resources/views/profile/edit.blade.php`
