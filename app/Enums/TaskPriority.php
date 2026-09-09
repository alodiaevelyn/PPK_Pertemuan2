<?php

namespace App\Enums;

enum TaskPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    /**
     * Label representasi teks Bahasa Indonesia
     */
    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Rendah',
            self::MEDIUM => 'Sedang',
            self::HIGH => 'Tinggi',
        };
    }

    /**
     * Kelas warna badge untuk Tailwind CSS
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::LOW => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-950 dark:text-emerald-300',
            self::MEDIUM => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950 dark:text-amber-300',
            self::HIGH => 'bg-rose-100 text-rose-800 border-rose-300 dark:bg-rose-950 dark:text-rose-300',
        };
    }

    /**
     * Titik warna indikator (bullet)
     */
    public function dotColor(): string
    {
        return match ($this) {
            self::LOW => 'bg-emerald-500',
            self::MEDIUM => 'bg-amber-500',
            self::HIGH => 'bg-rose-500',
        };
    }
}
