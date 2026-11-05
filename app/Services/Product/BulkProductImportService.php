<?php

namespace App\Services\Product;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class BulkProductImportService
{
    /**
     * Standard CSV header columns.
     */
    public const COLUMNS = [
        'title',
        'category',
        'price',
        'condition',
        'transaction_type',
        'bargainable',
        'location',
        'description',
        'delivery_methods',
        'image_url',
    ];

    /**
     * Generate sample CSV template with UTF-8 BOM for clean Excel viewing.
     */
    public function generateTemplateCsv(): string
    {
        $categories = Category::orderBy('id')->get();
        $cat1 = $categories->get(0)?->name ?? 'Buku per Semester';
        $cat2 = $categories->get(1)?->name ?? 'Perlengkapan Lab';
        $cat3 = $categories->get(2)?->name ?? 'Elektronik';

        $output = fopen('php://temp', 'r+');

        // Add UTF-8 BOM so Microsoft Excel renders accented/UTF-8 characters properly
        fwrite($output, "\xEF\xBB\xBF");

        // Write header
        fputcsv($output, self::COLUMNS);

        // Example Row 1
        fputcsv($output, [
            'Buku Kalkulus Purcell Edisi 9',
            $cat1,
            '65000',
            'bekas',
            'jual',
            'ya',
            'FSM Tembalang / Sekitar Kampus',
            'Buku referensi kalkulus lengkap, kondisi sangat mulus tanpa coretan pensil.',
            'pickup_point,courier',
            'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c',
        ]);

        // Example Row 2
        fputcsv($output, [
            'Jas Laboratorium Kimia Ukuran L',
            $cat2,
            '50000',
            'seperti_baru',
            'jual',
            'tidak',
            'Labtek V Kampus',
            'Jas lab lengan panjang warna putih bersih, sudah dicuci dan disetrika.',
            'pickup_point',
            '',
        ]);

        // Example Row 3
        fputcsv($output, [
            'Kalkulator Scientific Casio FX-991EX',
            $cat3,
            '185000',
            'bekas',
            'jual',
            'ya',
            'Perpustakaan Pusat',
            'Kalkulator fungsi lengkap untuk teknik dan sains, tombol empuk fungsi 100% normal.',
            'pickup_point,cod,courier',
            '',
        ]);

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }

    /**
     * Import multiple products from an uploaded CSV file.
     *
     * @return array{
     *     success: bool,
     *     imported_count: int,
     *     products: Collection<int, Product>,
     *     errors: array<int, string>,
     *     total_rows: int
     * }
     */
    public function import(User $user, UploadedFile|string $file): array
    {
        $filePath = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (! file_exists($filePath) || ! is_readable($filePath)) {
            throw new InvalidArgumentException('File CSV tidak dapat dibaca.');
        }

        $fileContent = file_get_contents($filePath);
        if ($fileContent === false || trim($fileContent) === '') {
            return [
                'success' => false,
                'imported_count' => 0,
                'products' => collect(),
                'errors' => ['File CSV kosong.'],
                'total_rows' => 0,
            ];
        }

        // Auto-detect delimiter (, or ; or \t or |)
        $delimiter = $this->detectDelimiter($fileContent);

        $handle = fopen('php://memory', 'r+');
        fwrite($handle, $fileContent);
        rewind($handle);

        // Read and normalize headers
        $headerLine = fgets($handle);
        if (! $headerLine) {
            fclose($handle);

            return [
                'success' => false,
                'imported_count' => 0,
                'products' => collect(),
                'errors' => ['Header kolom tidak ditemukan dalam file CSV.'],
                'total_rows' => 0,
            ];
        }

        // Remove UTF-8 BOM if present
        $headerLine = preg_replace('/^\xEF\xBB\xBF/', '', $headerLine);
        $rawHeaders = str_getcsv($headerLine, $delimiter);

        $headerMap = $this->mapHeaders($rawHeaders);

        // Required headers
        if (! isset($headerMap['title']) || ! isset($headerMap['category']) || ! isset($headerMap['price'])) {
            fclose($handle);

            return [
                'success' => false,
                'imported_count' => 0,
                'products' => collect(),
                'errors' => ["Header kolom wajib ('title', 'category', 'price') tidak lengkap dalam file."],
                'total_rows' => 0,
            ];
        }

        // Cache categories for lookup (by lower name, slug, and id)
        $categories = Category::all();
        $categoriesByLowerName = $categories->keyBy(fn ($c) => mb_strtolower(trim($c->name)));
        $categoriesBySlug = $categories->keyBy('slug');
        $categoriesById = $categories->keyBy('id');

        $importedProducts = collect();
        $errors = [];
        $rowNumber = 1; // header is line 1
        $dataRowsCount = 0;

        $rowsToInsert = [];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNumber++;

            // Skip empty rows
            if (! array_filter($row, fn ($val) => trim((string) $val) !== '')) {
                continue;
            }

            $dataRowsCount++;
            $parsed = $this->parseRow($row, $headerMap);

            // 1. Validate title
            $title = trim($parsed['title'] ?? '');
            if ($title === '') {
                $errors[] = "Baris {$rowNumber}: Judul produk wajib diisi.";

                continue;
            }
            if (mb_strlen($title) > 255) {
                $errors[] = "Baris {$rowNumber}: Judul produk maksimal 255 karakter.";

                continue;
            }

            // 2. Validate category
            $rawCategory = trim($parsed['category'] ?? '');
            if ($rawCategory === '') {
                $errors[] = "Baris {$rowNumber}: Kategori produk wajib diisi.";

                continue;
            }

            $category = $this->resolveCategory($rawCategory, $categoriesByLowerName, $categoriesBySlug, $categoriesById);
            if (! $category) {
                $errors[] = "Baris {$rowNumber}: Kategori '{$rawCategory}' tidak ditemukan dalam sistem.";

                continue;
            }

            // 3. Validate price
            $price = $this->parsePrice($parsed['price'] ?? '');
            if ($price === null || $price < 1000) {
                $errors[] = "Baris {$rowNumber}: Harga produk minimal Rp 1.000.";

                continue;
            }

            // 4. Validate condition
            $condition = $this->parseCondition($parsed['condition'] ?? '');

            // 5. Validate transaction type
            $transactionType = $this->parseTransactionType($parsed['transaction_type'] ?? '');

            // 6. Validate bargainable
            $bargainable = $this->parseBoolean($parsed['bargainable'] ?? '1');

            // 7. Location & Description
            $location = trim($parsed['location'] ?? '') ?: ($user->student?->university?->city ?? 'Kampus');
            $description = trim($parsed['description'] ?? '') ?: ("Dijual: {$title} dalam kondisi ".($condition === 'new' ? 'baru' : ($condition === 'like_new' ? 'seperti baru' : 'bekas')).'.');
            if (mb_strlen($description) > 5000) {
                $description = mb_substr($description, 0, 5000);
            }

            // 8. Delivery methods
            $deliveryMethods = $this->parseDeliveryMethods($parsed['delivery_methods'] ?? '');

            // 9. Optional image URL
            $imageUrl = trim($parsed['image_url'] ?? '');

            $rowsToInsert[] = [
                'user_id' => $user->id,
                'category_id' => $category->id,
                'title' => $title,
                'slug' => Product::generateUniqueSlug($title),
                'description' => $description,
                'price' => $price,
                'condition' => $condition,
                'transaction_type' => $transactionType,
                'status' => 'active',
                'location' => $location,
                'bargainable' => $bargainable,
                'delivery_methods' => $deliveryMethods,
                'image_url' => $imageUrl,
            ];
        }

