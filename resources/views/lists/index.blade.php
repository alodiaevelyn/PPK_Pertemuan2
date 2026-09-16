<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>List JARA</title>
</head>
<body>

    <h1>Daftar List / Project</h1>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <a href="/lists/create">+ Buat List Baru</a>

    <hr>

    @forelse ($lists as $list)

        <h2>{{ $list->name }}</h2>

        <p>{{ $list->description }}</p>

        <a href="/lists/{{ $list->id }}/tasks/create">
            + Tambah Tugas
        </a>

        <hr>

        <a href="/lists/{{ $list->id }}/members">
            Kelola Anggota
        </a>

        |
        <a href="{{ route('lists.progress', $list) }}">
            Pantau Progres
        </a>

        @can('delete', $list)
            |
            <form action="{{ route('lists.destroy', $list) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus daftar ini beserta seluruh tugas dan keanggotaan?');" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" style="color:red; background:none; border:none; cursor:pointer; text-decoration:underline;">
                    Hapus Daftar
                </button>
            </form>
        @endcan

    @empty

        <p>Belum ada project.</p>

    @endforelse

</body>
</html>