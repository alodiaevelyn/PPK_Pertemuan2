<?php

namespace App\Enums;

enum TaskStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';

    /**
     * Label representasi teks Bahasa Indonesia
     */
    public function label(): string
    {
        return match ($this) {
            self::TODO => 'Belum Dikerjakan',
            self::IN_PROGRESS => 'Sedang Dikerjakan',
            self::COMPLETED => 'Selesai',
        };
    }

    /**
     * Kelas warna badge untuk Tailwind CSS
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::TODO => 'bg-zinc-100 text-zinc-700 border-zinc-300 dark:bg-zinc-800 dark:text-zinc-300',
            self::IN_PROGRESS => 'bg-sky-100 text-sky-800 border-sky-300 dark:bg-sky-950 dark:text-sky-300',
            self::COMPLETED => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-950 dark:text-emerald-300',
        };
    }
}
