<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TaskController extends Controller
{
    // Menampilkan form tambah tugas
    public function create($listId)
    {
        $list = TaskList::where('id', $listId)
            ->where('owner_id', Auth::id())
            ->firstOrFail();

        return view('tasks.create', compact('list'));
    }

    // Menyimpan tugas ke dalam list/project
    public function store(Request $request, $listId)
    {
        $list = TaskList::where('id', $listId)
            ->where('owner_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
        ]);

        Task::create([
            'id' => 'TSK-' . strtoupper(Str::random(16)),
            'list_id' => $list->id,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => 'medium',
            'status' => 'todo',
            'created_by' => Auth::id(),
        ]);

        return redirect('/lists')
            ->with('success', 'Tugas berhasil ditambahkan ke project!');
    }
}
use App\Enums\TaskStatus;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use App\Models\TaskList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TaskController extends Controller
{
    /**
     * Menampilkan form pembuatan tugas dalam daftar
     */
    public function create(TaskList $list): View
    {
        Gate::authorize('createTask', $list);

        return view('tasks.create', compact('list'));
    }

    /**
     * Menyimpan tugas baru ke dalam daftar secara atomik (SRS-003, SRS-008, SRS-009)
     * Pengguna otomatis menjadi pembuat dan pemilik/penanggung jawab awal.
     */
    public function store(StoreTaskRequest $request, TaskList $list): RedirectResponse|JsonResponse
    {
        // Otorisasi sudah diperiksa di StoreTaskRequest::authorize() (SRS-009)

        $task = DB::transaction(function () use ($request, $list) {
            $validated = $request->validated();
            $validated['created_by'] = auth()->id();
            $validated['status'] = TaskStatus::TODO->value;

            $task = $list->tasks()->create($validated);

            // Pengguna otomatis menjadi pemilik / penanggung jawab tugas
            $task->assignees()->attach(auth()->id(), [
                'assigned_at' => now(),
            ]);

            return $task;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Tugas berhasil ditambahkan.',
                'task' => $task->load(['category', 'creator', 'assignees']),
            ], 201);
        }

        return redirect("/lists/{$list->id}/tasks/create")
            ->with('success', 'Tugas berhasil ditambahkan.');
    }

    /**
     * Memperbarui detail tugas, prioritas, status, atau tenggat waktu (SRS-003 & SRS-004)
     */
    public function update(UpdateTaskRequest $request, Task $task): RedirectResponse|JsonResponse
    {
        Gate::authorize('update', $task);

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
        Gate::authorize('toggleComplete', $task);

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
     * Menghapus tugas secara atomik (SRS-008 & SRS-009)
     */
    public function destroy(Task $task): RedirectResponse|JsonResponse
    {
        Gate::authorize('delete', $task);

        DB::transaction(function () use ($task) {
            $task->assignees()->detach();
            $task->delete();
        });

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Tugas berhasil dihapus.']);
        }

        return back()->with('success', 'Tugas berhasil dihapus.');
    }
}
