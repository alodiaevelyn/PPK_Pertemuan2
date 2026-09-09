<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Pengguna - JARA</title>
</head>
<body>

    <h1>Tambah Pengguna</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="/admin/users" method="POST">
        @csrf

        <label>Nama</label><br>
        <input type="text" name="name" value="{{ old('name') }}">
        <br><br>

        <label>Email</label><br>
        <input type="email" name="email" value="{{ old('email') }}">
        <br><br>

        <label>Password</label><br>
        <input type="password" name="password">
        <br><br>

        <label>Konfirmasi Password</label><br>
        <input type="password" name="password_confirmation">
        <br><br>

        <label>Role</label><br>
        <select name="role">
            <option value="user">User</option>
            <option value="admin">Admin</option>
        </select>

        <br><br>

        <button type="submit">Tambah Pengguna</button>
    </form>

    <br>

    <a href="/admin/users">Kembali</a>

</body>
</html>