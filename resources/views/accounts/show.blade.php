@extends('layouts.app')

@section('title', 'اطلاعات حساب')

@section('content')

    <div class="container-fluid py-4">

        <div class="card shadow-sm">

            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-bank"></i>
                    اطلاعات حساب
                </h5>
            </div>

            <div class="card-body">

                {{-- صاحب حساب --}}
                @if($account->customer)

                    <div class="mb-3">
                        <strong>عضو:</strong>

                        {{ $account->customer->first_name }}
                        {{ $account->customer->last_name }}
                    </div>

                    <div class="mb-3">
                        <strong>کد مشتری:</strong>

                        {{ $account->customer->customer_code }}
                    </div>

                @else

                    <div class="mb-3">
                        <strong>حساب سیستمی:</strong>

                        {{ $account->name ?? '-' }}
                    </div>

                @endif

                {{-- شماره حساب --}}
                <div class="mb-3">
                    <strong>شماره حساب:</strong>

                    <span dir="ltr">
                        {{ $account->account_number }}
                    </span>
                </div>

                {{-- نوع حساب --}}
                <div class="mb-3">
                    <strong>نوع حساب:</strong>

                    {{ $account->account_type->label() }}
                </div>

                {{-- موجودی فعلی --}}
                <div class="mb-4">
                    <strong>موجودی فعلی:</strong>

                    <span class="fw-bold">
                        {{ number_format($account->balance) }}
                        ریال
                    </span>
                </div>

                <hr>

                {{-- عملیات --}}
                <div class="d-flex flex-wrap gap-2">

                    <a href="{{ route('accounts.deposit.create', $account) }}"
                       class="btn btn-success">

                        <i class="bi bi-arrow-up-circle"></i>
                        واریز

                    </a>

                    <a href="{{ route('accounts.withdrawal.create', $account) }}"
                       class="btn btn-danger">

                        <i class="bi bi-arrow-down-circle"></i>
                        برداشت از حساب

                    </a>

                    <a href="{{ route('accounts.transactions', $account) }}"
                       class="btn btn-primary">

                        <i class="bi bi-list-ul"></i>
                        گردش حساب

                    </a>

                    <a href="{{ route('accounts.adjustment.create', $account) }}"
                       class="btn btn-warning">

                        <i class="bi bi-pencil-square"></i>
                        اصلاح موجودی

                    </a>

                </div>

            </div>

        </div>

    </div>

@endsection
