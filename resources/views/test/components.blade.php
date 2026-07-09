@extends('layouts.bootstrap')
@section('content')
    <h2 class="mb-4">تست Bootstrap</h2>
    <button class="btn btn-primary">
        تست Bootstrap
    </button>
    <form>

        <div class="row">
            <x-inputs.text-input
                label="نام"
                name="name"
                placeholder="نام را وارد کنید"
                required
                maxlength="50"
                minlength="3"
                autofocus
                dir="rtl"
                class="bg-warning border-info  bg-light"
            />
            <x-inputs.file-input
                label="عکس"
                name="image"

                />
            <x-inputs.textarea-input
                label="توضیحات"
                name="description"
            />
            <x-inputs.select-input
                label="نوع حساب"
                name="account_type"
                :options="[
        '1' => 'پس‌انداز',
        '2' => 'وام',
        '3' => 'جاری'
    ]"
            />
            <x-inputs.password-input
                label="رمز عبور"
                name="password"
                placeholder="رمز عبور"
                required
            />
        </div>
        <x-inputs.mobile-input
            label="شماره موبایل"
            name="mobile"
            required
        />
        <x-inputs.national-code-input
            label="کد ملی"
            name="national_code"
            required
        />
        <x-inputs.money-input
            label="  پول "
            name="money"
            required
        />
        <x-inputs.sheba-input
            label="  شبا "
            name="sheba"
            required
        />
        <x-inputs.date-input
            label="  تاریخ "
            name="deta"
            required
        />


    </form>
@endsection

