@extends('layouts.app')

@section('title','واریز به حساب پس‌انداز')

@section('content')

    <div class="container">

        <div class="card shadow-sm">

            <div class="card-header">
                <h5>
                    واریز به حساب پس‌انداز
                    <span class="text-primary">
        {{ $account->customer->first_name }}
        {{ $account->customer->last_name }}
    </span>
                </h5>
            </div>


            <div class="card-body">

                <p>
                    شماره حساب:
                    <strong dir="ltr">
                        {{ $account->prefix }}
                        {{ $account->account_number }}
                    </strong>
                </p>


                <p>
                    موجودی فعلی:
                    {{ number_format($account->balance) }}
                    ریال
                </p>



                <form method="POST"
                      action="{{ route('customer.savings.deposit.store') }}">

                    @csrf


                    <div class="mb-3">

                        <label>
                            مبلغ واریز
                        </label>


                        <input
                            type="number"
                            name="amount"
                            class="form-control"
                            min="50000"
                            required>

                    </div>


                    <button class="btn btn-success">

                        ادامه پرداخت

                    </button>


                </form>


            </div>

        </div>

    </div>

@endsection
