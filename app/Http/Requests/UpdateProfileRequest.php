<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama tampilan wajib diisi.',
            'avatar.image' => 'File avatar harus berupa gambar.',
            'avatar.mimes' => 'Format avatar yang didukung: JPG, PNG, WEBP.',
            'avatar.max' => 'Ukuran avatar maksimal 2MB.',
        ];
    }
}
