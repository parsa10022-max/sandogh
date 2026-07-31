@extends('layouts.app')

@section('content')

    <div class="card">

        <div class="card-header">
            اطلاعات حساب
        </div>

        <div class="card-body">

            <p>
                عضو:
                {{ $account->customer->first_name }}
                {{ $account->customer->last_name }}
            </p>

            <p>
                شماره حساب:
                <span dir="ltr">
              {{ $account->account_number }}
                </span>
            </p>

            <p>
                نوع حساب:
                {{ $account->account_type->label() }}
            </p>

            <p>
                موجودی:
                {{ number_format($account->balance) }}
                ریال
            </p>


            <a href="{{ route('accounts.deposit.create', $account) }}"
               class="btn btn-success">
                واریز
            </a>

            <a href="{{ route('accounts.transactions', $account) }}"
               class="btn btn-primary">
                گردش حساب
            </a>

        </div>

    </div>

@endsection
