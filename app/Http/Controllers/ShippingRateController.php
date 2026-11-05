<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\University;
use App\Services\Shipping\CampusShippingRateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShippingRateController extends Controller
{
    public function __construct(
        protected CampusShippingRateService $shippingRateService
    ) {}

    /**
     * Calculate and return shipping cost estimation between two campus locations.
     */
    public function estimate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'origin_university_id' => ['required', 'integer', 'exists:universities,id'],
            'destination_university_id' => ['required', 'integer', 'exists:universities,id'],
            'courier' => ['nullable', 'string', Rule::in(array_keys(Transaction::COURIERS))],
        ], [
            'origin_university_id.required' => 'Kampus asal pengiriman wajib dipilih.',
            'origin_university_id.exists' => 'Kampus asal pengiriman tidak valid.',
            'destination_university_id.required' => 'Kampus tujuan pengiriman wajib dipilih.',
            'destination_university_id.exists' => 'Kampus tujuan pengiriman tidak valid.',
            'courier.in' => 'Pilihan kurir ekspedisi tidak dikenali.',
        ]);

        $origin = University::findOrFail($validated['origin_university_id']);
        $destination = University::findOrFail($validated['destination_university_id']);
        $courier = $validated['courier'] ?? 'jnt';

        $estimate = $this->shippingRateService->calculateRate($origin, $destination, $courier);

        return response()->json([
            'success' => true,
            'data' => $estimate,
        ]);
    }
}
