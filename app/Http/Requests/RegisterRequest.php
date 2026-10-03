<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'university_id' => ['required', 'integer', 'exists:universities,id'],
            'student_number' => ['required', 'string', 'max:50'],
            'major' => ['nullable', 'string', 'max:100'],
            'university_email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'university_id.required' => 'Silakan pilih universitas Anda.',
            'university_id.exists' => 'Universitas yang dipilih tidak terdaftar.',
            'student_number.required' => 'Nomor Induk Mahasiswa (NIM) wajib diisi.',
            'university_email.required' => 'Email universitas wajib diisi.',
            'university_email.email' => 'Format email universitas tidak valid.',
            'university_email.unique' => 'Email universitas sudah terdaftar sebagai akun CampusMarket.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'terms.accepted' => 'Anda harus menyetujui syarat & ketentuan untuk mendaftar.',
        ];
    }
}
