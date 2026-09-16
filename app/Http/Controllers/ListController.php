<?php

namespace App\Http\Controllers;

use App\Models\TaskList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ListController extends Controller
{
    // Menampilkan daftar project/list milik user
    public function index()
    {
        $lists = TaskList::where('owner_id', Auth::id())
            ->latest()
            ->get();

        return view('lists.index', compact('lists'));
    }

    // Menampilkan form membuat project/list
    public function create()
    {
        return view('lists.create');
    }

    // Menyimpan project/list baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
        ]);

        TaskList::create([
            'id' => 'LST-' . strtoupper(Str::random(16)),
            'name' => $request->name,
            'description' => $request->description,
            'owner_id' => Auth::id(),
        ]);

        return redirect('/lists')
            ->with('success', 'List/project berhasil dibuat!');
    }
}