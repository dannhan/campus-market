# Spesifikasi Marketplace Mahasiswa — CampusMarket

## Deskripsi Singkat

CampusMarket adalah platform marketplace khusus untuk mahasiswa yang memungkinkan jual-beli berbagai kebutuhan akademik dan kehidupan kampus. Platform ini melayani transaksi antar mahasiswa dalam satu universitas atau antar universitas, dengan fokus pada produk-produk seperti:

- Buku teks dan referensi akademik (baru/bekas)
- Peralatan laboratorium dan praktikum
- Laptop, gadget, dan perangkat teknologi
- Alat tulis dan perlengkapan kuliah
- Pakaian dan aksesoris
- Makanan dan minuman
- Jasa (les privat, pengetikan, desain, dll.)

Platform ini dirancang dengan mempertimbangkan keterbatasan budget mahasiswa, kebutuhan untuk transaksi yang aman antar sesama mahasiswa, dan kemudahan akses melalui perangkat mobile.

## Kebutuhan Fungsional

| Kode | Deskripsi |
|---|---|
| SRS-001 | Sistem harus memungkinkan mahasiswa mendaftar menggunakan email universitas yang valid dan memverifikasi status kemahasiswaan |
| SRS-002 | Sistem harus memverifikasi domain email universitas dan mengintegrasikan dengan database mahasiswa untuk autentikasi |
| SRS-003 | Sistem harus memungkinkan pengguna login/logout dengan opsi "Remember Me" untuk kemudahan akses |
| SRS-004 | Sistem harus menyediakan profil mahasiswa yang menampilkan nama, universitas, jurusan, dan rating sebagai penjual/pembeli |
| SRS-005 | Sistem harus memungkinkan pencarian produk berdasarkan nama, kategori, universitas, dan kisaran harga |
| SRS-006 | Sistem harus menyediakan filter berdasarkan kondisi barang (baru/bekas), lokasi, dan jenis transaksi (jual/sewa) |
| SRS-007 | Sistem harus menampilkan detail produk dengan foto, deskripsi, harga, kondisi, lokasi penjual, dan kontak |
| SRS-008 | Sistem harus memungkinkan mahasiswa menambahkan produk untuk dijual dengan upload foto dan deskripsi lengkap |
| SRS-009 | Sistem harus menyediakan kategori khusus seperti "Buku per Semester", "Perlengkapan Lab", "Gadget Second", dan "Jasa Mahasiswa" |
| SRS-010 | Sistem harus memungkinkan sistem tawar-menawar (bargaining) antara pembeli dan penjual |
| SRS-011 | Admin harus dapat mengelola kategori produk dan menambah kategori baru sesuai kebutuhan |
| SRS-012 | Admin harus dapat mengakses laporan transaksi dan statistik penggunaan platform |
| SRS-013 | Sistem harus memungkinkan dispute resolution dengan mediasi admin untuk transaksi bermasalah |
| SRS-014 | Sistem harus menyediakan metode pembayaran yang ramah mahasiswa (transfer bank, e-wallet, COD) |
| SRS-015 | Sistem harus menyediakan opsi pengiriman: COD, pickup di kampus, atau kurir |
| SRS-016 | Sistem harus menyediakan chat system untuk komunikasi langsung antara pembeli dan penjual |
| SRS-017 | Sistem harus memungkinkan pembeli menambahkan produk ke wishlist atau "bookmark" |
| SRS-018 | Sistem harus mengintegrasikan sistem pembayaran dengan escrow untuk keamanan transaksi |
| SRS-019 | Sistem harus menampilkan estimasi ongkos kirim berdasarkan lokasi kampus |
| SRS-020 | Sistem harus memungkinkan pelacakan status transaksi dari pemesanan hingga selesai |
| SRS-021 | Sistem harus menyediakan sistem rating dan review untuk penjual dan pembeli |
| SRS-022 | Sistem harus mengirim notifikasi push untuk pesan baru, status transaksi, dan penawaran |
| SRS-023 | Sistem harus menyediakan dashboard penjual untuk mengelola produk dan melihat statistik penjualan |
| SRS-024 | Sistem harus memungkinkan bulk upload produk untuk penjual yang memiliki banyak barang |
| SRS-025 | Sistem harus menyediakan fitur "Cari Barang" dimana pembeli bisa request barang yang dicari |
| SRS-026 | Admin harus dapat mengelola sistem reward/poin untuk pengguna aktif |
| SRS-027 | Admin harus dapat memverifikasi dan memoderasi produk yang dijual untuk mencegah penipuan |
| SRS-028 | Sistem harus dapat mengekspor data transaksi dalam format Excel untuk analisis admin |

## Kebutuhan Non-Fungsional

| Kode | Deskripsi | Kategori |
|---|---|---|
| NFRS-001 | Interface harus mobile-friendly dan responsive untuk penggunaan di smartphone | Usability |
| NFRS-002 | Waktu loading halaman tidak boleh lebih dari 3 detik | Performance |
| NFRS-003 | Sistem harus dapat menangani 1000 pengguna concurrent | Performance |
| NFRS-004 | Sistem harus tersedia 99.5% uptime (maksimal downtime 36 jam per tahun) | Reliability |
| NFRS-005 | Data pembayaran harus dienkripsi dengan standard SSL/TLS | Security |
| NFRS-006 | Sistem harus melakukan backup data otomatis setiap hari | Reliability |
| NFRS-007 | Interface harus mudah dipahami oleh mahasiswa tanpa training khusus | Usability |
| NFRS-008 | Sistem harus kompatibel dengan browser Chrome, Firefox, Safari versi terbaru | Usability |
| NFRS-009 | Sistem harus dapat menangani upload foto hingga 10MB per gambar | Performance |
| NFRS-010 | Data pribadi mahasiswa harus dilindungi sesuai regulasi perlindungan data | Security |

## Instruksi Pengembangan

Lakukan aktivitas requirement elicitation, analysis, dan design untuk pengembangan CampusMarket dengan fokus pada:

### A. Identifikasi Aktor

Identifikasi semua aktor yang terlibat dalam sistem marketplace mahasiswa ini, termasuk peran khusus seperti moderator konten dan admin universitas.

### B. Identifikasi Skenario

Buat tabel Scenario Use Case untuk setiap skenario yang mencakup:

- Skenario jual-beli standar
- Skenario tawar-menawar
- Skenario dispute resolution / mediasi komplain
- Skenario verifikasi mahasiswa

### C. Use Case Diagram

Gambarkan Use Case Diagram yang menunjukkan hubungan antar use case dengan mempertimbangkan fitur khusus marketplace mahasiswa.

### D. Identifikasi Kebutuhan Non-Fungsional

Gunakan tabel kebutuhan non-fungsional yang sudah disediakan di atas.

### E. Sequence Diagram

Buat sequence diagram detail untuk setiap communication diagram yang menunjukkan alur interaksi sistem.

### F. Class Diagram

Gambarkan class diagram yang menunjukkan atribut dan hubungan antar class, termasuk class khusus seperti:

- `Student`
- `University`
- `ProductCategory`
- `EscrowPayment`

## Fitur Unggulan CampusMarket

1. **Verifikasi Mahasiswa:** Integrasi dengan sistem informasi akademik
2. **Campus Radius:** Pencarian berdasarkan jarak kampus
3. **Semester Bazaar:** Event khusus per semester untuk clearance sale
4. **Study Buddy Finder:** Mencari teman belajar sambil jual-beli buku
5. **Green Campus:** Program tukar-tambah untuk produk ramah lingkungan
6. **Student Discount Hub:** Kumpulan diskon khusus mahasiswa dari merchant partner
