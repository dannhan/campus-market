<?php

namespace App\Http\Requests\Admin;

use App\Models\Dispute;
use Illuminate\Foundation\Http\FormRequest;

class ResolveDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:under_review,resolved,closed'],
            'admin_decision' => ['nullable', 'string', 'in:'.implode(',', array_keys(Dispute::DECISIONS))],
            'admin_notes' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status proses mediasi wajib dipilih.',
            'status.in' => 'Status mediasi tidak valid.',
            'admin_decision.in' => 'Keputusan mediasi tidak valid.',
            'admin_notes.required' => 'Catatan mediasi dan instruksi penyelesaian wajib diisi.',
            'admin_notes.min' => 'Catatan mediasi minimal 5 karakter.',
            'admin_notes.max' => 'Catatan mediasi maksimal 2000 karakter.',
        ];
    }
}
