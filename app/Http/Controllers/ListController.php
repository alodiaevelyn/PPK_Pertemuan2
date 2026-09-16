<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskListRequest;
use App\Models\TaskList;
use Illuminate\Http\JsonResponse;
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
            $list = TaskList::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'owner_id' => Auth::id(),
            ]);

            // Menjadikan pembuat sebagai anggota awal dengan peran manager
            $list->members()->attach(Auth::id(), [
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
