<?php

namespace App\Services\Bargaining;

use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Domain service for the buyer-seller price bargaining flow (SRS-010).
 *
 * A negotiation is a thread of offers: the buyer opens with a root offer,
 * either party may counter (a child offer), and only the seller can accept.
 * Accepting an offer closes the negotiation and marks the product sold.
 */
class OfferService
{
    /**
     * Create a root offer from a buyer on a product.
     *
     * @throws InvalidArgumentException when the offer violates the bargaining rules.
     */
    public function makeOffer(Product $product, User $buyer, float $offeredPrice, ?string $message = null): Offer
    {
        if ($product->user_id === $buyer->id) {
            throw new InvalidArgumentException('Anda tidak dapat menawar produk milik Anda sendiri.');
        }

        if ($product->status !== 'active') {
            throw new InvalidArgumentException('Produk ini tidak lagi menerima penawaran.');
        }

        if (! $product->bargainable) {
            throw new InvalidArgumentException('Penjual tidak menerima tawaran harga untuk produk ini.');
        }

        $hasPendingOffer = Offer::where('product_id', $product->id)
            ->where('user_id', $buyer->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPendingOffer) {
            throw new InvalidArgumentException('Anda masih memiliki penawaran yang menunggu respons penjual di produk ini.');
        }

        return Offer::create([
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'parent_offer_id' => null,
            'offered_price' => $offeredPrice,
            'message' => $message,
            'status' => 'pending',
        ]);
    }

    /**
     * Respond to a pending offer: accept, reject, or counter.
     *
     * @return Offer|null the created counter-offer, or null when accept/reject.
     *
     * @throws InvalidArgumentException on unauthorized or invalid transitions.
     */
    public function respond(Offer $offer, User $responder, string $action, ?float $counterPrice = null, ?string $message = null): ?Offer
    {
        if (! $offer->isPending()) {
            throw new InvalidArgumentException('Penawaran ini sudah tidak dapat direspons.');
        }

        if (! $this->isParticipant($offer, $responder)) {
            throw new InvalidArgumentException('Anda tidak berhak merespons penawaran ini.');
        }

        if ($action === 'accept') {
            $this->accept($offer, $responder);

            return null;
        }

        if ($action === 'reject') {
            if ($offer->product->user_id !== $responder->id) {
                throw new InvalidArgumentException('Hanya penjual yang dapat menolak penawaran. Gunakan batalkan untuk menarik tawaran Anda.');
            }

            $offer->update(['status' => 'rejected']);

            return null;
        }

        // Counter-offer: either participant proposes a new price, threading off this offer.
        if ($counterPrice === null) {
            throw new InvalidArgumentException('Harga tawaran balasan wajib diisi.');
        }

        if ($offer->product->status !== 'active') {
            throw new InvalidArgumentException('Produk ini sudah tidak terbuka untuk negosiasi.');
        }

        return DB::transaction(function () use ($offer, $responder, $counterPrice, $message) {
            $offer->update(['status' => 'countered']);

            return Offer::create([
                'product_id' => $offer->product_id,
                'user_id' => $responder->id,
                'parent_offer_id' => $offer->id,
                'offered_price' => $counterPrice,
                'message' => $message,
                'status' => 'pending',
            ]);
        });
    }

    /**
     * Withdraw a pending offer as its maker.
     *
     * @throws InvalidArgumentException when withdrawal is not allowed.
     */
    public function withdraw(Offer $offer, User $user): void
    {
        if ($offer->user_id !== $user->id) {
            throw new InvalidArgumentException('Anda hanya dapat membatalkan penawaran milik Anda sendiri.');
        }

        if (! $offer->isPending()) {
            throw new InvalidArgumentException('Penawaran ini sudah diproses dan tidak dapat dibatalkan.');
        }

        $offer->update(['status' => 'withdrawn']);
    }

    /**
     * Seller accepts the offer: product becomes sold, competing offers are rejected.
     */
    protected function accept(Offer $offer, User $responder): void
    {
        if ($offer->product->user_id !== $responder->id) {
            throw new InvalidArgumentException('Hanya penjual yang dapat menerima penawaran.');
        }

        DB::transaction(function () use ($offer) {
            $offer->update(['status' => 'accepted']);

            $offer->product->update(['status' => 'sold']);

            Offer::where('product_id', $offer->product_id)
                ->where('id', '!=', $offer->id)
                ->where('status', 'pending')
                ->update(['status' => 'rejected']);
        });
    }

    /**
     * Whether the user may act on this offer: the seller or the thread counterparties.
     */
    protected function isParticipant(Offer $offer, User $user): bool
    {
        if ($offer->product->user_id === $user->id) {
            return true;
        }

        // The offerer and every previous participant in the counter-offer thread.
        $threadUserIds = [$offer->user_id];

        $parent = $offer->parentOffer;
        while ($parent !== null) {
            $threadUserIds[] = $parent->user_id;
            $parent = $parent->parentOffer;
        }

        return in_array($user->id, $threadUserIds, true);
    }
}
