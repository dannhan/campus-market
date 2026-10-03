# CampusMarket — Sprint 1 Retrospective

**Tanggal**: 4 Oktober 2026  
**Sprint**: Sprint 1  
**Fokus**: Fondasi Aplikasi, Autentikasi Mahasiswa, Verifikasi Kampus & Profil

---

## 1. What Went Well (Apa yang Berjalan Sangat Baik)
1. **Pemisahan Model yang Bersih (Separation of Concerns)**:
   - Keputusan memisahkan tabel `students` (pangkalan registri kampus) dari tabel `users` (akun login marketplace) terbukti sangat tepat. Hal ini mencegah polusi data akun oleh data registri kampus dan membuat validasi integritas sangat jelas.
2. **Arsitektur Layanan Verifikasi Berbasis Interface**:
   - Pembuatan `StudentVerificationService` dan `StudentVerificationProviderInterface` memisahkan controller dari ketergantungan langsung ke database. Di kemudian hari, integrasi ke API eksternal (Pangkalan Data Dikti / SSO Kampus) dapat dilakukan tanpa merombak form registrasi.
3. **Penyelarasan Desain Antarmuka (UI Alignment)**:
   - Tampilan mobile-first yang mengacu pada wireframe Carousell berhasil diwujudkan menggunakan Tailwind CSS v4 dan Alpine.js, menciptakan antarmuka yang bersih, intuitif, dan responsif.
4. **Cakupan Pengujian Komprehensif (TDD/Automated Testing)**:
   - Sebanyak 37 skenario pengujian dengan 106 asersi dibangun dan berhasil melacak potensi celah keamanan (seperti percobaan pengubahan NIM atau email melalui form edit profil).
5. **Keamanan Bawaan Laravel**:
   - Pemanfaatan fitur keamanan bawaan Laravel (CSRF protection, Argon/Bcrypt password hashing, Signed URLs untuk verifikasi email, session fixation protection, dan Policy authorization) berjalan optimal.

---

## 2. What Could Be Improved (Apa yang Perlu Ditingkatkan)
1. **Penyempurnaan Algoritma Pencocokan Nama**:
   - Pada Sprint 1, pencocokan nama menggunakan kesamaan teks case-insensitive dan Levenshtein distance sederhana. Di masa depan, nama mahasiswa dengan gelar, singkatan nama marga, atau tanda baca khusus (seperti apostrof) dapat diuji dengan normalisasi fonetik (seperti Metaphone/Soundex bahasa Indonesia).
2. **Dukungan Kampus Multi-Kampus / Cabang**:
   - Saat ini tabel universitas menggunakan satu domain email primer. Beberapa universitas besar memiliki subdomain fakultas yang beragam (misalnya `@ft.undip.ac.id`, `@fk.undip.ac.id`). Skema dapat dikembangkan menjadi `hasMany` email domains per universitas jika diperlukan.
3. **Penanganan Rate Limiting**:
   - Rute kirim ulang verifikasi email telah dilindungi `throttle:6,1`. Pada Sprint 2, endpoint login dan registrasi sebaiknya ditambahkan rate limiting khusus untuk mitigasi serangan brute-force dan credential stuffing.

---

## 3. Action Items untuk Sprint 2
1. **Persiapan Modul Listing Produk (SRS-005 dkk)**:
   - Merancang migrasi tabel `categories`, `products`, dan `product_images` dengan relasi terhadap `users`.
2. **Integrasi Rating Dinamis**:
   - Menghubungkan kolom `seller_rating` dan `buyer_rating` pada `users` dengan tabel ulasan pasca-transaksi.
3. **Komponen Navigasi Bawah (Bottom Navigation)**:
   - Mengaktifkan tautan pada bottom navigation bar (Eksplor, Kategori, Jual, Pesan, Saya) yang pada Sprint 1 masih berupa representasi visual.
