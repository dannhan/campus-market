<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * Store a new rating and review for a completed transaction.
     */
    public function store(StoreReviewRequest $request, Transaction $transaction): RedirectResponse
    {
        $user = $request->user();

        if ($transaction->buyer_id !== $user->id && $transaction->seller_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak untuk memberikan ulasan pada transaksi ini.');
        }

        if ($transaction->status !== Transaction::STATUS_COMPLETED) {
            return back()->with('error', 'Ulasan dan rating hanya dapat diberikan setelah transaksi selesai.');
        }

        if ($transaction->hasReviewedBy($user)) {
            return back()->with('error', 'Anda sudah memberikan ulasan dan rating untuk transaksi ini.');
        }

        $isBuyer = ($transaction->buyer_id === $user->id);
        $revieweeId = $isBuyer ? $transaction->seller_id : $transaction->buyer_id;
        $type = $isBuyer ? Review::TYPE_SELLER_REVIEW : Review::TYPE_BUYER_REVIEW;

        $reviewee = User::findOrFail($revieweeId);

        Review::create([
            'transaction_id' => $transaction->id,
            'reviewer_id' => $user->id,
            'reviewee_id' => $reviewee->id,
            'product_id' => $transaction->product_id,
            'type' => $type,
            'rating' => (int) $request->validated('rating'),
            'comment' => $request->validated('comment'),
            'tags' => $request->validated('tags', []),
        ]);

        // Recalculate target user's aggregated ratings
        $reviewee->recalculateRatings();

        $targetLabel = $isBuyer ? 'penjual' : 'pembeli';

        return back()->with('success', "Terima kasih! Ulasan dan rating untuk {$targetLabel} berhasil dikirim.");
    }

    /**
     * Display public/student ratings and reviews profile for a user.
     */
    public function userReviews(Request $request, User $user): View
    {
        $user->load(['student.university']);

        $tab = $request->query('tab', 'seller');

        $sellerReviews = Review::where('reviewee_id', $user->id)
            ->where('type', Review::TYPE_SELLER_REVIEW)
            ->with(['reviewer.student.university', 'product'])
            ->latest()
            ->paginate(10, ['*'], 'seller_page')
            ->withQueryString();

        $buyerReviews = Review::where('reviewee_id', $user->id)
            ->where('type', Review::TYPE_BUYER_REVIEW)
            ->with(['reviewer.student.university', 'product'])
            ->latest()
            ->paginate(10, ['*'], 'buyer_page')
            ->withQueryString();

        // Calculate distribution for seller reviews
        $sellerRatingsDistribution = [
            5 => Review::where('reviewee_id', $user->id)->where('type', Review::TYPE_SELLER_REVIEW)->where('rating', 5)->count(),
            4 => Review::where('reviewee_id', $user->id)->where('type', Review::TYPE_SELLER_REVIEW)->where('rating', 4)->count(),
            3 => Review::where('reviewee_id', $user->id)->where('type', Review::TYPE_SELLER_REVIEW)->where('rating', 3)->count(),
            2 => Review::where('reviewee_id', $user->id)->where('type', Review::TYPE_SELLER_REVIEW)->where('rating', 2)->count(),
            1 => Review::where('reviewee_id', $user->id)->where('type', Review::TYPE_SELLER_REVIEW)->where('rating', 1)->count(),
        ];

        return view('reviews.user', [
            'user' => $user,
            'tab' => $tab,
            'sellerReviews' => $sellerReviews,
            'buyerReviews' => $buyerReviews,
            'sellerRatingsDistribution' => $sellerRatingsDistribution,
            'sellerTags' => Review::SELLER_TAGS,
            'buyerTags' => Review::BUYER_TAGS,
        ]);
    }
}
