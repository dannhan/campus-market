<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'offered_price' => ['required', 'numeric', 'min:1000', 'max:999999999999'],
            'message' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'offered_price.required' => 'Harga penawaran wajib diisi.',
            'offered_price.min' => 'Harga penawaran minimal Rp 1.000.',
            'message.max' => 'Pesan penawaran maksimal 500 karakter.',
        ];
    }
}
