<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Tugas - JARA</title>
</head>
<body>

    <h1>Tambah Tugas</h1>

    <p>
        Project:
        <strong>{{ $list->name }}</strong>
    </p>

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="/lists/{{ $list->id }}/tasks" method="POST">
        @csrf

        <label>Judul Tugas</label><br>
        <input type="text" name="title">
        <br><br>

        <label>Prioritas</label><br>
        <select name="priority">
            <option value="low">Rendah (Low)</option>
            <option value="medium" selected>Sedang (Medium)</option>
            <option value="high">Tinggi (High)</option>
        </select>
        <br><br>

        <label>Tenggat Waktu (Opsional)</label><br>
        <input type="datetime-local" name="due_date">
        <br><br>

        <button type="submit">Tambah Tugas</button>
    </form>

    <br>

    <a href="/lists">Kembali ke Project</a>

</body>
</html>