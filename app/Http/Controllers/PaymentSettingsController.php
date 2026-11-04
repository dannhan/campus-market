<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePaymentSettingsRequest;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentSettingsController extends Controller
{
    /**
     * Show the student's payment receiving accounts settings form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.payments', [
            'user' => $user,
            'bankChannels' => Transaction::BANK_CHANNELS,
            'ewalletChannels' => Transaction::EWALLET_CHANNELS,
        ]);
    }

    /**
     * Update the student's payment receiving accounts.
     */
    public function update(UpdatePaymentSettingsRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update($request->validated());

        return redirect()->route('profile.payments.edit')->with('status', 'Informasi rekening dan e-wallet penerimaan pembayaran berhasil diperbarui.');
    }
}
