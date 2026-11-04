<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadPaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'payment_proof' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'buyer_notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_proof.required' => 'Foto bukti pembayaran / struk transfer wajib diunggah.',
            'payment_proof.image' => 'File bukti pembayaran harus berupa gambar.',
            'payment_proof.mimes' => 'Format gambar yang diperbolehkan: JPG, PNG, WEBP.',
            'payment_proof.max' => 'Ukuran foto maksimal 5MB.',
            'buyer_notes.max' => 'Catatan maksimal 500 karakter.',
        ];
    }
}
