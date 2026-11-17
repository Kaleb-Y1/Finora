<?php

namespace App\Http\Requests\Ledger;

use Illuminate\Foundation\Http\FormRequest;

class AddMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manageMembers', $this->route('book'));
    }

    public function rules(): array
    {
        return [
            'identifier' => ['required', 'string'],
            'role' => ['required', 'in:admin,member'],
        ];
    }

    public function messages(): array
    {
        return [
            'identifier.required' => 'Username atau email anggota wajib diisi.',
            'role.required' => 'Peran anggota wajib dipilih.',
            'role.in' => 'Peran yang dipilih tidak valid (hanya Admin Buku atau Anggota).',
        ];
    }
}
