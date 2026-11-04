<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShipTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $transaction = $this->route('transaction');

        return $this->user() !== null
            && $transaction instanceof Transaction
            && $this->user()->id === $transaction->seller_id;
    }

    public function rules(): array
    {
        return [
            'courier_name' => ['required', 'string', Rule::in(array_keys(Transaction::COURIERS))],
            'tracking_number' => ['required', 'string', 'max:100'],
            'seller_notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'courier_name.required' => 'Ekspedisi / kurir wajib dipilih.',
            'courier_name.in' => 'Ekspedisi yang dipilih tidak valid.',
            'tracking_number.required' => 'Nomor resi pengiriman wajib diisi.',
            'tracking_number.max' => 'Nomor resi maksimal 100 karakter.',
            'seller_notes.max' => 'Catatan maksimal 500 karakter.',
        ];
    }
}
