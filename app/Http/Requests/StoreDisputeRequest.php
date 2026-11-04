<?php

namespace App\Http\Requests;

use App\Models\Dispute;
use Illuminate\Foundation\Http\FormRequest;

class StoreDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'offer_id' => ['nullable', 'exists:offers,id'],
            'title' => ['required', 'string', 'max:150'],
            'reason' => ['required', 'string', 'in:'.implode(',', array_keys(Dispute::REASONS))],
            'solution_requested' => ['required', 'string', 'in:'.implode(',', array_keys(Dispute::SOLUTIONS))],
            'description' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Produk terkait transaksi wajib dipilih.',
            'product_id.exists' => 'Produk yang dipilih tidak ditemukan.',
            'title.required' => 'Judul kendala wajib diisi.',
            'title.max' => 'Judul kendala maksimal 150 karakter.',
            'reason.required' => 'Penyebab masalah wajib dipilih.',
            'reason.in' => 'Penyebab masalah yang dipilih tidak valid.',
            'solution_requested.required' => 'Tuntutan solusi yang diharapkan wajib dipilih.',
            'solution_requested.in' => 'Solusi yang dipilih tidak valid.',
            'description.required' => 'Jelaskan kronologi kendala yang Anda alami.',
            'description.min' => 'Penjelasan kendala minimal 10 karakter.',
            'description.max' => 'Penjelasan kendala maksimal 2000 karakter.',
        ];
    }
}
