<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\Product\BulkProductImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class SellerBulkProductController extends Controller
{
    public function __construct(
        protected BulkProductImportService $importService
    ) {}

    /**
     * Display the bulk product upload form and guidelines.
     */
    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        return view('seller.bulk-upload', [
            'categories' => $categories,
        ]);
    }

    /**
     * Download the standard CSV template for bulk product upload.
     */
    public function template(): Response
    {
        $csvContent = $this->importService->generateTemplateCsv();

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_bulk_upload_produk.csv"',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Process the uploaded CSV file and bulk-import products.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:5120', // 5MB max
            ],
        ], [
            'csv_file.required' => 'Pilih file CSV terlebih dahulu untuk diunggah.',
            'csv_file.file' => 'File yang diunggah tidak valid.',
            'csv_file.mimes' => 'Format file harus berupa CSV (.csv) atau teks (.txt).',
            'csv_file.max' => 'Ukuran file CSV maksimal 5MB.',
        ]);

        $file = $request->file('csv_file');
        $result = $this->importService->import($request->user(), $file);

        if ($result['imported_count'] > 0) {
            $message = "Berhasil mengunggah {$result['imported_count']} produk ke etalase toko Anda.";

            if (! empty($result['errors'])) {
                return redirect()
                    ->route('seller.products')
                    ->with('status', $message)
                    ->with('bulk_warnings', $result['errors']);
            }

            return redirect()
                ->route('seller.products')
                ->with('status', $message);
        }

        // No rows could be imported
        $errorMessages = ! empty($result['errors'])
            ? implode(' ', array_slice($result['errors'], 0, 5))
            : 'Tidak ada produk yang berhasil diimpor. Periksa kembali isi file CSV Anda.';

        return back()
            ->withInput()
            ->with('error', $errorMessages)
            ->with('bulk_errors', $result['errors']);
    }
}
