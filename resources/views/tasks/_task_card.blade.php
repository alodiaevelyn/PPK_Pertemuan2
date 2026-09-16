<div class="flex items-start justify-between p-4 bg-white dark:bg-zinc-900 border rounded-xl shadow-xs transition-all duration-200 {{ $task->isCompleted() ? 'border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/40' : 'border-zinc-300 dark:border-zinc-700' }}">
    <div class="flex items-start gap-3.5 flex-1 min-w-0">
        {{-- SRS-004: Checkbox Interaktif untuk Menandai Tugas Selesai --}}
        <form action="{{ route('tasks.toggle-complete', $task) }}" method="POST" class="pt-0.5">
            @csrf
            @method('PATCH')
            <button type="submit" 
                    class="w-5 h-5 rounded-md border flex items-center justify-center transition-colors cursor-pointer {{ $task->isCompleted() ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-zinc-400 hover:border-zinc-600 dark:border-zinc-600' }}"
                    title="{{ $task->isCompleted() ? 'Tandai belum selesai' : 'Tandai selesai' }}">
                @if($task->isCompleted())
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                @endif
            </button>
        </form>

        <div class="flex-1 min-w-0">
            {{-- SRS-004: Judul Tugas dengan Efek Coret jika Selesai --}}
            <h4 class="text-base font-semibold leading-tight {{ $task->isCompleted() ? 'line-through text-zinc-400 dark:text-zinc-500' : 'text-zinc-900 dark:text-zinc-100' }}">
                {{ $task->title }}
            </h4>

            @if($task->description)
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400 line-clamp-2 {{ $task->isCompleted() ? 'line-through opacity-70' : '' }}">
                    {{ $task->description }}
                </p>
            @endif

            {{-- Metadata Tugas: Kategori, Prioritas, Waktu, & Status Selesai --}}
            <div class="mt-2.5 flex flex-wrap items-center gap-2 text-xs">
                
                {{-- Kategori Tugas --}}
                @if($task->category)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md font-medium bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                        📁 {{ $task->category->name }}
                    </span>
                @endif

                {{-- SRS-003: Badge Prioritas Tugas --}}
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-medium border {{ $task->priority->badgeClasses() }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $task->priority->dotColor() }}"></span>
                    Prioritas {{ $task->priority->label() }}
                </span>

                {{-- SRS-003: Tenggat Waktu Tugas --}}
                @if($task->due_date)
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-medium border {{ $task->isOverdue() ? 'bg-rose-100 text-rose-800 border-rose-300 dark:bg-rose-950 dark:text-rose-300 animate-pulse' : 'bg-zinc-100 text-zinc-700 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        {{ $task->due_date->translatedFormat('d M Y, H:i') }}
                        @if($task->isOverdue())
                            <span class="font-bold">(Terlambat)</span>
                        @endif
                    </span>
                @endif

                {{-- SRS-004: Indikator Status Tugas Selesai & Timestamp --}}
                @if($task->isCompleted())
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-medium bg-emerald-100 text-emerald-800 border border-emerald-300 dark:bg-emerald-950 dark:text-emerald-300">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Selesai {{ $task->completed_at ? 'pada ' . $task->completed_at->translatedFormat('d M H:i') : '' }}
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full font-medium {{ $task->status->badgeClasses() }}">
                        {{ $task->status->label() }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Aksi Hapus Tugas --}}
    <form action="{{ route('tasks.destroy', $task) }}" method="POST" onsubmit="return confirm('Hapus tugas ini?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="p-1.5 text-zinc-400 hover:text-rose-600 transition-colors rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 cursor-pointer" title="Hapus Tugas">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
        </button>
    </form>
</div>
