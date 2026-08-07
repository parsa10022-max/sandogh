@extends('layouts.app')

@section('title','لیست کمک‌ها')

@section('content')

    <div class="container py-4">

        <div class="card shadow">

            <div class="card-header">
                <h5 class="mb-0">
                    لیست کمک‌های ثبت شده
                </h5>
            </div>
            <div class="card border border-2 shadow-sm mb-4">

                <div class="card-header bg-light fw-bold">

                    <i class="bi bi-bank"></i>

                    حساب‌های سیستمی

                </div>


                <div class="card-body p-3">


                    <div class="row g-3">

                        @foreach($accounts as $account)

                            <div class="col-md-4 col-lg-3">

                                <a href="{{ route('accounts.transactions',$account) }}"
                                   class="text-decoration-none">


                                    <div class="card shadow-sm border border-2 account-card">


                                        <div class="card-body p-3">


                                            <div class="d-flex justify-content-between align-items-center">

                                                <div class="fw-bold text-dark">

                                                    <i class="bi bi-wallet2 text-primary"></i>

                                                    {{ $account->name }}

                                                </div>


                                                <span class="badge bg-primary">

                                        {{ $account->account_number }}

                                    </span>


                                            </div>


                                            <hr class="my-2">


                                            <div class="small text-muted">

                                                موجودی

                                            </div>


                                            <div class="fw-bold text-success">

                                                {{ number_format($account->balance) }}

                                                ریال

                                            </div>


                                        </div>


                                    </div>


                                </a>

                            </div>

                        @endforeach


                    </div>


                </div>

            </div>

            <div class="card-body">

                <table class="table table-bordered table-hover text-center align-middle">

                    <thead class="table-light">

                    <tr>

                        <th>
                            تاریخ
                        </th>

                        <th>
                            عنوان حساب
                        </th>

                        <th>
                            شماره حساب
                        </th>

                        <th>
                            مبلغ
                        </th>

                        <th>
                            ثبت کننده
                        </th>

                        <th>
                            توضیحات
                        </th>

                    </tr>

                    </thead>


                    <tbody>


                    @forelse($transactions as $transaction)

                        <tr>


                            <td>

                                {{ \Morilog\Jalali\Jalalian::fromDateTime($transaction->transaction_date)
                                    ->format('Y/m/d') }}

                            </td>



                            <td>

                <span class="fw-bold">

                    {{ $transaction->account->name ?? '-' }}

                </span>

                            </td>



                            <td>

                <span class="text-primary">

                    {{ $transaction->account->account_number ?? '-' }}

                </span>

                            </td>



                            <td>

                <span class="fw-bold text-success">

                    {{ number_format($transaction->amount) }}

                </span>

                            </td>



                            <td>

                                {{ $transaction->creator->name ?? '-' }}

                            </td>



                            <td>

                                {{ $transaction->description ?? '-' }}

                            </td>


                        </tr>


                    @empty

                        <tr>

                            <td colspan="6">

                                موردی ثبت نشده است.

                            </td>

                        </tr>

                    @endforelse


                    </tbody>


                </table>


                {{ $transactions->links() }}

            </div>

        </div>

    </div>

@endsection
