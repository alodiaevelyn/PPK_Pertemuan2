<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - JARA</title>
</head>
<body>

    <h1>Login JARA</h1>

    {{-- Pesan registrasi berhasil --}}
    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    {{-- Pesan error --}}
    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="/login" method="POST">
        @csrf

        <label>Email</label><br>
        <input type="email" name="email">
        <br><br>

        <label>Password</label><br>
        <input type="password" name="password">
        <br><br>

        <button type="submit">Login</button>
    </form>

    <p>
        Belum punya akun?
        <a href="/register">Daftar</a>
    </p>

</body>
</html>