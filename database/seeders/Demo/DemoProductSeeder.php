<?php

namespace Database\Seeders\Demo;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DemoProductSeeder extends Seeder
{
    /**
     * Demo listings, three per demo seller across all existing categories.
     *
     * @var array<int, array{slug: string, seller: string, category: string, title: string, description: string, price: int, condition: string, transaction_type: string, status: string, location: string, bargainable: bool, views_count: int}>
     */
    public const PRODUCTS = [
        [
            'slug' => 'demo-buku-struktur-data-undip',
            'seller' => 'demo.raka@students.undip.ac.id',
            'category' => 'buku-per-semester',
            'title' => 'Buku Struktur Data dan Algoritma Edisi Revisi',
            'description' => 'Buku teks struktur data untuk mahasiswa informatika. Edisi revisi, kondisi masih rapi dengan sedikit coretan pensil di bab 3.',
            'price' => 85000,
            'condition' => 'used',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Kampus Tembalang, UNDIP',
            'bargainable' => true,
            'views_count' => 34,
        ],
        [
            'slug' => 'demo-breadboard-kit-praktikum-undip',
            'seller' => 'demo.raka@students.undip.ac.id',
            'category' => 'perlengkapan-lab',
            'title' => 'Breadboard dan Kit Praktikum Elektronika',
            'description' => 'Satu set breadboard, kabel jumper, resistor, dan LED untuk praktikum elektronika dasar. Lengkap dan sudah dicek berfungsi.',
            'price' => 120000,
            'condition' => 'like_new',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Kampus Tembalang, UNDIP',
            'bargainable' => true,
            'views_count' => 21,
        ],
        [
            'slug' => 'demo-les-privat-algoritma-undip',
            'seller' => 'demo.raka@students.undip.ac.id',
            'category' => 'jasa-mahasiswa',
            'title' => 'Jasa Les Privat Algoritma dan Pemrograman',
            'description' => 'Les privat intensif untuk persiapan UTS/UAS algoritma dan pemrograman. Bisa online atau tatap muka di sekitar Tembalang.',
            'price' => 75000,
            'condition' => 'new',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Kampus Tembalang, UNDIP',
            'bargainable' => false,
            'views_count' => 47,
        ],
        [
            'slug' => 'demo-kalkulator-casio-fx991-undip',
            'seller' => 'demo.maya@students.undip.ac.id',
            'category' => 'alat-tulis',
            'title' => 'Kalkulator Casio FX-991EX Classwiz',
            'description' => 'Kalkulator scientific lengkap dengan fungsi matriks dan statistik. Layar masih jernih, tombol responsif, bonus baterai cadangan.',
            'price' => 275000,
            'condition' => 'used',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Kampus Pleburan, UNDIP',
            'bargainable' => true,
            'views_count' => 18,
        ],
        [
            'slug' => 'demo-jaket-lab-manajemen-undip',
            'seller' => 'demo.maya@students.undip.ac.id',
            'category' => 'pakaian-aksesoris',
            'title' => 'Jaket Laboratorium Manajemen',
            'description' => 'Jaket lab ukuran M, dipakai dua semester dan sudah dicuci bersih. Cocok untuk praktikum atau kegiatan organisasi.',
            'price' => 95000,
            'condition' => 'like_new',
            'transaction_type' => 'sale',
            'status' => 'sold',
            'location' => 'Kampus Pleburan, UNDIP',
            'bargainable' => true,
            'views_count' => 12,
        ],
        [
            'slug' => 'demo-buku-pengantar-akuntansi-undip',
            'seller' => 'demo.maya@students.undip.ac.id',
            'category' => 'buku-per-semester',
            'title' => 'Buku Pengantar Akuntansi Edisi IFRS',
            'description' => 'Buku pengantar akuntansi berbasis IFRS, edisi terbaru. Sampul sedikit lecek, isi bersih tanpa highlight.',
            'price' => 110000,
            'condition' => 'used',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Kampus Pleburan, UNDIP',
            'bargainable' => true,
            'views_count' => 29,
        ],
        [
            'slug' => 'demo-lenovo-thinkpad-t480-ugm',
            'seller' => 'demo.bagas@mail.ugm.ac.id',
            'category' => 'gadget-second',
            'title' => 'Laptop Lenovo ThinkPad T480 RAM 16GB',
            'description' => 'ThinkPad T480 dengan RAM 16GB dan SSD 512GB. Baterai masih awet sekitar 5 jam, keyboard nyaman untuk coding. Charger original.',
            'price' => 4200000,
            'condition' => 'used',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Kampus UGM, Yogyakarta',
            'bargainable' => true,
            'views_count' => 63,
        ],
        [
            'slug' => 'demo-helm-proyek-lab-ugm',
            'seller' => 'demo.bagas@mail.ugm.ac.id',
            'category' => 'perlengkapan-lab',
            'title' => 'Helm Proyek Laboratorium',
            'description' => 'Helm proyek standar K3 untuk praktikum di lab mesin. Baru dipakai sekali, masih mulus dan tali pengait lengkap.',
            'price' => 45000,
            'condition' => 'like_new',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Kampus UGM, Yogyakarta',
            'bargainable' => true,
            'views_count' => 9,
        ],
        [
            'slug' => 'demo-sewa-proyektor-mini-ugm',
            'seller' => 'demo.bagas@mail.ugm.ac.id',
            'category' => 'elektronik',
            'title' => 'Sewa Proyektor Mini untuk Presentasi',
            'description' => 'Proyektor mini 1080p untuk presentasi kelompok atau acara organisasi. Termasuk kabel HDMI dan remote, tarif per hari.',
            'price' => 60000,
            'condition' => 'used',
            'transaction_type' => 'rent',
            'status' => 'active',
            'location' => 'Kampus UGM, Yogyakarta',
            'bargainable' => true,
            'views_count' => 15,
        ],
        [
            'slug' => 'demo-buku-public-speaking-ugm',
            'seller' => 'demo.tari@mail.ugm.ac.id',
            'category' => 'buku-per-semester',
            'title' => 'Buku Public Speaking dan Retorika',
            'description' => 'Buku referensi public speaking untuk mata kuliah komunikasi. Kondisi 90% baru, tidak ada tulisan di dalamnya.',
            'price' => 70000,
            'condition' => 'used',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Kampus UGM, Yogyakarta',
            'bargainable' => true,
            'views_count' => 11,
        ],
        [
            'slug' => 'demo-jasa-desain-poster-ugm',
            'seller' => 'demo.tari@mail.ugm.ac.id',
            'category' => 'jasa-mahasiswa',
            'title' => 'Jasa Desain Poster dan Feed Instagram',
            'description' => 'Desain poster acara atau feed Instagram untuk organisasi kampus. Revisi maksimal dua kali, pengerjaan 2-3 hari.',
            'price' => 50000,
            'condition' => 'new',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Kampus UGM, Yogyakarta',
            'bargainable' => true,
            'views_count' => 25,
        ],
        [
            'slug' => 'demo-sewa-tripod-ring-light-ugm',
            'seller' => 'demo.tari@mail.ugm.ac.id',
            'category' => 'elektronik',
            'title' => 'Sewa Tripod dan Ring Light untuk Konten',
            'description' => 'Paket tripod kamera dan ring light untuk kebutuhan foto atau video tugas. Bisa disewa harian, ambil sendiri di Jogja.',
            'price' => 40000,
            'condition' => 'like_new',
            'transaction_type' => 'rent',
            'status' => 'active',
            'location' => 'Kampus UGM, Yogyakarta',
            'bargainable' => true,
            'views_count' => 8,
        ],
        [
            'slug' => 'demo-keyboard-mekanikal-65-itb',
            'seller' => 'demo.yoga@mahasiswa.itb.ac.id',
            'category' => 'elektronik',
            'title' => 'Keyboard Mekanikal 65% Hot-swap',
            'description' => 'Keyboard mekanikal 65% dengan switch linear dan fitur hot-swap. Sudah dilengkapi keycap PBT, kabel USB-C original.',
            'price' => 450000,
            'condition' => 'like_new',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Kampus ITB Ganesha, Bandung',
            'bargainable' => true,
            'views_count' => 31,
        ],
        [
            'slug' => 'demo-buku-clean-code-itb',
            'seller' => 'demo.yoga@mahasiswa.itb.ac.id',
            'category' => 'buku-per-semester',
            'title' => 'Buku Clean Code Bahasa Indonesia',
            'description' => 'Buku Clean Code versi terjemahan Indonesia. Cocok untuk mata kuliah rekayasa perangkat lunak, kondisi masih bagus.',
            'price' => 95000,
            'condition' => 'used',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Kampus ITB Ganesha, Bandung',
            'bargainable' => true,
            'views_count' => 22,
        ],
        [
            'slug' => 'demo-jasa-bimbingan-tubes-itb',
            'seller' => 'demo.yoga@mahasiswa.itb.ac.id',
            'category' => 'jasa-mahasiswa',
            'title' => 'Jasa Bimbingan Tugas Besar',
            'description' => 'Bimbingan konsep dan debugging tugas besar pemrograman. Sesi dua jam, bisa online atau di sekitar Ganesha.',
            'price' => 150000,
            'condition' => 'new',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Kampus ITB Ganesha, Bandung',
            'bargainable' => true,
            'views_count' => 40,
        ],
        [
            'slug' => 'demo-wacom-intuos-s-itb',
            'seller' => 'demo.sinta@mahasiswa.itb.ac.id',
            'category' => 'gadget-second',
            'title' => 'Drawing Tablet Wacom Intuos S',
            'description' => 'Drawing tablet Wacom Intuos S dengan stylus dan kabel. Aktif dipakai untuk tugas ilustrasi, kondisi mulus tanpa goresan.',
            'price' => 650000,
            'condition' => 'used',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Kampus ITB Ganesha, Bandung',
            'bargainable' => true,
            'views_count' => 27,
        ],
        [
            'slug' => 'demo-set-marker-warna-itb',
            'seller' => 'demo.sinta@mahasiswa.itb.ac.id',
            'category' => 'alat-tulis',
            'title' => 'Set Marker Warna dan Pensil Sketsa',
            'description' => 'Set marker warna 24 pcs dan pensil sketsa untuk mata kuliah desain. Beberapa marker sudah mulai kering, sisanya masih lancar.',
            'price' => 180000,
            'condition' => 'like_new',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Kampus ITB Ganesha, Bandung',
            'bargainable' => true,
            'views_count' => 14,
        ],
        [
            'slug' => 'demo-jasa-editing-video-itb',
            'seller' => 'demo.sinta@mahasiswa.itb.ac.id',
            'category' => 'jasa-mahasiswa',
            'title' => 'Jasa Editing Video Tugas Kuliah',
            'description' => 'Editing video presentasi atau dokumentasi acara kampus. Termasuk color grading sederhana dan subtitle, durasi maksimal 10 menit.',
            'price' => 100000,
            'condition' => 'new',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Kampus ITB Ganesha, Bandung',
            'bargainable' => true,
            'views_count' => 19,
        ],
        [
            'slug' => 'demo-monitor-lg-24-ui',
            'seller' => 'demo.aldi@ui.ac.id',
            'category' => 'elektronik',
            'title' => 'Monitor LG 24 Inci Full HD',
            'description' => 'Monitor LG 24 inci IPS Full HD dengan port HDMI dan VGA. Tidak ada dead pixel, cocok untuk belajar dan kerja tugas.',
            'price' => 1250000,
            'condition' => 'used',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Kampus UI Depok',
            'bargainable' => true,
            'views_count' => 26,
        ],
        [
            'slug' => 'demo-buku-ai-modern-approach-ui',
            'seller' => 'demo.aldi@ui.ac.id',
            'category' => 'buku-per-semester',
            'title' => 'Buku Artificial Intelligence: A Modern Approach',
            'description' => 'Buku teks kecerdasan buatan edisi internasional. Masih tersegel rapi, belum pernah dipakai untuk kuliah.',
            'price' => 320000,
            'condition' => 'like_new',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Kampus UI Depok',
            'bargainable' => true,
            'views_count' => 17,
        ],
        [
            'slug' => 'demo-rice-cooker-mini-ui',
            'seller' => 'demo.aldi@ui.ac.id',
            'category' => 'elektronik',
            'title' => 'Rice Cooker Mini Anak Kos',
            'description' => 'Rice cooker mini 1,2 liter, cocok untuk anak kos. Sudah tidak dipakai karena pindah domisili, kondisi berfungsi normal.',
            'price' => 150000,
            'condition' => 'used',
            'transaction_type' => 'sale',
            'status' => 'withdrawn',
            'location' => 'Kampus UI Depok',
            'bargainable' => true,
            'views_count' => 6,
        ],
        [
            'slug' => 'demo-paket-snack-mahasiswa-ui',
            'seller' => 'demo.putri@ui.ac.id',
            'category' => 'makanan-minuman',
            'title' => 'Paket Snack Mahasiswa untuk Belajar',
            'description' => 'Paket snack ringan untuk menemani belajar kelompok. Isi 10 bungkus aneka camilan, bisa COD di sekitar kampus.',
            'price' => 25000,
            'condition' => 'new',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Kampus UI Depok',
            'bargainable' => true,
            'views_count' => 10,
        ],
        [
            'slug' => 'demo-buku-manajemen-keuangan-ui',
            'seller' => 'demo.putri@ui.ac.id',
            'category' => 'buku-per-semester',
            'title' => 'Buku Manajemen Keuangan',
            'description' => 'Buku manajemen keuangan untuk mahasiswa akuntansi. Ada highlight stabilo di beberapa bab, sisanya masih bersih.',
            'price' => 90000,
            'condition' => 'used',
            'transaction_type' => 'sale',
            'status' => 'active',
            'location' => 'Kampus UI Depok',
            'bargainable' => true,
            'views_count' => 13,
        ],
        [
            'slug' => 'demo-jasa-ketik-skripsi-ui',
            'seller' => 'demo.putri@ui.ac.id',
            'category' => 'jasa-mahasiswa',
            'title' => 'Jasa Ketik dan Format Skripsi',
            'description' => 'Jasa pengetikan dan perapian format skripsi sesuai pedoman kampus. Termasuk daftar isi otomatis dan pengecekan typo.',
            'price' => 200000,
            'condition' => 'new',
            'transaction_type' => 'sale',
            'status' => 'sold',
            'location' => 'Kampus UI Depok',
            'bargainable' => true,
            'views_count' => 33,
        ],
    ];

