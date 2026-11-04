<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Standard category icons supported by CampusMarket UI.
     *
     * @var array<string, string>
     */
    public const AVAILABLE_ICONS = [
        'book-open' => 'Buku & Catatan Kuliah',
        'beaker' => 'Perlengkapan Lab & Praktikum',
        'device-phone' => 'Gadget & Handphone',
        'briefcase' => 'Jasa Mahasiswa',
        'pencil' => 'Alat Tulis & Menggambar',
        'shirt' => 'Pakaian & Aksesoris',
        'cpu' => 'Elektronik & Perangkat Komputer',
        'cake' => 'Makanan & Minuman',
    ];

    /**
     * Display a listing of product categories with their product count.
     */
    public function index(): View
    {
        $categories = Category::query()
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new product category.
     */
    public function create(): View
    {
        $nextSortOrder = (Category::max('sort_order') ?? 0) + 1;

        return view('admin.categories.create', [
            'icons' => self::AVAILABLE_ICONS,
            'nextSortOrder' => $nextSortOrder,
        ]);
    }

    /**
     * Store a newly created product category in storage.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if (! isset($validated['sort_order']) || $validated['sort_order'] === null) {
            $validated['sort_order'] = (Category::max('sort_order') ?? 0) + 1;
        }

        $category = Category::create($validated);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', "Kategori '{$category->name}' berhasil ditambahkan.");
    }

    /**
     * Show the form for editing the specified product category.
     */
    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
            'icons' => self::AVAILABLE_ICONS,
        ]);
    }

    /**
     * Update the specified product category in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $validated = $request->validated();

        $category->update($validated);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', "Kategori '{$category->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified category from storage if no products exist.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $productCount = $category->products()->count();

        if ($productCount > 0) {
            return back()->with(
                'error',
                "Kategori '{$category->name}' tidak dapat dihapus karena masih digunakan oleh {$productCount} produk."
            );
        }

        $categoryName = $category->name;
        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('status', "Kategori '{$categoryName}' berhasil dihapus.");
    }
}