        fclose($handle);

        // Perform transactional insertion for all valid products
        if (! empty($rowsToInsert)) {
            DB::transaction(function () use ($rowsToInsert, &$importedProducts) {
                foreach ($rowsToInsert as $data) {
                    $imageUrl = $data['image_url'];
                    unset($data['image_url']);

                    /** @var Product $product */
                    $product = Product::create($data);

                    if (! empty($imageUrl) && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                        $product->images()->create([
                            'path' => $imageUrl,
                            'is_primary' => true,
                            'sort_order' => 0,
                        ]);
                    }

                    $importedProducts->push($product);
                }
            });
        }

        return [
            'success' => $importedProducts->count() > 0,
            'imported_count' => $importedProducts->count(),
            'products' => $importedProducts,
            'errors' => $errors,
            'total_rows' => $dataRowsCount,
        ];
    }

    /**
     * Auto-detect CSV delimiter (, ; \t |).
     */
    protected function detectDelimiter(string $content): string
    {
        $firstLine = strtok($content, "\r\n") ?: $content;
        $delimiters = [',', ';', "\t", '|'];
        $counts = [];

        foreach ($delimiters as $delim) {
            $counts[$delim] = substr_count($firstLine, $delim);
        }

        arsort($counts);
        $best = array_key_first($counts);

        return $counts[$best] > 0 ? $best : ',';
    }

    /**
     * Map header names and their common synonyms to canonical keys.
     *
     * @param  array<int, string>  $rawHeaders
     * @return array<string, int>
     */
    protected function mapHeaders(array $rawHeaders): array
    {
        $synonyms = [
            'title' => ['title', 'judul', 'nama_produk', 'nama produk', 'nama barang', 'nama'],
            'category' => ['category', 'kategori', 'kategori_id', 'category_id', 'kategori produk'],
            'price' => ['price', 'harga', 'harga_produk', 'nominal'],
            'condition' => ['condition', 'kondisi', 'kondisi_barang'],
            'transaction_type' => ['transaction_type', 'jenis_transaksi', 'tipe_transaksi', 'tipe'],
            'bargainable' => ['bargainable', 'bisa_nego', 'nego', 'dapat_dinego'],
            'location' => ['location', 'lokasi', 'lokasi_kampus', 'tempat'],
            'description' => ['description', 'deskripsi', 'keterangan', 'detail'],
            'delivery_methods' => ['delivery_methods', 'metode_pengiriman', 'pengiriman', 'opsi_pengiriman'],
            'image_url' => ['image_url', 'foto', 'gambar', 'url_gambar', 'url_foto', 'image'],
        ];

        $map = [];

        foreach ($rawHeaders as $index => $header) {
            $clean = mb_strtolower(trim((string) $header));
            $clean = str_replace(['"', "'"], '', $clean);

            foreach ($synonyms as $canonicalKey => $aliases) {
                if (in_array($clean, $aliases, true)) {
                    $map[$canonicalKey] = $index;
                    break;
                }
            }
        }

        return $map;
    }

    /**
     * Map row columns using canonical headers.
     *
     * @param  array<int, string>  $row
     * @param  array<string, int>  $headerMap
     * @return array<string, string>
     */
    protected function parseRow(array $row, array $headerMap): array
    {
        $data = [];
        foreach ($headerMap as $key => $index) {
            $data[$key] = isset($row[$index]) ? trim((string) $row[$index]) : '';
        }

        return $data;
    }

    /**
     * Resolve category from ID, slug, or name.
     */
    protected function resolveCategory(
        string $raw,
        Collection $byLowerName,
        Collection $bySlug,
        Collection $byId
    ): ?Category {
        if (is_numeric($raw) && isset($byId[(int) $raw])) {
            return $byId[(int) $raw];
        }

        $lower = mb_strtolower(trim($raw));
        if (isset($byLowerName[$lower])) {
            return $byLowerName[$lower];
        }

        $slug = Str::slug($raw);
        if (isset($bySlug[$slug])) {
            return $bySlug[$slug];
        }

        return null;
    }

    /**
     * Parse numeric price from raw currency string (e.g. "Rp 65.000" or "65000").
     */
    protected function parsePrice(string $raw): ?float
    {
        $clean = trim($raw);
        if ($clean === '') {
            return null;
        }

        // Remove "Rp", whitespace, and non-digits/periods/commas
        $clean = preg_replace('/[^\d.,]/', '', $clean) ?? '';

        // If Indonesian formatted like "65.000" or "65.000,00"
        if (str_contains($clean, '.') && str_contains($clean, ',')) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } elseif (str_contains($clean, '.') && ! str_contains($clean, ',')) {
            // Check if dot is thousand separator: e.g. "65.000" (3 digits after dot)
            if (preg_match('/^\d{1,3}(\.\d{3})+$/', $clean)) {
                $clean = str_replace('.', '', $clean);
            }
        } elseif (str_contains($clean, ',') && ! str_contains($clean, '.')) {
            // E.g. "65,000" thousand separator
            if (preg_match('/^\d{1,3}(,\d{3})+$/', $clean)) {
                $clean = str_replace(',', '', $clean);
            } else {
                $clean = str_replace(',', '.', $clean);
            }
        }

        return is_numeric($clean) ? (float) $clean : null;
    }

    /**
     * Parse condition to valid enum: new, like_new, used.
     */
    protected function parseCondition(string $raw): string
    {
        $lower = mb_strtolower(trim($raw));

        return match ($lower) {
            'baru', 'new' => 'new',
            'seperti_baru', 'seperti baru', 'like_new', 'like new', 'mulus' => 'like_new',
            default => 'used',
        };
    }

    /**
     * Parse transaction type: sale or rent.
     */
    protected function parseTransactionType(string $raw): string
    {
        $lower = mb_strtolower(trim($raw));

        return match ($lower) {
            'sewa', 'rent', 'rental' => 'rent',
            default => 'sale',
        };
    }

    /**
     * Parse boolean string.
     */
    protected function parseBoolean(string $raw): bool
    {
        $lower = mb_strtolower(trim($raw));

        if (in_array($lower, ['0', 'tidak', 'false', 'no', 't', 'bukan'], true)) {
            return false;
        }

        return true;
    }

    /**
     * Parse delivery methods array from comma/pipe-separated string.
     *
     * @return array<int, string>
     */
    protected function parseDeliveryMethods(string $raw): array
    {
        if (trim($raw) === '') {
            return ['pickup_point', 'courier'];
        }

        $items = preg_split('/[,;|]+/', $raw) ?: [];
        $methods = [];

        foreach ($items as $item) {
            $clean = mb_strtolower(trim($item));
            if (in_array($clean, ['pickup', 'pickup_point', 'ambil', 'kampus', 'ambil_sendiri'], true)) {
                $methods[] = 'pickup_point';
            } elseif (in_array($clean, ['cod', 'ketemuan', 'tunai'], true)) {
                $methods[] = 'cod';
            } elseif (in_array($clean, ['courier', 'kurir', 'ekspedisi', 'antar'], true)) {
                $methods[] = 'courier';
            }
        }

        $methods = array_unique($methods);

        return ! empty($methods) ? array_values($methods) : ['pickup_point', 'courier'];
    }
}