    /**
     * Color pairs used for the generated placeholder images.
     *
     * @var array<int, array{0: array{0: int, 1: int, 2: int}, 1: array{0: int, 1: int, 2: int}}>
     */
    protected const PALETTES = [
        [[13, 148, 136], [15, 76, 92]],
        [[59, 130, 246], [30, 58, 138]],
        [[245, 158, 11], [146, 64, 14]],
        [[168, 85, 247], [88, 28, 135]],
        [[236, 72, 153], [131, 24, 67]],
        [[34, 197, 94], [20, 83, 45]],
        [[20, 184, 166], [17, 94, 89]],
        [[249, 115, 22], [124, 45, 18]],
    ];

    public function run(): void
    {
        $canRenderImages = extension_loaded('gd');

        if (! $canRenderImages) {
            $this->command?->warn('GD extension not found: demo products will be seeded without photos.');
        }

        foreach (self::PRODUCTS as $index => $data) {
            $seller = User::where('email', $data['seller'])->firstOrFail();
            $category = Category::where('slug', $data['category'])->firstOrFail();

            $product = Product::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'user_id' => $seller->id,
                    'category_id' => $category->id,
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'condition' => $data['condition'],
                    'transaction_type' => $data['transaction_type'],
                    'status' => $data['status'],
                    'location' => $data['location'],
                    'bargainable' => $data['bargainable'],
                    'views_count' => $data['views_count'],
                ]
            );

