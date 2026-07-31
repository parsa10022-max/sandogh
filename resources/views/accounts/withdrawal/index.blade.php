@extends('layouts.app')

@section('title', 'برداشت‌های من')

@section('content')

    <div class="container">

        <div class="card shadow-sm border-0">

            <div class="card-header bg-light">
                <h5 class="mb-0">
                    برداشت‌های من
                </h5>
            </div>


            <div class="card-body">

                @if($withdrawals->count())

                    <div class="table-responsive">

                        <table class="table table-bordered align-middle">

                            <thead class="table-light">

                            <tr>
                                <th>مبلغ</th>
                                <th>بانک مقصد</th>
                                <th>شماره شبا</th>
                                <th>وضعیت</th>
                                <th>تاریخ درخواست</th>
                                <th>عملیات</th>
                            </tr>

                            </thead>


                            <tbody>

                            @foreach($withdrawals as $withdrawal)

                                <tr>

                                    <td>
                                        {{ number_format($withdrawal->amount) }}
                                        ریال
                                    </td>


                                    <td>
                                        {{ \App\Support\Iban::bankName(
                                            $withdrawal->account->customer->iban
                                        ) }}
                                    </td>


                                    <td dir="ltr">
                                        {{ \App\Support\Iban::format(
                                            $withdrawal->account->customer->iban
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


                                            @case(\App\Enums\WithdrawalStatus::CANCELLED)

                                            <span class="badge bg-secondary">
                                                لغو شده
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
                                           class="btn btn-sm btn-primary mb-1">

                                            مشاهده

                                        </a>


                                        @if($withdrawal->status === \App\Enums\WithdrawalStatus::PENDING)

                                            <form method="POST"
                                                  action="{{ route('withdrawals.cancel',$withdrawal) }}"
                                                  class="d-inline">

                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('آیا از لغو این درخواست اطمینان دارید؟')">

                                                    لغو درخواست

                                                </button>

                                            </form>

                                        @endif


                                    </td>

                                </tr>

                            @endforeach

                            </tbody>

                        </table>

                    </div>


                    {{ $withdrawals->links() }}


                @else

                    <div class="alert alert-info">
                        درخواست برداشتی ثبت نشده است.
                    </div>

                @endif


            </div>

        </div>

    </div>

@endsection
