<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\TaskList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListProgressController extends Controller
{
    /**
     * Menampilkan dashboard progres penyelesaian tugas bagi pemilik daftar (SRS-005)
     */
    public function show(Request $request, TaskList $list): View|JsonResponse
    {
        // Otorisasi Kriteria Penerimaan SRS-005: Pemilik daftar dapat memantau progres tugas
        if ($list->owner_id !== auth()->id()) {
            abort(403, 'Akses ditolak. Hanya pemilik daftar yang dapat memantau progres penyelesaian.');
        }

        $filterStatus = $request->query('status');
        $filterPriority = $request->query('priority');

        $tasksQuery = $list->tasks()->with(['creator', 'category', 'assignees']);

        // Filter berdasarkan status
        if ($filterStatus && TaskStatus::tryFrom($filterStatus)) {
            $tasksQuery->where('status', $filterStatus);
        }

        // Filter berdasarkan prioritas (SRS-003)
        if ($filterPriority && TaskPriority::tryFrom($filterPriority)) {
            $tasksQuery->byPriority(TaskPriority::from($filterPriority));
        }

        $tasks = $tasksQuery->orderBy('due_date')->get();

        // Metrik statistik progres daftar (SRS-005)
        $statistics = [
            'total' => $list->total_tasks_count,
            'completed' => $list->completed_tasks_count,
            'in_progress' => $list->in_progress_tasks_count,
            'todo' => $list->todo_tasks_count,
            'overdue' => $list->overdue_tasks_count,
            'percentage' => $list->progress_percentage,
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'list' => $list,
                'statistics' => $statistics,
                'tasks' => $tasks,
            ]);
        }

        return view('lists.progress', compact('list', 'tasks', 'statistics', 'filterStatus', 'filterPriority'));
    }
}
