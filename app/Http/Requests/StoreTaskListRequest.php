<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskListRequest extends FormRequest
{
    /**
     * Menentukan otorisasi request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Aturan validasi input pembuatan daftar (SRS-009).
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * Pesan kustom validasi input.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama daftar/project wajib diisi.',
            'name.string' => 'Nama daftar harus berupa teks.',
            'name.max' => 'Nama daftar maksimal 150 karakter.',
            'description.string' => 'Deskripsi harus berupa teks.',
        ];
    }
}
