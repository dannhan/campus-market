# CampusMarket — Progress Report 5 (UTS)

**Proyek**: CampusMarket  
**Tagline**: "Dari Mahasiswa, Untuk Mahasiswa"  
**Sprint**: Sprint 2  
**Tanggal**: 17 Oktober 2026  
**Penulis**: Ahmad Danun Hanani (Lead Software Engineer)

---

## 1. Tujuan Sprint
Tujuan utama Sprint 2 adalah mengubah fondasi autentikasi Sprint 1 menjadi marketplace yang fungsional, mencakup:
- Etalase produk publik dengan pencarian dan filter yang kaya.
- Manajemen listing produk oleh mahasiswa, termasuk unggah foto produk.
- Kategori produk khusus kebutuhan kampus.
- Tawar-menawar harga (bargaining) antara pembeli dan penjual.

---

## 2. Requirement yang Diimplementasikan (Pemetaan SRS)

Implementasi Sprint 2 memenuhi 6 butir spesifikasi kebutuhan perangkat lunak (Software Requirements Specification):

| ID Kebutuhan | Deskripsi SRS | Status Implementasi |
|---|---|---|
| **SRS-005** | Pencarian produk berdasarkan nama, kategori, universitas, dan kisaran harga. | **SELESAI (100%)** |
| **SRS-006** | Filter berdasarkan kondisi barang (baru/bekas), lokasi, dan jenis transaksi (jual/sewa). | **SELESAI (100%)** |
| **SRS-007** | Detail produk dengan foto, deskripsi, harga, kondisi, lokasi penjual, dan kontak. | **SELESAI (100%)** |
| **SRS-008** | Mahasiswa dapat menambahkan produk untuk dijual dengan upload foto dan deskripsi lengkap. | **SELESAI (100%)** |
| **SRS-009** | Kategori khusus seperti "Buku per Semester", "Perlengkapan Lab", "Gadget Second", dan "Jasa Mahasiswa". | **SELESAI (100%)** |
| **SRS-010** | Tawar-menawar (bargaining) antara pembeli dan penjual. | **SELESAI (100%)** |

*Catatan: Fitur transaksi lanjutan (checkout, escrow payment, pengiriman, chat, review) secara ketat tidak disertakan pada sprint ini dan menjadi rencana Sprint 3.*

---

## 3. Arsitektur Sistem
Aplikasi tetap dibangun di atas Laravel dengan pola *Domain-Driven & Service-Oriented*, kini diperluas ke domain katalog:
- **Model**: `Category`, `Product`, `ProductImage`, `Offer` (bersama model Sprint 1).
- **Query Scopes**: `Product` menyediakan `scopeSearch`, `scopeForUniversity`, `scopePriceBetween`, dan `scopeActive` sehingga logika filter dapat diuji dan digunakan ulang.
- **Layanan Bargaining**: `App\Services\Bargaining\OfferService` merangkum seluruh aturan negosiasi (validasi penawaran, terima/tolak/counter, penarikan) di luar controller.
- **Kebijakan Otorisasi**: `ProductPolicy` memastikan hanya pemilik listing yang dapat mengedit atau menghapus.
- **Form Request**: `StoreProductRequest`, `UpdateProductRequest`, `StoreOfferRequest`, `RespondOfferRequest`.
- **Presentasi Frontend**: Blade Templates mobile-first dengan Tailwind CSS v4 dan Alpine.js (galeri foto dan tab inbox tawaran).

---

## 4. Database
Empat migrasi baru menambah tiga entitas utama dan tabel gambar:
1. `categories`: `name`, `slug` (unik), `icon`, `description`, `sort_order`.
2. `products`: `user_id` (penjual), `category_id`, `title`, `slug` (unik, digenerate otomatis), `description`, `price decimal(12,2)`, `condition` (`new`/`like_new`/`used`), `transaction_type` (`sale`/`rent`), `status` (`active`/`sold`/`withdrawn`), `location`, `bargainable`, `views_count`.
3. `product_images`: `product_id`, `path`, `is_primary` (foto sampul), `sort_order`.
4. `offers`: `product_id`, `user_id` (pihak pengusul harga), `parent_offer_id` (thread tawaran balasan, self-referencing), `offered_price`, `message`, `status` (`pending`/`accepted`/`rejected`/`countered`/`withdrawn`).

