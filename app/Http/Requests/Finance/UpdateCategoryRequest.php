<?php

namespace App\Http\Requests\Finance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller via Policy
    }

    public function rules(): array
    {
        $bookId   = session('active_book_id');
        $category = $this->route('category');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories')->where(function ($query) use ($bookId) {
                    return $query->where('book_id', $bookId)
                                 ->where('type', $this->input('type'));
                })->ignore($category),
            ],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'Nama kategori wajib diisi.',
            'name.max'       => 'Nama kategori maksimal 100 karakter.',
            'name.unique'    => 'Nama kategori dengan tipe yang sama sudah ada di buku ini.',
            'type.required'  => 'Tipe kategori wajib dipilih.',
            'type.in'        => 'Tipe kategori harus pemasukan atau pengeluaran.',
            'description.max'=> 'Deskripsi maksimal 255 karakter.',
        ];
    }
}
