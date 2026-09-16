<?php

namespace App\Policies;

use App\Models\TaskList;
use App\Models\User;

class TaskListPolicy
{
    /**
     * Menentukan apakah pengguna dapat melihat detail list.
     */
    public function view(User $user, TaskList $list): bool
    {
        return $user->id === $list->owner_id || $list->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Menentukan apakah pengguna dapat membuat list baru.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Menentukan apakah pengguna dapat menambahkan tugas baru ke dalam list.
     * Pengguna harus menjadi pemilik atau anggota terdaftar (SRS-009).
     */
    public function createTask(User $user, TaskList $list): bool
    {
        return $user->id === $list->owner_id || $list->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Menentukan apakah pengguna dapat menghapus daftar beserta isinya.
     * HANYA PEMILIK DAFTAR yang memiliki kewenangan ini (SRS-008 & SRS-009).
     */
    public function delete(User $user, TaskList $list): bool
    {
        return $user->id === $list->owner_id;
    }

    /**
     * Menentukan apakah pengguna dapat memantau progres daftar (SRS-005).
     */
    public function viewProgress(User $user, TaskList $list): bool
    {
        return $user->id === $list->owner_id;
    }
}
