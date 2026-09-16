<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Menentukan apakah pengguna dapat melihat tugas.
     */
    public function view(User $user, Task $task): bool
    {
        $list = $task->taskList;

        return $user->id === $task->created_by
            || $user->id === $list->owner_id
            || $list->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Menentukan apakah pengguna dapat memperbarui tugas (SRS-003 & SRS-004).
     */
    public function update(User $user, Task $task): bool
    {
        $list = $task->taskList;

        return $user->id === $task->created_by
            || $user->id === $list->owner_id
            || $list->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Menentukan apakah pengguna dapat menghapus tugas.
     * Hanya pembuat tugas atau pemilik list yang berwenang.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->created_by || $user->id === $task->taskList->owner_id;
    }

    /**
     * Menentukan apakah pengguna dapat mengubah status penyelesaian tugas (SRS-004).
     */
    public function toggleComplete(User $user, Task $task): bool
    {
        return true;
    }
}
