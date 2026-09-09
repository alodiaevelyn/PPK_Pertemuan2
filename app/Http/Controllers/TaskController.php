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