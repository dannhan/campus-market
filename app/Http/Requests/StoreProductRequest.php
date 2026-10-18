<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'price' => ['required', 'numeric', 'min:1000', 'max:999999999999'],
            'condition' => ['required', Rule::in(Product::CONDITIONS)],
            'transaction_type' => ['required', Rule::in(Product::TRANSACTION_TYPES)],
            'location' => ['nullable', 'string', 'max:255'],
            'bargainable' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul produk wajib diisi.',
            'title.max' => 'Judul produk maksimal 255 karakter.',
            'description.required' => 'Deskripsi produk wajib diisi.',
            'category_id.required' => 'Kategori produk wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'price.required' => 'Harga produk wajib diisi.',
            'price.min' => 'Harga produk minimal Rp 1.000.',
            'condition.required' => 'Kondisi barang wajib dipilih.',
            'condition.in' => 'Kondisi barang tidak valid.',
            'transaction_type.required' => 'Jenis transaksi wajib dipilih.',
            'transaction_type.in' => 'Jenis transaksi tidak valid.',
            'images.max' => 'Maksimal 5 foto produk.',
            'images.*.image' => 'Setiap lampiran harus berupa gambar.',
            'images.*.mimes' => 'Format foto yang didukung: JPG, PNG, WEBP.',
            'images.*.max' => 'Ukuran setiap foto maksimal 5MB.',
        ];
    }
}
