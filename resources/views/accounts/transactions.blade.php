@extends('layouts.app')

@section('content')

    <div class="card">

        <div class="card-header d-flex justify-content-between">

            <div>
                گردش حساب
            </div>

            <div>
            <span dir="ltr">
                {{ $account->account_number }}
            </span>
            </div>

        </div>


        <div class="card-body">


            <div class="mb-3">

                عضو:
                {{ $account->customer->first_name }}
                {{ $account->customer->last_name }}

                <br>

                موجودی فعلی:

                {{ number_format($account->balance) }}
                ریال

            </div>



            <div class="table-responsive">

                <table class="table table-bordered table-striped">

                    <thead>

                    <tr>

                        <th>تاریخ</th>
                        <th>شماره تراکنش</th>
                        <th>نوع تراکنش </th>
                        <th>نوع</th>
                        <th>مبلغ</th>
                        <th>قبل</th>
                        <th>بعد</th>
                        <th>توضیح</th>

                    </tr>

                    </thead>


                    <tbody>

                    @forelse($transactions as $transaction)
                        @php
                            $jalaliDateService = app(\App\Services\Date\JalaliDateService::class);
                        @endphp
                        <tr>

                            <td>
                                {{ $jalaliDateService->toJalali($transaction->transaction_date) }}
                            </td>


                            <td dir="ltr">
                                {{ $transaction->transaction_no }}
                            </td>
                            <td dir="ltr">
                                {{ $transaction->payment_method?->label() ?? '-' }}
                            </td>

                            <td>

                                @if($transaction->transaction_type === \App\Enums\TransactionType::DEPOSIT)

                                    <span class="badge bg-success">
            {{ $transaction->transaction_type->label() }}
        </span>

                                @elseif($transaction->transaction_type === \App\Enums\TransactionType::WITHDRAWAL)

                                    <span class="badge bg-danger">
            {{ $transaction->transaction_type->label() }}
        </span>

                                @else

                                    <span class="badge bg-secondary">
            {{ $transaction->transaction_type->label() }}
        </span>

                                @endif
                            </td>



                            <td>
                                {{ number_format($transaction->amount) }}
                                ریال
                            </td>


                            <td>
                                {{ number_format($transaction->balance_before) }}
                                ریال
                            </td>


                            <td>
                                {{ number_format($transaction->balance_after) }}
                                ریال
                            </td>


                            <td>
                                {{ $transaction->description }}
                            </td>

                        </tr>


                    @empty

                        <tr>
                            <td colspan="7" class="text-center">
                                تراکنشی ثبت نشده است.
                            </td>
                        </tr>

                    @endforelse


                    </tbody>

                </table>

            </div>


            {{ $transactions->links() }}


        </div>

    </div>

@endsection
