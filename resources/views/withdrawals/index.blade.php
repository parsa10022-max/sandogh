@extends('layouts.app')

@section('title', 'درخواست‌های برداشت')

@section('content')

    <div class="container">

        <div class="card shadow-sm border-0">

            <div class="card-header bg-light">
                <h5 class="mb-0">
                    درخواست‌های برداشت
                </h5>
            </div>


            <div class="card-body">

                @if($withdrawals->count())

                    <div class="table-responsive">

                        <table class="table table-bordered align-middle">

                            <thead class="table-light">

                            <tr>
                                <th>مشتری</th>
                                <th>شماره حساب</th>
                                <th>مبلغ</th>
                                <th>بانک مقصد</th>
                                <th>شماره شبا</th>
                                <th>وضعیت</th>
                                <th>تاریخ درخواست</th>
                                <th>
                                    عملیات
                                </th>
                            </tr>

                            </thead>


                            <tbody>

                            @foreach($withdrawals as $withdrawal)

                                <tr>

                                    <td>
                                        {{ $withdrawal->account->customer->first_name }}
                                        {{ $withdrawal->account->customer->last_name }}
                                    </td>


                                    <td>
                                        {{ $withdrawal->account->account_number }}
                                    </td>


                                    <td>
                                        {{ number_format($withdrawal->amount) }}
                                        ریال
                                    </td>


                                    <td>
                                        {{ \App\Support\Iban::bankName(
                                            $withdrawal->iban
                                        ) }}
                                    </td>


                                    <td dir="ltr">

                                        {{ \App\Support\Iban::format(
                                            $withdrawal->iban
                                        ) }}

                                    </td>

                                    <td>
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


                                        @endswitch

                                    </td>


                                    <td>
                                        {{ \Morilog\Jalali\Jalalian::fromDateTime(
                                            $withdrawal->created_at
                                        )->format('Y/m/d H:i') }}
                                    </td>

                                    <td>

                                        <a href="{{ route('withdrawals.show',$withdrawal) }}"
                                           class="btn btn-sm btn-primary">

                                            مشاهده

                                        </a>

                                    </td>
                                </tr>

                            @endforeach

                            </tbody>

                        </table>

                    </div>


                    {{ $withdrawals->links() }}


                @else

                    <div class="alert alert-info">
                        درخواست برداشتی وجود ندارد.
                    </div>

                @endif


            </div>

        </div>

    </div>

@endsection
