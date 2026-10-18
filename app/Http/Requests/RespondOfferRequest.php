<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RespondOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['accept', 'reject', 'counter'])],
            'offered_price' => ['required_if:action,counter', 'nullable', 'numeric', 'min:1000', 'max:999999999999'],
            'message' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'Aksi respons penawaran wajib dipilih.',
            'action.in' => 'Aksi respons penawaran tidak valid.',
            'offered_price.required_if' => 'Harga tawaran balasan wajib diisi.',
            'offered_price.min' => 'Harga tawaran balasan minimal Rp 1.000.',
            'message.max' => 'Pesan maksimal 500 karakter.',
        ];
    }
}
