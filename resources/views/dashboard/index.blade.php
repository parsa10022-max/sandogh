<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>داشبورد</title>
</head>
<body>

<h1>داشبورد</h1>

<p>خوش آمدید {{ Auth::user()->username }}</p>

<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit">
        خروج
    </button>
</form>

</body>
</html>
