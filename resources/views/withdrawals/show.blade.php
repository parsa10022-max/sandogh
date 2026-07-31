@extends('layouts.app')

@section('title', 'مشاهده درخواست برداشت')

@section('content')

    <div class="container">

        <div class="card shadow-sm border-0">

            <div class="card-header bg-light">

                <h5 class="mb-0">
                    جزئیات درخواست برداشت
                </h5>

            </div>


            <div class="card-body">


                @if(session('success'))

                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>

                @endif


                <div class="row">


                    <div class="col-md-6 mb-3">

                        <div class="d-flex">

                        <span class="text-muted me-2">
                            مشتری:
                        </span>

                            <strong>
                                {{ $withdrawal->account->customer->first_name }}
                                {{ $withdrawal->account->customer->last_name }}
                            </strong>

                        </div>

                    </div>



                    <div class="col-md-6 mb-3">

                        <div class="d-flex">

                        <span class="text-muted me-2">
                            شماره حساب:
                        </span>

                            <strong>
                                {{ $withdrawal->account->account_number }}
                            </strong>

                        </div>

                    </div>



                    <div class="col-md-6 mb-3">

                        <div class="d-flex">

                        <span class="text-muted me-2">
                            مبلغ برداشت:
                        </span>

                            <strong>
                                {{ number_format($withdrawal->amount) }}
                                ریال
                            </strong>

                        </div>

                    </div>



                    <div class="col-md-6 mb-3">

                        <div class="d-flex">

                        <span class="text-muted me-2">
                            بانک مقصد:
                        </span>

                            <strong>
                                {{ \App\Support\Iban::bankName(
                                    $withdrawal->account->customer->iban
                                ) }}
                            </strong>

                        </div>

                    </div>



                    <div class="col-md-6">

                        <label class="form-label">
                            شماره شبا
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ \App\Support\Iban::format($withdrawal->iban) }}"
                            readonly>

                    </div>

                    <div class="col-md-6">

                        <label class="form-label">
                            بانک مقصد
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ \App\Support\Iban::bankName($withdrawal->iban) }}"
                            readonly>

                    </div>



                    <div class="col-md-6 mb-3">

                        <div class="d-flex align-items-center">

        <span class="text-muted me-2">
            وضعیت:
        </span>

                            @switch($withdrawal->status)

                                @case(\App\Enums\WithdrawalStatus::PENDING)

                                <span class="badge bg-warning">
                    در انتظار پرداخت
                </span>

                                @break

                                @case(\App\Enums\WithdrawalStatus::PAID)

                                <span class="badge bg-success">
                    پرداخت شده
                </span>

                                @break

                                @case(\App\Enums\WithdrawalStatus::REJECTED)

                                <span class="badge bg-danger">
                    رد شده
                </span>

                                @break

                                @case(\App\Enums\WithdrawalStatus::CANCELLED)

                                <span class="badge bg-secondary">
                    لغو شده توسط مشتری
                </span>

                                @break

                            @endswitch

                        </div>

                    </div>



                    <div class="col-md-6 mb-3">

                        <div class="d-flex">

                        <span class="text-muted me-2">
                            تاریخ درخواست:
                        </span>

                            <strong>

                                {{ \Morilog\Jalali\Jalalian::fromDateTime(
                                    $withdrawal->created_at
                                )->format('Y/m/d H:i') }}

                            </strong>

                        </div>

                    </div>



                    <div class="col-md-6 mb-3">

                        <div class="d-flex">

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


    </div>

    @if($withdrawal->status === \App\Enums\WithdrawalStatus::PENDING)

        <hr>

        <form method="POST"
              action="{{ route('withdrawals.approve', $withdrawal) }}">

            @csrf

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="form-label">
                        بانک پرداخت کننده
                    </label>

                    <select
                        name="payment_bank"
                        class="form-select"
                        required>

                        <option value="">
                            انتخاب کنید
                        </option>

                        @foreach(\App\Enums\PaymentBank::cases() as $bank)

                            <option value="{{ $bank->value }}">

                                {{ $bank->label() }}

                            </option>

                        @endforeach

                    </select>

                </div>


                <div class="col-md-6 mb-3">

                    <label class="form-label">

                        شماره پیگیری

                    </label>

                    <input
                        type="text"
                        name="payment_tracking_code"
                        class="form-control"
                        required>

                </div>

            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">

                {{-- فرم تایید --}}
                <button
                    type="submit"
                    class="btn btn-success">

                    <i class="bi bi-check-circle"></i>

                    تایید پرداخت

                </button>

        </form>

        {{-- فرم رد --}}
        <form
            method="POST"
            action="{{ route('withdrawals.reject', $withdrawal) }}">

            @csrf

            <button
                type="submit"
                class="btn btn-danger"
                onclick="return confirm('آیا از رد این درخواست اطمینان دارید؟')">

                <i class="bi bi-x-circle"></i>

                رد درخواست

            </button>

        </form>

        </div>






    @endif

@endsection
