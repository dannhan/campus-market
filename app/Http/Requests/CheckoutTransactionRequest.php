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

    protected function prepareForValidation(): void
    {
        if (! $this->filled('delivery_method')) {
            $this->merge([
                'delivery_method' => ($this->input('payment_method') === Transaction::PAYMENT_METHOD_COD || $this->filled('meeting_location'))
                    ? Transaction::DELIVERY_METHOD_COD
                    : Transaction::DELIVERY_METHOD_PICKUP,
            ]);
        }

        if (! $this->has('use_escrow')) {
            $this->merge([
                'use_escrow' => in_array($this->input('payment_method'), [
                    Transaction::PAYMENT_METHOD_BANK_TRANSFER,
                    Transaction::PAYMENT_METHOD_EWALLET,
                ], true),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'offer_id' => ['nullable', 'integer', 'exists:offers,id'],
            'payment_method' => ['required', 'string', Rule::in(array_keys(Transaction::PAYMENT_METHODS))],
            'payment_channel' => ['required', 'string', Rule::in(array_keys(Transaction::PAYMENT_CHANNELS))],
            'delivery_method' => ['required', 'string', Rule::in(array_keys(Transaction::DELIVERY_METHODS))],
            'use_escrow' => ['nullable', 'boolean'],
            'meeting_location' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(
                    $this->input('delivery_method') === Transaction::DELIVERY_METHOD_COD
                ),
            ],
            'pickup_location' => ['nullable', 'string', 'max:255'],
            'shipping_recipient_name' => [
                'nullable',
                'string',
                'max:100',
                Rule::requiredIf($this->input('delivery_method') === Transaction::DELIVERY_METHOD_COURIER),
            ],
            'shipping_phone' => [
                'nullable',
                'string',
                'max:30',
                Rule::requiredIf($this->input('delivery_method') === Transaction::DELIVERY_METHOD_COURIER),
            ],
            'shipping_address' => [
                'nullable',
                'string',
                'max:500',
                Rule::requiredIf($this->input('delivery_method') === Transaction::DELIVERY_METHOD_COURIER),
            ],
            'courier_name' => [
                'nullable',
                'string',
                Rule::requiredIf($this->input('delivery_method') === Transaction::DELIVERY_METHOD_COURIER),
                Rule::in(array_keys(Transaction::COURIERS)),
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
            'delivery_method.required' => 'Opsi pengiriman barang wajib dipilih.',
            'delivery_method.in' => 'Opsi pengiriman yang dipilih tidak valid.',
            'meeting_location.required' => 'Untuk opsi COD, lokasi janji temu di kampus wajib diisi.',
            'meeting_location.max' => 'Lokasi janji temu maksimal 255 karakter.',
            'shipping_recipient_name.required' => 'Nama penerima paket wajib diisi untuk pengiriman kurir.',
            'shipping_phone.required' => 'Nomor telepon penerima wajib diisi untuk pengiriman kurir.',
            'shipping_address.required' => 'Alamat lengkap pengiriman wajib diisi untuk pengiriman kurir.',
            'courier_name.required' => 'Pilihan kurir ekspedisi wajib dipilih.',
            'buyer_notes.max' => 'Catatan pesanan maksimal 500 karakter.',
        ];
    }
}
