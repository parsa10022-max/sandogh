<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>ورود</title>
</head>
<body>
@error('username')
<div style="color:red">
    {{ $message }}
</div>
@enderror
<h2>ورود به سامانه</h2>

<form method="POST" action="{{ route('login.store') }}">
    @csrf

    <div>
        <label>نام کاربری</label>

        <input
            type="text"
            name="username"
            value="{{ old('username') }}"
        >
    </div>

    <br>

    <div>
        <label>رمز عبور</label>

        <input
            type="password"
            name="password"
        >
    </div>

    <br>

    <button type="submit">
        ورود
    </button>

</form>

</body>
</html>
