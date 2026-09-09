<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Anggota - JARA</title>
</head>
<body>

    <h1>{{ $list->name }}</h1>

    <h2>Anggota Project</h2>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    @forelse ($members as $member)
        <p>
            {{ $member->user->name }}
            - {{ $member->user->email }}
            - {{ $member->role }}
        </p>
    @empty
        <p>Belum ada anggota.</p>
    @endforelse

    <hr>

    <h2>Tambah Anggota</h2>

    <form action="/lists/{{ $list->id }}/members" method="POST">
        @csrf

        <label>Email pengguna</label><br>
        <input type="email" name="email">
        <br><br>

        <label>Role</label><br>
        <select name="role">
            <option value="member">Member</option>
            <option value="manager">Manager</option>
        </select>

        <br><br>

        <button type="submit">Tambah Anggota</button>
    </form>

    <br>

    <a href="/lists">Kembali ke Project</a>

</body>
</html> 