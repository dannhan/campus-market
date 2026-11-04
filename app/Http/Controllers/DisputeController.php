<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDisputeRequest;
use App\Models\Dispute;
use App\Models\Offer;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DisputeController extends Controller
{
    /**
     * Display a listing of disputes involving the authenticated student.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $disputes = Dispute::query()
            ->with(['product', 'reporter', 'respondent', 'admin'])
            ->where(function ($q) use ($user) {
                $q->where('reporter_id', $user->id)
                    ->orWhere('respondent_id', $user->id);
            })
            ->latest()
            ->paginate(10);

        return view('disputes.index', compact('disputes'));
    }

    /**
     * Show the form for creating a new dispute / reporting an issue.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $offerId = $request->query('offer_id');
        $productId = $request->query('product_id');

        $offer = null;
        $product = null;

        if ($offerId) {
            $offer = Offer::with(['product.user'])->findOrFail($offerId);
            $product = $offer->product;

            // Check if user is either buyer or seller in this offer
            if ($offer->user_id !== $user->id && $product->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki hak untuk mengajukan komplain pada transaksi ini.');
            }
        } elseif ($productId) {
            $product = Product::with('user')->findOrFail($productId);
        }

        return view('disputes.create', [
            'offer' => $offer,
            'product' => $product,
            'reasons' => Dispute::REASONS,
            'solutions' => Dispute::SOLUTIONS,
        ]);
    }

    /**
     * Store a newly created dispute report in storage.
     */
    public function store(StoreDisputeRequest $request): RedirectResponse
    {
        $user = $request->user();
        $product = Product::findOrFail($request->validated('product_id'));
        $offer = null;

        if ($request->filled('offer_id')) {
            $offer = Offer::findOrFail($request->validated('offer_id'));

            // Check if user is either buyer or seller in this offer
            if ($offer->user_id !== $user->id && $product->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki hak untuk mengajukan komplain pada transaksi ini.');
            }
        } elseif ($product->user_id !== $user->id) {
            // If reporting without specific offer_id, check if user has any offer on this product
            $userOffer = Offer::where('product_id', $product->id)->where('user_id', $user->id)->latest()->first();
            if (! $userOffer) {
                abort(403, 'Anda tidak memiliki riwayat transaksi atau penawaran pada produk ini.');
            }
            $offer = $userOffer;
        }

        // Determine respondent
        if ($user->id === $product->user_id) {
            // Seller is reporting buyer
            if ($offer) {
                $respondentId = $offer->user_id;
            } else {
                return back()->withInput()->withErrors(['product_id' => 'Pelaporan oleh penjual memerlukan data tawaran transaksi pembeli terkait.']);
            }
        } else {
            // Buyer is reporting seller
            $respondentId = $product->user_id;
        }

        $dispute = Dispute::create([
            'product_id' => $product->id,
            'offer_id' => $offer?->id,
            'reporter_id' => $user->id,
            'respondent_id' => $respondentId,
            'title' => $request->validated('title'),
            'reason' => $request->validated('reason'),
            'description' => $request->validated('description'),
            'solution_requested' => $request->validated('solution_requested'),
            'status' => Dispute::STATUS_OPEN,
        ]);

        return redirect()
            ->route('disputes.show', $dispute)
            ->with('status', 'Laporan kendala transaksi berhasil diajukan dan sedang menunggu mediasi admin.');
    }

    /**
     * Display the specified dispute details and mediation status.
     */
    public function show(Dispute $dispute, Request $request): View
    {
        $user = $request->user();

        // Ensure user is involved in the dispute or is an admin
        if ($dispute->reporter_id !== $user->id && $dispute->respondent_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Anda tidak memiliki akses ke berkas mediasi ini.');
        }

        $dispute->load(['product.user.student.university', 'offer', 'reporter.student.university', 'respondent.student.university', 'admin']);

        return view('disputes.show', compact('dispute'));
    }
}
