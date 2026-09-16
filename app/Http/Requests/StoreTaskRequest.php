<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Models\TaskList;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    /**
     * Memeriksa otorisasi pengguna untuk menambahkan tugas ke dalam list (SRS-009).
     * Hanya pemilik daftar atau anggota terdaftar yang diizinkan.
     */
    public function authorize(): bool
    {
        $list = $this->route('list');

        if (is_numeric($list) || is_string($list)) {
            $list = TaskList::find($list);
        }

        if (! $list instanceof TaskList) {
            return false;
        }

        return $this->user()?->can('createTask', $list) ?? false;
    }

    /**
     * Aturan validasi input penambahan tugas (SRS-009 & SRS-003).
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:task_categories,id'],
            // SRS-003: Menetapkan prioritas tugas (low, medium, high)
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            // SRS-003: Menentukan batas waktu tugas (format tanggal/waktu valid)
            'due_date' => ['nullable', 'date'],
        ];
    }

    /**
     * Pesan kustom validasi input.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul tugas wajib diisi.',
            'title.string' => 'Judul tugas harus berupa teks.',
            'title.max' => 'Judul tugas maksimal 200 karakter.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'priority.required' => 'Prioritas tugas wajib dipilih.',
            'priority.enum' => 'Prioritas tugas yang dipilih tidak valid (Rendah, Sedang, atau Tinggi).',
            'due_date.date' => 'Format tenggat waktu tidak valid.',
        ];
    }
}
