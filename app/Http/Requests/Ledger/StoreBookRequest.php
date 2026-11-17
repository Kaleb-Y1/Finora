<?php

namespace App\Http\Requests\Ledger;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'opening_balance_date' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama buku keuangan wajib diisi.',
            'name.max' => 'Nama buku keuangan maksimal 150 karakter.',
            'opening_balance.required' => 'Saldo awal wajib diisi (minimal 0).',
            'opening_balance.numeric' => 'Saldo awal harus berupa angka valid.',
            'opening_balance.min' => 'Saldo awal tidak boleh bernilai negatif.',
            'opening_balance_date.required' => 'Tanggal saldo awal wajib ditentukan.',
            'opening_balance_date.date' => 'Format tanggal saldo awal tidak valid.',
        ];
    }
}
