<?php

namespace App\Http\Controllers;

use App\Models\ListMember;
use App\Models\TaskList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ListController extends Controller
{
    // Menampilkan daftar project/list milik user
    public function index(): View
    {
        $lists = TaskList::where('owner_id', Auth::id())
            ->latest()
            ->get();

        return view('lists.index', compact('lists'));
    }

    // Menampilkan form membuat project/list
    public function create(): View
    {
        return view('lists.create');
    }

    /**
     * Menyimpan daftar tugas (list/project) baru (SRS-006).
     *
     * Acceptance criteria:
     * - Pengguna dapat membuat daftar tugas baru.
     * - Pengguna yang membuat daftar otomatis menjadi pemilik daftar.
     * - Data daftar yang dibuat tersimpan dengan benar.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
        ]);

        $list = DB::transaction(function () use ($validated) {
            // Pengguna yang membuat daftar otomatis menjadi pemilik daftar.
            $list = TaskList::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'owner_id' => Auth::id(),
            ]);

            // Pemilik otomatis tercatat sebagai anggota dengan role manager,
            // supaya daftar langsung konsisten dengan fitur keanggotaan (list_members).
            ListMember::create([
                'list_id' => $list->id,
                'user_id' => Auth::id(),
                'role' => 'manager',
                'joined_at' => now(),
            ]);

            return $list;
        });

        return redirect('/lists')
            ->with('success', "List/project '{$list->name}' berhasil dibuat!");
    }
}