Relasi yang terjalin:
```
User (1) ───< Product (*) >─── (1) Category
Product (1) ───< ProductImage (*)
Product (1) ───< Offer (*) >─── (1) User (pembeli)
Offer   (1) ───< Offer (*)   (thread counter-offer, parent_offer_id)
```

Indeks disiapkan untuk pola akses etalase: `(status, category_id)` dan `price` pada `products`; `(product_id, status)` dan `(user_id, status)` pada `offers`.

---

## 5. Etalase & Pencarian (SRS-005, SRS-006)
- Rute: `GET /products` (publik, tidak memerlukan login).
- Pencarian teks bebas (`q`) pada judul dan deskripsi.
- Filter gabungan: kategori (slug), universitas penjual (via relasi `user.student.university` — konsep *Campus Radius*), kondisi barang, jenis transaksi, dan rentang harga minimum/maksimum.
- Pengurangan urutan: terbaru (default), harga terendah, harga tertinggi; paginasi 12 produk per halaman dengan pelestarian parameter filter (`withQueryString`).
- Nilai filter yang tidak valid (mis. kondisi di luar enumerasi) diabaikan secara aman tanpa error.

---

## 6. Detail Produk (SRS-007)
- Rute: `GET /products/{product}` (publik; listing non-aktif hanya terlihat oleh pemilik).
- Galeri foto interaktif (Alpine.js) dengan foto sampul dan penanda halaman gambar.
- Informasi lengkap: harga terformat (`Rp 150.000`), badge kondisi, jenis transaksi, lokasi penjual, jumlah dilihat, dan badge khusus "Bisa nego".
- Kartu penjual menampilkan avatar, nama, universitas, jurusan, dan rating penjual.
- Penghitungan `views_count` bertambah setiap kunjungan detail.

---

## 7. Manajemen Listing (SRS-008)
- Rute terproteksi middleware `auth` + `verified`: `GET /products/create`, `POST /products`, `GET /products/{product}/edit`, `PUT/PATCH /products/{product}`, `DELETE /products/{product}`.
- Form unggah mendukung hingga **5 foto** (JPG/PNG/WEBP, maksimal 5MB per foto), disimpan ke disk `public` di bawah `products/`; foto pertama otomatis menjadi sampul.
- Validasi ketat: judul, deskripsi (maks. 5000 karakter), kategori valid, harga minimal Rp 1.000, kondisi dan jenis transaksi sesuai enumerasi.
- Slug produk digenerate otomatis dan unik (hook `creating` pada model, dengan fallback random suffix saat bentrokan).
- Edit memungkinkan penggantian seluruh foto; berkas lama dihapus fisik dari storage. Hapus listing menghapus foto fisik dan baris terkait secara transaksional (FK `cascadeOnDelete` untuk gambar dan tawaran).
- Otorisasi via `ProductPolicy`: pengguna lain mendapat 403.

---

## 8. Kategori Produk (SRS-009)
- `CategorySeeder` menyiapkan 8 kategori khas kampus: **Buku per Semester**, **Perlengkapan Lab**, **Gadget Second**, **Jasa Mahasiswa**, Alat Tulis, Pakaian & Aksesoris, Elektronik, dan Makanan & Minuman.
- Kategori tampil sebagai *chips* yang dapat digulir horizontal pada halaman etalase.
- FK `category_id` memakai `restrictOnDelete` agar kategori yang masih dipakai produk tidak terhapus sembarangan.

---

