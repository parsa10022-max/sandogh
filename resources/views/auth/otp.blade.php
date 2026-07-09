<!DOCTYPE html>
<html lang="fa">

<head>
    <meta charset="UTF-8">
    <title>تأیید ورود</title>
</head>

<body>

<h2>تأیید ورود</h2>

<p>
    کد ارسال شده به شماره موبایل خود را وارد کنید.
</p>

@if ($errors->any())
    <div style="color:red">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@if(config('app.debug') && $otp)

    <div style="
        background:#fff3cd;
        border:1px solid #ffeeba;
        padding:15px;
        margin-bottom:20px;
        border-radius:8px;
    ">

        <strong>کد تست OTP:</strong>

        <span style="font-size:22px">
            {{ $otp->code }}
        </span>

    </div>

@endif
<form method="POST" action="{{ route('otp.verify') }}">

    @csrf

    <div>

        <label>کد تأیید</label>

        <input
            type="text"
            name="code"
            value="{{ old('code') }}"
            maxlength="6"
            autofocus>

    </div>

    <br>

    <button type="submit">
        تأیید
    </button>

</form>

</body>

</html>
