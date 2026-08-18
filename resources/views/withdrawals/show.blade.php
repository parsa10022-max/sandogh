@extends('layouts.app')

@section('title', 'مشاهده درخواست برداشت')

@section('content')

    <div class="container">

        {{-- =========================================================
             WITHDRAWAL INFORMATION
        ========================================================== --}}

        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-light">

                <h5 class="mb-0">
                    جزئیات درخواست برداشت
                </h5>

            </div>


            <div class="card-body">

                {{-- Success --}}
                @if(session('success'))

                    <div class="alert alert-success">

                        <i class="bi bi-check-circle me-1"></i>

                        {{ session('success') }}

                    </div>

                @endif


                {{-- Error --}}
                @if(session('error'))

                    <div class="alert alert-danger">

                        <i class="bi bi-exclamation-triangle me-1"></i>

                        {{ session('error') }}

                    </div>

                @endif


                {{-- Validation Errors --}}
                @if($errors->any())

                    <div class="alert alert-danger">

                        <ul class="mb-0">

                            @foreach($errors->all() as $error)

                                <li>
                                    {{ $error }}
                                </li>

                            @endforeach

                        </ul>

                    </div>

                @endif


                <div class="row">

                    {{-- =================================================
                         CUSTOMER
                    ================================================== --}}

                    <div class="col-md-6 mb-3">

                        <div class="d-flex align-items-center">

                            <span class="text-muted me-2">
                                مشتری:
                            </span>

                            <strong>

                                {{ $withdrawal->account->customer->first_name }}
                                {{ $withdrawal->account->customer->last_name }}

                            </strong>

                        </div>

                    </div>


                    {{-- =================================================
                         ACCOUNT NUMBER
                    ================================================== --}}

                    <div class="col-md-6 mb-3">

                        <div class="d-flex align-items-center">

                            <span class="text-muted me-2">
                                شماره حساب:
                            </span>

                            <strong dir="ltr">

                                {{ $withdrawal->account->account_number }}

                            </strong>

                        </div>

                    </div>


                    {{-- =================================================
                         AMOUNT
                    ================================================== --}}

                    <div class="col-md-6 mb-3">

                        <div class="d-flex align-items-center">

                            <span class="text-muted me-2">
                                مبلغ برداشت:
                            </span>

                            <strong>

                                {{ number_format($withdrawal->amount) }}

                                ریال

                            </strong>

                        </div>

                    </div>


                    {{-- =================================================
                         STATUS
                    ================================================== --}}

                    <div class="col-md-6 mb-3">

                        <div class="d-flex align-items-center">

                            <span class="text-muted me-2">
                                وضعیت:
                            </span>

                            @if($withdrawal->status instanceof \App\Enums\WithdrawalStatus)

                                <span
                                    class="badge bg-{{ $withdrawal->status->badge() }}"
                                >
                                    {{ $withdrawal->status->label() }}
                                </span>

                            @else

                                <span class="badge bg-secondary">
                                    {{ $withdrawal->status }}
                                </span>

                            @endif

                        </div>

                    </div>


                    {{-- =================================================
                         IBAN
                    ================================================== --}}

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            شماره شبا
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ \App\Support\Iban::format($withdrawal->iban) }}"
                            readonly
                            dir="ltr"
                        >

                    </div>


                    {{-- =================================================
                         DESTINATION BANK
                    ================================================== --}}

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            بانک مقصد
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ \App\Support\Iban::bankName($withdrawal->iban) }}"
                            readonly
                        >

                    </div>


                    {{-- =================================================
                         REQUEST DATE
                    ================================================== --}}

                    <div class="col-md-6 mb-3">

                        <div class="d-flex align-items-center">

                            <span class="text-muted me-2">
                                تاریخ درخواست:
                            </span>

                            <strong dir="ltr">

                                {{ \Morilog\Jalali\Jalalian::fromDateTime(
                                    $withdrawal->created_at
                                )->format('Y/m/d H:i') }}

                            </strong>

                        </div>

                    </div>


                    {{-- =================================================
                         DESCRIPTION
                    ================================================== --}}

                    <div class="col-md-6 mb-3">

                        <div class="d-flex align-items-center">

                            <span class="text-muted me-2">
                                توضیحات:
                            </span>

                            <strong>

                                {{ $withdrawal->description ?? '-' }}

                            </strong>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        {{-- =========================================================
             PAID INFORMATION
        ========================================================== --}}

        @if($withdrawal->status === \App\Enums\WithdrawalStatus::PAID)

            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header bg-success text-white">

                    <h6 class="mb-0">

                        <i class="bi bi-check-circle me-1"></i>

                        اطلاعات پرداخت

                    </h6>

                </div>


                <div class="card-body">

                    <div class="row">

                        {{-- =================================================
                             PAYMENT BANK
                        ================================================== --}}

                        <div class="col-md-6 mb-3">

                            <span class="text-muted d-block mb-1">
                                بانک پرداخت‌کننده
                            </span>

                            <strong>

                                @if($withdrawal->payment_bank instanceof \App\Enums\PaymentBank)

                                    {{ $withdrawal->payment_bank->label() }}

                                @else

                                    -

                                @endif

                            </strong>

                        </div>


                        {{-- =================================================
                             TRACKING CODE
                        ================================================== --}}

                        <div class="col-md-6 mb-3">

                            <span class="text-muted d-block mb-1">
                                کد پیگیری پرداخت
                            </span>

                            <strong dir="ltr">

                                {{ $withdrawal->payment_tracking_code ?? '-' }}

                            </strong>

                        </div>


                        {{-- =================================================
                             PAID BY
                        ================================================== --}}

                        <div class="col-md-6 mb-3">

                            <span class="text-muted d-block mb-1">
                                ثبت‌کننده پرداخت
                            </span>

                            <strong>

                                {{ $withdrawal->paidBy?->username ?? '-' }}

                            </strong>

                        </div>


                        {{-- =================================================
                             PAID AT
                        ================================================== --}}

                        <div class="col-md-6 mb-3">

                            <span class="text-muted d-block mb-1">
                                تاریخ پرداخت
                            </span>

                            <strong dir="ltr">

                                @if($withdrawal->paid_at)

                                    {{ \Morilog\Jalali\Jalalian::fromDateTime(
                                        $withdrawal->paid_at
                                    )->format('Y/m/d H:i') }}

                                @else

                                    -

                                @endif

                            </strong>

                        </div>

                    </div>

                </div>

            </div>

        @endif



        {{-- =========================================================
             PENDING ACTIONS
        ========================================================== --}}

        @if($withdrawal->status === \App\Enums\WithdrawalStatus::PENDING)

            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header bg-light">

                    <h6 class="mb-0">

                        <i class="bi bi-gear me-1"></i>

                        مدیریت درخواست برداشت

                    </h6>

                </div>


                <div class="card-body">

                    {{-- =================================================
                         APPROVE PAYMENT
                    ================================================== --}}

                    <form
                        method="POST"
                        action="{{ route('withdrawals.approve', $withdrawal) }}"
                    >

                        @csrf

                        <div class="row">

                            {{-- بانک پرداخت‌کننده --}}
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    بانک پرداخت‌کننده
                                </label>

                                <select
                                    name="payment_bank"
                                    class="form-select @error('payment_bank') is-invalid @enderror"
                                    required
                                >

                                    <option value="">
                                        انتخاب بانک
                                    </option>

                                    @foreach(\App\Enums\PaymentBank::cases() as $bank)

                                        <option
                                            value="{{ $bank->value }}"
                                            @selected(old('payment_bank') == $bank->value)
                                        >

                                        {{ $bank->label() }}

                                        </option>

                                    @endforeach

                                </select>

                                @error('payment_bank')

                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>

                                @enderror

                            </div>


                            {{-- کد پیگیری --}}
                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    کد پیگیری پرداخت
                                </label>

                                <input
                                    type="text"
                                    name="payment_tracking_code"
                                    class="form-control @error('payment_tracking_code') is-invalid @enderror"
                                    value="{{ old('payment_tracking_code') }}"
                                    maxlength="100"
                                    required
                                >

                                @error('payment_tracking_code')

                                <div class="invalid-feedback">

                                    {{ $message }}

                                </div>

                                @enderror

                            </div>

                        </div>


                        {{-- ثبت پرداخت --}}
                        <div class="mt-3">

                            <button
                                type="submit"
                                class="btn btn-success"
                                onclick="return confirm('آیا پرداخت این درخواست انجام شده است؟')"
                            >

                                <i class="bi bi-check-circle me-1"></i>

                                ثبت پرداخت

                            </button>

                        </div>

                    </form>


                    {{-- =================================================
                         REJECT
                    ================================================== --}}

                    <form
                        method="POST"
                        action="{{ route('withdrawals.reject', $withdrawal) }}"
                        class="mt-2"
                    >

                        @csrf

                        <button
                            type="submit"
                            class="btn btn-danger"
                            onclick="return confirm('آیا از رد این درخواست اطمینان دارید؟ مبلغ به حساب مشتری بازگردانده خواهد شد.')"
                        >

                            <i class="bi bi-x-circle me-1"></i>

                            رد درخواست

                        </button>

                    </form>

                </div>

            </div>

        @endif

    </div>

@endsection
