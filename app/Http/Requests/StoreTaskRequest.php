<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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

    public function messages(): array
    {
        return [
            'title.required' => 'Judul tugas wajib diisi.',
            'title.max' => 'Judul tugas maksimal 200 karakter.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'priority.required' => 'Prioritas tugas wajib dipilih.',
            'priority.enum' => 'Prioritas tugas yang dipilih tidak valid (Rendah, Sedang, atau Tinggi).',
            'due_date.date' => 'Format tenggat waktu tidak valid.',
        ];
    }
}
