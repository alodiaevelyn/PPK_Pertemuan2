<?php

namespace App\Models\Traits;

use App\Enums\TaskStatus;

trait HasTaskProgress
{
    /**
     * Menghitung total seluruh tugas dalam daftar
     */
    public function getTotalTasksCountAttribute(): int
    {
        return $this->tasks()->count();
    }

    /**
     * Menghitung jumlah tugas yang telah selesai (SRS-004 & SRS-005)
     */
    public function getCompletedTasksCountAttribute(): int
    {
        return $this->tasks()->where('status', TaskStatus::COMPLETED->value)->count();
    }

    /**
     * Menghitung jumlah tugas yang sedang dikerjakan
     */
    public function getInProgressTasksCountAttribute(): int
    {
        return $this->tasks()->where('status', TaskStatus::IN_PROGRESS->value)->count();
    }

    /**
     * Menghitung jumlah tugas yang masih todo (belum dikerjakan)
     */
    public function getTodoTasksCountAttribute(): int
    {
        return $this->tasks()->where('status', TaskStatus::TODO->value)->count();
    }

    /**
     * Menghitung jumlah tugas yang melewati tenggat waktu dan belum selesai (SRS-003 & SRS-005)
     */
    public function getOverdueTasksCountAttribute(): int
    {
        return $this->tasks()
            ->where('status', '!=', TaskStatus::COMPLETED->value)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->count();
    }

    /**
     * Menghitung persentase progres penyelesaian tugas (0 - 100%) (SRS-005)
     * Mengembalikan 0 jika tidak ada tugas untuk mencegah pembagian dengan nol.
     */
    public function getProgressPercentageAttribute(): int
    {
        $total = $this->total_tasks_count;

        if ($total === 0) {
            return 0;
        }

        $completed = $this->completed_tasks_count;

        return (int) round(($completed / $total) * 100);
    }
}
