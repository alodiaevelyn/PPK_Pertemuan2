<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat List - JARA</title>
</head>
<body>

    <h1>Buat List / Project</h1>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="/lists" method="POST">
        @csrf

        <label>Nama List / Project</label><br>
        <input type="text" name="name">
        <br><br>

        <label>Deskripsi</label><br>
        <textarea name="description"></textarea>
        <br><br>

        <button type="submit">Buat Project</button>
    </form>

    <br>

    <a href="/lists">Kembali</a>

</body>
</html>