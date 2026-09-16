<?php

namespace App\Http\Controllers;

use App\Models\ListMember;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ListMemberController extends Controller
{
    // Menampilkan anggota dalam sebuah project
    public function index($listId)
    {
        $list = TaskList::where('id', $listId)
            ->where('owner_id', Auth::id())
            ->firstOrFail();

        $members = ListMember::where('list_id', $list->id)
            ->with('user')
            ->get();

        return view('lists.members', compact('list', 'members'));
    }

    // Menambahkan anggota
    public function store(Request $request, $listId)
    {
        $list = TaskList::where('id', $listId)
            ->where('owner_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:member,manager',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
        if (!$user) {
            return back()->withErrors([
                'email' => 'Pengguna dengan email tersebut tidak ditemukan.',
            ]);
        }

        $alreadyMember = ListMember::where('list_id', $list->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyMember) {
            return back()->withErrors([
                'email' => 'Pengguna tersebut sudah menjadi anggota.',
            ]);
        }

        ListMember::create([
            'id' => 'LM-' . strtoupper(Str::random(17)),
            'list_id' => $list->id,
            'user_id' => $user->id,
            'role' => $request->role,
            'joined_at' => now(),
        ]);

        return redirect("/lists/{$list->id}/members")
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }
}
}
