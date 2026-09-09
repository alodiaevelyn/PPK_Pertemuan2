<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-6 shadow-sm mb-6">
    <h3 class="text-lg font-bold text-zinc-900 dark:text-zinc-100 mb-4">Tambah Tugas Baru ke Daftar</h3>

    <form action="{{ route('tasks.store', $list) }}" method="POST" class="space-y-4">
        @csrf

        {{-- Judul Tugas --}}
        <div>
            <label for="title" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Judul Tugas *</label>
            <input type="text" name="title" id="title" required maxlength="200" placeholder="Contoh: Menyusun modul praktikum..." 
                   class="w-full px-3.5 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            @error('title') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Deskripsi Tugas --}}
        <div>
            <label for="description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Detail Tugas</label>
            <textarea name="description" id="description" rows="2" placeholder="Catatan atau instruksi tugas..." 
                      class="w-full px-3.5 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-emerald-500 focus:outline-none"></textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- SRS-003: Menentukan Prioritas Tugas --}}
            <div>
                <label for="priority" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Prioritas *</label>
                <select name="priority" id="priority" required
                        class="w-full px-3.5 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="low">Rendah (Low)</option>
                    <option value="medium" selected>Sedang (Medium)</option>
                    <option value="high">Tinggi (High)</option>
                </select>
                @error('priority') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- SRS-003: Menentukan Waktu Tugas (Deadline) --}}
            <div>
                <label for="due_date" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Tenggat Waktu</label>
                <input type="datetime-local" name="due_date" id="due_date"
                       class="w-full px-3.5 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                @error('due_date') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Kategori Tugas (Opsional) --}}
            <div>
                <label for="category_id" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Kategori</label>
                <select name="category_id" id="category_id"
                        class="w-full px-3.5 py-2 rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <option value="">Tanpa Kategori</option>
                    @if(isset($list->categories))
                        @foreach($list->categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium shadow-sm transition-colors cursor-pointer">
                Tambah Tugas
            </button>
        </div>
    </form>
</div>
