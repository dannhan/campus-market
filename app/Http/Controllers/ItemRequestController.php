<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ItemRequest;
use App\Models\ItemRequestOffer;
use App\Models\Product;
use App\Models\University;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemRequestController extends Controller
{
    /**
     * Display a listing of wanted item requests.
     */
    public function index(Request $request): View
    {
        $query = ItemRequest::with(['user.student.university', 'category', 'university'])
            ->withCount('offers')
            ->latest();

        // 1. Search Query
        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 2. Category Filter
        if ($categorySlug = $request->query('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        // 3. Urgency Filter
        if ($urgency = $request->query('urgency')) {
            if (in_array($urgency, ['urgent', 'normal', 'low'], true)) {
                $query->where('urgency', $urgency);
            }
        }

        // 4. Status Filter (default: open)
        $statusFilter = $request->query('status', 'open');
        if ($statusFilter === 'open') {
            $query->where('status', ItemRequest::STATUS_OPEN);
        } elseif ($statusFilter === 'fulfilled') {
            $query->where('status', ItemRequest::STATUS_FULFILLED);
        } elseif ($statusFilter === 'closed') {
            $query->where('status', ItemRequest::STATUS_CLOSED);
        }

        // 5. University / Campus Scope
        if ($uniId = $request->query('university_id')) {
            $query->where('university_id', $uniId);
        }

        // 6. Scope (my requests)
        $scope = $request->query('scope', 'all');
        if ($scope === 'my' && $request->user()) {
            $query->where('user_id', $request->user()->id);
        }

        $requests = $query->paginate(12)->withQueryString();

        $categories = Category::orderBy('sort_order')->get();
        $universities = University::orderBy('name')->get();

        $openCount = ItemRequest::where('status', ItemRequest::STATUS_OPEN)->count();
        $fulfilledCount = ItemRequest::where('status', ItemRequest::STATUS_FULFILLED)->count();
        $myRequestsCount = $request->user() ? ItemRequest::where('user_id', $request->user()->id)->count() : 0;

        return view('requests.index', [
            'requests' => $requests,
            'categories' => $categories,
            'universities' => $universities,
            'statusFilter' => $statusFilter,
            'scope' => $scope,
            'openCount' => $openCount,
            'fulfilledCount' => $fulfilledCount,
            'myRequestsCount' => $myRequestsCount,
            'currentCategory' => $categorySlug,
            'currentUrgency' => $request->query('urgency'),
            'searchQuery' => $request->query('q'),
        ]);
    }

    /**
     * Show the form for creating a new item request.
     */
    public function create(Request $request): View
    {
        $categories = Category::orderBy('sort_order')->get();
        $userUniversity = $request->user()->student?->university ?? University::first();

        return view('requests.create', [
            'categories' => $categories,
            'userUniversity' => $userUniversity,
        ]);
    }

    /**
     * Store a newly created item request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['required', 'string', 'max:3000'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0'],
            'condition_preference' => ['required', 'in:any,new,like_new,used'],
            'urgency' => ['required', 'in:urgent,normal,low'],
        ], [
            'title.required' => 'Nama barang yang dicari wajib diisi.',
            'title.max' => 'Nama barang maksimal 255 karakter.',
            'category_id.required' => 'Pilih kategori barang yang sesuai.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'description.required' => 'Jelaskan rincian atau spesifikasi barang yang Anda butuhkan.',
            'condition_preference.required' => 'Pilih preferensi kondisi barang.',
            'urgency.required' => 'Pilih tingkat urgensi pencarian barang.',
        ]);

        $universityId = $request->user()->student?->university_id ?? University::first()?->id;

        $itemRequest = ItemRequest::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'university_id' => $universityId,
            'status' => ItemRequest::STATUS_OPEN,
            'slug' => ItemRequest::generateUniqueSlug($validated['title']),
        ]);

        return redirect()
            ->route('requests.show', $itemRequest)
            ->with('status', 'Permintaan barang berhasil dipasang di Papan Cari Barang.');
    }

    /**
     * Display the specified item request.
     */
    public function show(Request $request, ItemRequest $itemRequest): View
    {
        $itemRequest->increment('views_count');

        $itemRequest->load([
            'user.student.university',
            'category',
            'university',
            'offers.user.student.university',
            'offers.product',
        ]);

        $isOwner = $request->user()?->id === $itemRequest->user_id;

        // If authenticated non-owner, retrieve their active products to offer
        $userActiveProducts = collect();
        if ($request->user() && ! $isOwner) {
            $userActiveProducts = Product::where('user_id', $request->user()->id)
                ->where('status', 'active')
                ->latest()
                ->get();
        }

        // Check if current user has already submitted an offer
        $userExistingOffer = null;
        if ($request->user() && ! $isOwner) {
            $userExistingOffer = $itemRequest->offers->firstWhere('user_id', $request->user()->id);
        }

        return view('requests.show', [
            'itemRequest' => $itemRequest,
            'isOwner' => $isOwner,
            'userActiveProducts' => $userActiveProducts,
            'userExistingOffer' => $userExistingOffer,
        ]);
    }

    /**
     * Show the form for editing the request.
     */
    public function edit(Request $request, ItemRequest $itemRequest): View
    {
        if ($request->user()->id !== $itemRequest->user_id) {
            abort(403, 'Anda tidak berhak mengedit permintaan ini.');
        }

        $categories = Category::orderBy('sort_order')->get();

        return view('requests.edit', [
            'itemRequest' => $itemRequest,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the item request.
     */
    public function update(Request $request, ItemRequest $itemRequest): RedirectResponse
    {
        if ($request->user()->id !== $itemRequest->user_id) {
            abort(403, 'Anda tidak berhak mengubah permintaan ini.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['required', 'string', 'max:3000'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0'],
            'condition_preference' => ['required', 'in:any,new,like_new,used'],
            'urgency' => ['required', 'in:urgent,normal,low'],
        ]);

        $itemRequest->update($validated);

        return redirect()
            ->route('requests.show', $itemRequest)
            ->with('status', 'Permintaan barang berhasil diperbarui.');
    }

    /**
     * Delete the item request.
     */
    public function destroy(Request $request, ItemRequest $itemRequest): RedirectResponse
    {
        if ($request->user()->id !== $itemRequest->user_id) {
            abort(403, 'Anda tidak berhak menghapus permintaan ini.');
        }

        $itemRequest->delete();

        return redirect()
            ->route('requests.index')
            ->with('status', 'Permintaan barang berhasil dihapus.');
    }

    /**
     * Close or mark the request as fulfilled.
     */
    public function close(Request $request, ItemRequest $itemRequest): RedirectResponse
    {
        if ($request->user()->id !== $itemRequest->user_id) {
            abort(403, 'Anda tidak berhak mengubah status permintaan ini.');
        }

        $newStatus = $request->input('status', ItemRequest::STATUS_FULFILLED);
        if (! in_array($newStatus, [ItemRequest::STATUS_FULFILLED, ItemRequest::STATUS_CLOSED], true)) {
            $newStatus = ItemRequest::STATUS_FULFILLED;
        }

        $itemRequest->update([
            'status' => $newStatus,
            'fulfilled_at' => now(),
        ]);

        $statusText = $newStatus === ItemRequest::STATUS_FULFILLED
            ? 'Selamat! Permintaan barang ditandai sebagai Sudah Ditemukan.'
            : 'Permintaan barang telah ditutup.';

        return back()->with('status', $statusText);
    }

    /**
     * Reopen a closed or fulfilled request.
     */
    public function reopen(Request $request, ItemRequest $itemRequest): RedirectResponse
    {
        if ($request->user()->id !== $itemRequest->user_id) {
            abort(403, 'Anda tidak berhak membuka kembali permintaan ini.');
        }

        $itemRequest->update([
            'status' => ItemRequest::STATUS_OPEN,
            'fulfilled_at' => null,
        ]);

        return back()->with('status', 'Permintaan barang dibuka kembali untuk penawaran.');
    }

    /**
     * Submit an offer for an item request.
     */
    public function storeOffer(Request $request, ItemRequest $itemRequest): RedirectResponse
    {
        if ($request->user()->id === $itemRequest->user_id) {
            return back()->with('error', 'Anda tidak dapat menawarkan barang pada permintaan milik sendiri.');
        }

        if (! $itemRequest->isOpen()) {
            return back()->with('error', 'Permintaan ini sudah selesai atau ditutup sehingga tidak menerima penawaran lagi.');
        }

        $validated = $request->validate([
            'price' => ['required', 'numeric', 'min:1000', 'max:999999999999'],
            'message' => ['required', 'string', 'max:1000'],
            'product_id' => ['nullable', 'exists:products,id'],
        ], [
            'price.required' => 'Tentukan harga penawaran barang Anda.',
            'price.min' => 'Harga penawaran minimal Rp 1.000.',
            'message.required' => 'Tuliskan pesan penjelasan kondisi barang kepada pembuat permintaan.',
        ]);

        $itemRequest->offers()->create([
            'user_id' => $request->user()->id,
            'product_id' => $validated['product_id'] ?? null,
            'price' => $validated['price'],
            'message' => $validated['message'],
            'status' => ItemRequestOffer::STATUS_PENDING,
        ]);

        return back()->with('status', 'Penawaran barang berhasil dikirim ke pembuat permintaan!');
    }

    /**
     * Accept an offer on the request.
     */
    public function acceptOffer(Request $request, ItemRequest $itemRequest, ItemRequestOffer $offer): RedirectResponse
    {
        if ($request->user()->id !== $itemRequest->user_id) {
            abort(403, 'Anda tidak berhak menerima penawaran untuk permintaan ini.');
        }

        if ($offer->item_request_id !== $itemRequest->id) {
            abort(404, 'Penawaran tidak cocok dengan permintaan.');
        }

        $offer->update([
            'status' => ItemRequestOffer::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);

        $itemRequest->update([
            'status' => ItemRequest::STATUS_FULFILLED,
            'fulfilled_at' => now(),
        ]);

        return back()->with('status', 'Penawaran diterima! Status permintaan telah diubah menjadi Sudah Ditemukan.');
    }
}
