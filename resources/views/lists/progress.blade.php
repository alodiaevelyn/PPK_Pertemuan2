<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progres Daftar: {{ $list->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-zinc-50 dark:bg-zinc-950 text-zinc-800 dark:text-zinc-200 antialiased min-h-screen py-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">

        {{-- Header Daftar Tugas (SRS-001 & SRS-005) --}}
        <div class="mb-8 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Pemantauan Progres Daftar (SRS-005)</span>
                <h1 class="text-3xl font-extrabold text-zinc-900 dark:text-white mt-1">{{ $list->name }}</h1>
                <p class="text-zinc-600 dark:text-zinc-400 mt-1">{{ $list->description ?: 'Tidak ada deskripsi daftar.' }}</p>
                @if($list->owner)
                    <p class="text-xs text-zinc-500 mt-1">Pemilik Daftar: <span class="font-medium text-zinc-800 dark:text-zinc-300">{{ $list->owner->name }}</span></p>
                @endif
            </div>
        </div>

        {{-- Notifikasi Flash Session --}}
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-950/50 dark:border-emerald-800 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        {{-- SRS-005: Komponen Progress Bar & Ringkasan Metrik --}}
        <x-task-progress-bar :list="$list" :statistics="$statistics" class="mb-8" />

        {{-- Form Tambah Tugas Baru (SRS-003) --}}
        @include('tasks._form_modal', ['list' => $list])

        {{-- Filter & Daftar Tugas (SRS-003, SRS-004, SRS-005) --}}
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">Daftar Tugas</h3>
                <div class="flex items-center gap-1.5 text-xs font-medium overflow-x-auto pb-1">
                    <a href="{{ route('lists.progress', $list) }}" 
                       class="px-3 py-1.5 rounded-lg transition-colors {{ !$filterStatus && !$filterPriority ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400' }}">Semua</a>
                    <a href="{{ route('lists.progress', [$list, 'status' => 'todo']) }}" 
                       class="px-3 py-1.5 rounded-lg transition-colors {{ $filterStatus === 'todo' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400' }}">Todo</a>
                    <a href="{{ route('lists.progress', [$list, 'status' => 'in_progress']) }}" 
                       class="px-3 py-1.5 rounded-lg transition-colors {{ $filterStatus === 'in_progress' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400' }}">Sedang Dikerjakan</a>
                    <a href="{{ route('lists.progress', [$list, 'status' => 'completed']) }}" 
                       class="px-3 py-1.5 rounded-lg transition-colors {{ $filterStatus === 'completed' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400' }}">Selesai</a>
                </div>
            </div>

            @forelse($tasks as $task)
                @include('tasks._task_card', ['task' => $task])
            @empty
                <div class="p-8 text-center bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-800 text-zinc-500">
                    Belum ada tugas pada kategori ini.
                </div>
            @endforelse
        </div>

    </div>
</body>
</html>
