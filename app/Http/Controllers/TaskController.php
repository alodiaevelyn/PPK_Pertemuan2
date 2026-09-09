<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Models\TaskList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class TaskController extends Controller
{
    /**
     * Menyimpan tugas baru ke dalam daftar dengan prioritas dan batas waktu (SRS-003)
     */
    public function store(StoreTaskRequest $request, TaskList $list): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = auth()->id();
        $validated['status'] = TaskStatus::TODO->value;

        $task = $list->tasks()->create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Tugas berhasil ditambahkan.',
                'task' => $task->load(['category', 'creator']),
            ], 201);
        }

        return back()->with('success', 'Tugas berhasil ditambahkan.');
    }

    /**
     * Memperbarui detail tugas, prioritas, status, atau tenggat waktu (SRS-003 & SRS-004)
     */
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        // SRS-004: Sinkronisasi waktu completed_at saat status berubah
        if (isset($validated['status'])) {
            if ($validated['status'] === TaskStatus::COMPLETED->value && ! $task->isCompleted()) {
                $validated['completed_at'] = now();
            } elseif ($validated['status'] !== TaskStatus::COMPLETED->value) {
                $validated['completed_at'] = null;
            }
        }

        $task->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Tugas berhasil diperbarui.',
                'task' => $task,
            ]);
        }

        return back()->with('success', 'Tugas berhasil diperbarui.');
    }

    /**
     * Menandai tugas sebagai selesai atau mengembalikan ke todo (SRS-004)
     */
    public function toggleComplete(Task $task): RedirectResponse|JsonResponse
    {
        $task->toggleComplete();

        $statusLabel = $task->status->label();

        if (request()->wantsJson()) {
            return response()->json([
                'message' => "Status tugas berhasil diubah menjadi {$statusLabel}.",
                'task_id' => $task->id,
                'status' => $task->status->value,
                'is_completed' => $task->isCompleted(),
                'completed_at' => $task->completed_at?->toIso8601String(),
                'list_progress' => $task->taskList->progress_percentage,
            ]);
        }

        return back()->with('success', "Status tugas berhasil diubah menjadi {$statusLabel}.");
    }

    /**
     * Menghapus tugas
     */
    public function destroy(Task $task): RedirectResponse|JsonResponse
    {
        $task->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Tugas berhasil dihapus.']);
        }

        return back()->with('success', 'Tugas berhasil dihapus.');
    }
}