            if ($canRenderImages && $product->images()->doesntExist()) {
                $this->seedImages($product, $category, ($index % 3) + 1, $index);
            }
        }
    }

    /**
     * Attach generated placeholder photos; the first photo becomes the cover.
     */
    protected function seedImages(Product $product, Category $category, int $count, int $variant): void
    {
        for ($index = 0; $index < $count; $index++) {
            $path = sprintf('products/demo/%s-%d.jpg', $product->slug, $index + 1);

            Storage::disk('public')->put(
                $path,
                $this->renderPlaceholder($product->title, $category->name, $variant + $index)
            );

            $product->images()->create([
                'path' => $path,
                'is_primary' => $index === 0,
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * Render an 800x600 JPEG with the product title and category name.
     */
    protected function renderPlaceholder(string $title, string $category, int $variant): string
    {
        $width = 400;
        $height = 300;

        $canvas = imagecreatetruecolor($width, $height);

        [$from, $to] = self::PALETTES[$variant % count(self::PALETTES)];

        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / ($height - 1);
            $color = imagecolorallocate(
                $canvas,
                (int) ($from[0] + ($to[0] - $from[0]) * $ratio),
                (int) ($from[1] + ($to[1] - $from[1]) * $ratio),
                (int) ($from[2] + ($to[2] - $from[2]) * $ratio),
            );
            imageline($canvas, 0, $y, $width, $y, $color);
        }

        $glow = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledellipse($canvas, 350, 45, 120, 120, $glow);
        imagefilledellipse($canvas, 25, 285, 90, 90, $glow);

        $white = imagecolorallocate($canvas, 255, 255, 255);
        $shadow = imagecolorallocate($canvas, 15, 23, 42);

        $lines = explode("\n", wordwrap($title, 34));
        $lineHeight = imagefontheight(5) + 6;
        $startY = (int) (($height - count($lines) * $lineHeight) / 2) - 16;

        foreach ($lines as $line => $text) {
            $x = (int) (($width - strlen($text) * imagefontwidth(5)) / 2);
            $y = $startY + $line * $lineHeight;

            imagestring($canvas, 5, $x + 1, $y + 1, $text, $shadow);
            imagestring($canvas, 5, $x, $y, $text, $white);
        }

        $label = strtoupper($category);
        imagestring($canvas, 3, (int) (($width - strlen($label) * imagefontwidth(3)) / 2), 238, $label, $white);
        imagestring($canvas, 2, (int) (($width - strlen('CAMPUSMARKET DEMO') * imagefontwidth(2)) / 2), 262, 'CAMPUSMARKET DEMO', $white);

        $scaled = imagescale($canvas, 800, 600);

        ob_start();
        imagejpeg($scaled, null, 88);
        $binary = (string) ob_get_clean();

        imagedestroy($canvas);
        imagedestroy($scaled);

        return $binary;
    }
}
