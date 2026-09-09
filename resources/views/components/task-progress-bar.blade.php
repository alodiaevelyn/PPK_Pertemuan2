@props(['list', 'statistics' => null])

@php
    $stats = $statistics ?? [
        'total' => $list->total_tasks_count,
        'completed' => $list->completed_tasks_count,
        'in_progress' => $list->in_progress_tasks_count,
        'todo' => $list->todo_tasks_count,
        'overdue' => $list->overdue_tasks_count,
        'percentage' => $list->progress_percentage,
    ];
@endphp

<div {{ $attributes->merge(['class' => 'p-6 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-sm']) }}>
    <div class="flex items-center justify-between mb-3">
        <div>
            <h3 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Progres Penyelesaian Tugas</h3>
            <p class="text-sm text-zinc-500">{{ $stats['completed'] }} dari {{ $stats['total'] }} tugas telah diselesaikan</p>
        </div>
        <div class="text-right">
            <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ $stats['percentage'] }}%</span>
        </div>
    </div>

    {{-- Visual Progress Bar Dinamis --}}
    <div class="w-full bg-zinc-200 dark:bg-zinc-800 rounded-full h-3.5 overflow-hidden">
        <div class="bg-emerald-500 h-3.5 rounded-full transition-all duration-500 ease-out"
             style="width: {{ $stats['percentage'] }}%;"></div>
    </div>

    {{-- Ringkasan Metrik Statistik (SRS-005) --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-zinc-100 dark:border-zinc-800">
        <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/60">
            <p class="text-xs text-zinc-500 font-medium">Total Tugas</p>
            <p class="text-xl font-bold text-zinc-900 dark:text-zinc-100 mt-0.5">{{ $stats['total'] }}</p>
        </div>
        <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40">
            <p class="text-xs text-emerald-700 dark:text-emerald-400 font-medium">Selesai</p>
            <p class="text-xl font-bold text-emerald-700 dark:text-emerald-400 mt-0.5">{{ $stats['completed'] }}</p>
        </div>
        <div class="p-3 rounded-xl bg-sky-50 dark:bg-sky-950/40">
            <p class="text-xs text-sky-700 dark:text-sky-400 font-medium">Dalam Proses</p>
            <p class="text-xl font-bold text-sky-700 dark:text-sky-400 mt-0.5">{{ $stats['in_progress'] }}</p>
        </div>
        <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40">
            <p class="text-xs text-rose-700 dark:text-rose-400 font-medium">Terlambat</p>
            <p class="text-xl font-bold text-rose-700 dark:text-rose-400 mt-0.5">{{ $stats['overdue'] }}</p>
        </div>
    </div>
</div>
