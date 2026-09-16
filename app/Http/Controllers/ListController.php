<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskListRequest;
use App\Models\TaskList;
use Illuminate\Http\JsonResponse;
use App\Models\ListMember;
use App\Models\TaskList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ListController extends Controller
{
    /**
     * Menampilkan daftar project/list milik user (SRS-001)
     */
    public function index(): View|JsonResponse
    {
        $lists = TaskList::where('owner_id', Auth::id())
            ->withCount('tasks')
            ->latest()
            ->get();

        if (request()->wantsJson()) {
            return response()->json(['lists' => $lists]);
        }

        return view('lists.index', compact('lists'));
    }

    /**
     * Menampilkan form membuat project/list (SRS-001)
     */
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
     * Menyimpan project/list baru secara atomik (SRS-008 & SRS-009)
     */
    public function store(StoreTaskListRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        $list = DB::transaction(function () use ($validated) {
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

            // Menjadikan pembuat sebagai anggota awal dengan peran manager
            $list->members()->attach(Auth::id(), [
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

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'List/project berhasil dibuat.',
                'list' => $list->load('members'),
            ], 201);
        }

        return redirect('/lists')
            ->with('success', 'List/project berhasil dibuat!');
    }

    /**
     * Menghapus daftar beserta seluruh tugas, penugasan, dan keanggotaan secara atomik (SRS-008 & SRS-009)
     */
    public function destroy(Request $request, TaskList $list): RedirectResponse|JsonResponse
    {
        // Otorisasi: Hanya pemilik daftar yang berwenang (SRS-009)
        Gate::authorize('delete', $list);

        // Eksekusi atomik penghapusan seluruh entitas terkait (SRS-008)
        DB::transaction(function () use ($list) {
            // 1. Hapus penugasan pada semua tugas di list
            $taskIds = $list->tasks()->pluck('id');
            if ($taskIds->isNotEmpty()) {
                DB::table('task_assignments')->whereIn('task_id', $taskIds)->delete();
            }

            // 2. Hapus tugas-tugas di dalam list
            $list->tasks()->delete();

            // 3. Hapus kategori tugas
            $list->categories()->delete();

            // 4. Hapus seluruh keanggotaan di list_members
            $list->members()->detach();

            // 5. Hapus list itu sendiri
            $list->delete();
        });

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Daftar dan seluruh data di dalamnya berhasil dihapus.',
            ]);
        }

        return redirect('/lists')
            ->with('success', 'Daftar dan seluruh data di dalamnya berhasil dihapus.');
    }
}
        return redirect('/lists')
            ->with('success', "List/project '{$list->name}' berhasil dibuat!");
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
