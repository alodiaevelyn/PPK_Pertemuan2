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

    @empty

        <p>Belum ada project.</p>

    @endforelse

</body>
</html>