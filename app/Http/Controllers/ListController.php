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

    public function destroy(Request $request, $id)
    {
        try {
            // 1Mencari data
            $list = TaskList::find($id);

            if (!$list) {
                return response()->json(['message' => 'Daftar tugas tidak ditemukan.'], 404);
            }

            if ($list->owner_id !== auth()->id()) {
                return response()->json(['message' => 'Akses ditolak: Hanya pemilik yang dapat menghapus daftar ini.'], 403);
            }

            DB::transaction(function () use ($id, $list) {
                // Hapus task_assignments (melalui relasi tasks)
                DB::table('task_assignment')
                  ->whereIn('task_id', function($query) use ($id) {
                      $query->select('id')->from('tasks')->where('list_id', $id);
                  })->delete();

                // Hapus tasks
                DB::table('tasks')->where('list_id', $id)->delete();

                // Hapus task_categories
                DB::table('task_categories')->where('list_id', $id)->delete();

                // Hapus list_members
                DB::table('list_members')->where('list_id', $id)->delete();

                // Hapus list utama
                $list->delete();
            });

            return response()->json(['message' => 'Daftar beserta seluruh tugas dan anggotanya berhasil dihapus.'], 200);

        } catch (Exception $e) {
            // Jika gagal, DB::transaction otomatis melakukan rollback
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus daftar.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}