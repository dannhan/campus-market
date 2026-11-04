<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'bank_name' => ['nullable', 'string', Rule::in(array_keys(Transaction::BANK_CHANNELS))],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_name' => ['nullable', 'string', 'max:100'],
            'ewallet_provider' => ['nullable', 'string', Rule::in(array_keys(Transaction::EWALLET_CHANNELS))],
            'ewallet_account_number' => ['nullable', 'string', 'max:50'],
            'ewallet_account_name' => ['nullable', 'string', 'max:100'],
            'preferred_meeting_location' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'bank_name.in' => 'Bank yang dipilih tidak valid.',
            'bank_account_number.max' => 'Nomor rekening maksimal 50 karakter.',
            'bank_account_name.max' => 'Nama pemilik rekening maksimal 100 karakter.',
            'ewallet_provider.in' => 'E-Wallet yang dipilih tidak valid.',
            'ewallet_account_number.max' => 'Nomor e-wallet maksimal 50 karakter.',
            'ewallet_account_name.max' => 'Nama akun e-wallet maksimal 100 karakter.',
            'preferred_meeting_location.max' => 'Lokasi COD favorit maksimal 255 karakter.',
        ];
    }
}