## 9. Tawar-Menawar / Bargaining (SRS-010)
- Rute terproteksi: `POST /products/{product}/offers` (ajukan), `GET /offers` (inbox), `POST /offers/{offer}/respond` (terima/tolak/counter), `POST /offers/{offer}/cancel` (batalkan).
- **Ajukan tawaran**: pembeli mengirim harga tawaran (min. Rp 1.000) beserta pesan opsional; ditolak jika produk bukan milik sendiri, produk tidak aktif, tidak `bargainable`, atau masih ada penawaran pending di produk yang sama.
- **Terima (khusus penjual)**: penawaran menjadi `accepted`, produk menjadi `sold`, dan seluruh penawaran pending lainnya otomatis `rejected` — semua dalam satu transaksi database.
- **Tolak (khusus penjual)**: penawaran menjadi `rejected`, produk tetap aktif.
- **Counter**: salah satu pihak mengusulkan harga baru; penawaran lama menjadi `countered` dan penawaran baru tertaut melalui `parent_offer_id` membentuk thread negosiasi bertingkat.
- **Batalkan**: pemberi tawaran menarik penawaran pending miliknya sendiri.
- Inbox tawaran (`GET /offers`) menampilkan dua tab: tawaran masuk (sebagai penjual) dan tawaran terkirim (sebagai pembeli), lengkap dengan aksi respons.
- Seluruh aturan domain berada di `OfferService` dengan pesan kesalahan berbahasa Indonesia yang muncul sebagai flash message.

---

## 10. Testing
Pengujian otomatis dilakukan menggunakan PHPUnit dengan total **73 test cases** dan **244 assertions** (36 skenario baru pada Sprint 2) dengan tingkat kelulusan 100%:
- `ProductManagementTest`: 8 tests (publikasi produk dengan foto, validasi field, larangan lebih dari 5 foto, edit oleh pemilik, penolakan akses non-pemilik, hapus produk + berkas fisik, penggantian foto saat edit, proteksi form untuk tamu).
- `ProductBrowsingTest`: 10 tests (penjelajahan publik, detail produk + penghitung dilihat, listing terjual tersembunyi, pencarian judul/deskripsi, filter kategori/universitas/kondisi/jenis transaksi/rentang harga, pengurutan harga, nilai filter tidak valid).
- `BargainingTest`: 18 tests (pengajuan tawaran, larangan tawar produk sendiri, larangan tawaran ganda, tawar ulang setelah ditolak, produk non-bargainable, terima + penolakan otomatis penawaran bersaing, larangan pembeli menerima/menolak, counter bertingkat dua arah, proteksi pihak luar, validasi aksi dan harga counter, pembatalan, inbox dua tab, larangan menawar produk terjual).

---

## 11. Hasil Implementasi
Seluruh komponen Sprint 2 telah selesai dibuat, diintegrasikan, dan diuji secara menyeluruh. Alur pengguna utama kini lengkap dari pendaftaran terverifikasi hingga negosiasi harga: tamu dapat menjelajah dan mencari produk, mahasiswa terverifikasi dapat mengunggah listing dengan foto, dan calon pembeli dapat menawar harga dengan alur counter-offer yang benar.

---

## 12. Kendala yang Dihadapi & Solusi
1. **Route `{product}` Menelan URL `/products/create`**:
   - *Kendala*: Rute publik `GET /products/{product}` yang terdaftar lebih awal menangkap URL statis `/products/create`, mengembalikan 404.
   - *Solusi*: Rute detail produk dipindah ke akhir registrasi rute dan dikunci dengan `whereNumber('product')`, sehingga segmen statis selalu menang.
2. **Slug Kosong pada Pembuatan Model Langsung**:
   - *Kendala*: `Product::create()` tanpa kolom slug melanggar constraint `NOT NULL` (terdeteksi test).
   - *Solusi*: Hook `creating` pada model `Product` menggenerate slug unik otomatis dari judul bila belum disediakan.
3. **Redirect Hasil Counter-Off**:
   - *Kendala*: Redirect respons penawaran menyertakan model pada rute tanpa parameter, menghasilkan URL rusak (`/offers?2`).
   - *Solusi*: Cabang redirect dipisah antara counter (ke inbox `/offers`) dan terima/tolak (ke detail produk).

---

## 13. Rencana Sprint Berikutnya (Sprint 3)
Pada Sprint 3, pengembangan beralih ke transaksi dan komunikasi:
1. Keranjang dan alur checkout dengan metode pembayaran ramah mahasiswa (transfer bank, e-wallet, COD) — SRS-014.
2. Escrow payment untuk keamanan transaksi — SRS-018.
3. Opsi pengiriman (COD, pickup di kampus, kurir) beserta estimasi ongkos kirim — SRS-015, SRS-019.
4. Chat system antar pembeli dan penjual — SRS-016.
5. Pelacakan status transaksi dan rating/review penjual-pembeli — SRS-020, SRS-021.
