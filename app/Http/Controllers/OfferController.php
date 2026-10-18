<?php

namespace App\Http\Controllers;

use App\Http\Requests\RespondOfferRequest;
use App\Http\Requests\StoreOfferRequest;
use App\Models\Offer;
use App\Models\Product;
use App\Services\Bargaining\OfferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfferController extends Controller
{
    public function __construct(protected OfferService $offers)
    {
    }

    /**
     * List the user's bargaining inbox: offers received on own products and offers sent.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $received = Offer::query()
            ->whereHas('product', fn ($q) => $q->where('user_id', $user->id))
            ->where('user_id', '!=', $user->id)
            ->with(['product', 'user.student.university'])
            ->latest()
            ->paginate(10, ['*'], 'received_page')
            ->withQueryString();

        $sent = Offer::query()
            ->where('user_id', $user->id)
            ->with(['product.user.student.university'])
            ->latest()
            ->paginate(10, ['*'], 'sent_page')
            ->withQueryString();

        return view('offers.index', [
            'receivedOffers' => $received,
            'sentOffers' => $sent,
        ]);
    }

    /**
     * A buyer opens a negotiation on a product (SRS-010).
     */
    public function store(StoreOfferRequest $request, Product $product): RedirectResponse
    {
        try {
            $this->offers->makeOffer(
                $product,
                $request->user(),
                (float) $request->validated('offered_price'),
                $request->validated('message'),
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Penawaran harga berhasil dikirim ke penjual.');
    }

    /**
     * Seller or counterparty accepts, rejects, or counters a pending offer.
     */
    public function respond(RespondOfferRequest $request, Offer $offer): RedirectResponse
    {
        try {
            $counterOffer = $this->offers->respond(
                $offer,
                $request->user(),
                $request->validated('action'),
                $request->validated('action') === 'counter'
                    ? (float) $request->validated('offered_price')
                    : null,
                $request->validated('message'),
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $statusMessage = match ($request->validated('action')) {
            'accept' => 'Penawaran diterima. Produk ditandai sebagai terjual.',
            'reject' => 'Penawaran ditolak.',
            default => 'Tawaran balasan berhasil dikirim.',
        };

        if ($counterOffer) {
            return redirect()->route('offers.index')->with('status', $statusMessage);
        }

        return redirect()
            ->route('products.show', $offer->product)
            ->with('status', $statusMessage);
    }

    /**
     * The offerer withdraws their own pending offer.
     */
    public function cancel(Request $request, Offer $offer): RedirectResponse
    {
        try {
            $this->offers->withdraw($offer, $request->user());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Penawaran berhasil dibatalkan.');
    }
}
