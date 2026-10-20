<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\University;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Browse the marketplace with search and filters (SRS-005, SRS-006).
     *
     * Requests with `fragment=1` receive the next page of cards as JSON so the
     * product grid can append them without a full page reload.
     */
    public function index(Request $request): View|JsonResponse
    {
        $filters = $request->only(['q', 'category', 'university', 'condition', 'type', 'min_price', 'max_price', 'sort']);

        $products = Product::query()
            ->active()
            ->search($filters['q'] ?? null)
            ->when($filters['category'] ?? null, function ($query, $slug) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $slug));
            })
            ->when(
                $filters['university'] ?? null,
                fn ($query) => $query->forUniversity(
                    University::where('slug', $filters['university'])->value('id')
                )
            )
            ->when($filters['condition'] ?? null, function ($query, $condition) {
                if (in_array($condition, Product::CONDITIONS, true)) {
                    $query->where('condition', $condition);
                }
            })
            ->when($filters['type'] ?? null, function ($query, $type) {
                if (in_array($type, Product::TRANSACTION_TYPES, true)) {
                    $query->where('transaction_type', $type);
                }
            })
            ->priceBetween($filters['min_price'] ?? null, $filters['max_price'] ?? null)
            ->with(['category', 'user.student.university', 'images'])
            ->when($filters['sort'] ?? null, function ($query, $sort) {
                match ($sort) {
                    'price_asc' => $query->orderBy('price'),
                    'price_desc' => $query->orderByDesc('price'),
                    default => $query->latest(),
                };
            }, fn ($query) => $query->latest())
            ->paginate(12)
            ->withQueryString();

        if ($request->boolean('fragment')) {
            return response()->json([
                'html' => view('products._cards', ['products' => $products])->render(),
                'next_page_url' => $products->nextPageUrl(),
                'has_more' => $products->hasMorePages(),
                'remaining' => max($products->total() - ($products->lastItem() ?? 0), 0),
                'count' => $products->count(),
            ]);
        }

        return view('products.index', [
            'products' => $products,
            'categories' => Category::orderBy('sort_order')->get(),
            'universities' => University::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    /**
     * Display the product detail page (SRS-007).
     */
    public function show(Request $request, Product $product): View
    {
        $isOwner = $request->user()?->id === $product->user_id;

        if ($product->status !== 'active' && ! $isOwner) {
            abort(404);
        }

        $product->increment('views_count');

        $product->load(['category', 'user.student.university', 'images']);

        $myPendingOffer = null;
        if ($request->user()) {
            $myPendingOffer = $product->offers()
                ->where('user_id', $request->user()->id)
                ->where('status', 'pending')
                ->latest()
                ->first();
        }

        return view('products.show', [
            'product' => $product,
            'seller' => $product->user,
            'isOwner' => $isOwner,
            'myPendingOffer' => $myPendingOffer,
            'pendingOffersCount' => $product->offers()->where('status', 'pending')->count(),
        ]);
    }

    /**
     * Show the product creation form (SRS-008).
     */
    public function create(Request $request): View
    {
        return view('products.create', [
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Store a new product listing with uploaded photos (SRS-008).
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $product = DB::transaction(function () use ($request, $validated) {
            $product = Product::create([
                ...collect($validated)->except(['images'])->all(),
                'user_id' => $request->user()->id,
                'slug' => Product::generateUniqueSlug($validated['title']),
                'status' => 'active',
                'bargainable' => $request->boolean('bargainable', true),
            ]);

            $this->storeImages($product, $request->file('images', []));

            return $product;
        });

        return redirect()
            ->route('products.show', $product)
            ->with('status', 'Produk berhasil dipublikasikan ke etalase.');
    }

    /**
     * Show the edit form for the seller's own product.
     */
    public function edit(Request $request, Product $product): View
    {
        Gate::authorize('update', $product);

        $product->load('images');

        return view('products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Update the seller's own product; replacing photos when new ones are uploaded.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);

        $validated = $request->validated();

        DB::transaction(function () use ($request, $product, $validated) {
            $product->update([
                ...collect($validated)->except(['images'])->all(),
                'bargainable' => $request->boolean('bargainable', true),
            ]);

            if ($request->hasFile('images')) {
                $product->images->each(function ($image) {
                    $image->deleteFile();
                    $image->delete();
                });

                $this->storeImages($product, $request->file('images', []));
            }
        });

        return redirect()
            ->route('products.show', $product)
            ->with('status', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the seller's own product together with its stored photos.
     */
    public function destroy(Request $request, Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);

        DB::transaction(function () use ($product) {
            $product->images->each(function ($image) {
                $image->deleteFile();
                $image->delete();
            });

            $product->delete();
        });

        return redirect()
            ->route('products.index')
            ->with('status', 'Produk berhasil dihapus dari etalase.');
    }

    /**
     * Persist uploaded photos as product images; first upload becomes the cover.
     */
    protected function storeImages(Product $product, array $files): void
    {
        foreach (array_values($files) as $index => $file) {
            if (! $file->isValid()) {
                continue;
            }

            $product->images()->create([
                'path' => $file->store('products', 'public'),
                'is_primary' => $index === 0,
                'sort_order' => $index,
            ]);
        }
    }
}
