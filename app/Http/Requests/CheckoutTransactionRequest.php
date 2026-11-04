<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'offer_id' => ['nullable', 'integer', 'exists:offers,id'],
            'payment_method' => ['required', 'string', Rule::in(array_keys(Transaction::PAYMENT_METHODS))],
            'payment_channel' => ['required', 'string', Rule::in(array_keys(Transaction::PAYMENT_CHANNELS))],
            'meeting_location' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf($this->input('payment_method') === Transaction::PAYMENT_METHOD_COD),
            ],
            'buyer_notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Produk yang ingin dibeli wajib dipilih.',
            'product_id.exists' => 'Produk tidak ditemukan.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran yang dipilih tidak valid.',
            'payment_channel.required' => 'Pilihan saluran pembayaran wajib dipilih.',
            'payment_channel.in' => 'Saluran pembayaran tidak valid.',
            'meeting_location.required' => 'Untuk metode COD, lokasi janji temu di kampus wajib diisi.',
            'meeting_location.max' => 'Lokasi janji temu maksimal 255 karakter.',
            'buyer_notes.max' => 'Catatan pesanan maksimal 500 karakter.',
        ];
    }
}
