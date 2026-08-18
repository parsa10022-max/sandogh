
@extends('layouts.app')

@section('title', 'درخواست‌های برداشت')

@section('content')

    <div class="container">

        <div class="card shadow-sm border-0">

            {{-- Header --}}
            <div class="card-header bg-light">

                <h5 class="mb-0">
                    درخواست‌های برداشت
                </h5>

            </div>


            <div class="card-body">

                @if(session('success'))

                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>

                @endif


                @if($withdrawals->count())

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover align-middle">

                            <thead class="table-light">

                            <tr>

                                <th>
                                    مشتری
                                </th>

                                <th>
                                    شماره حساب
                                </th>

                                <th>
                                    مبلغ
                                </th>

                                <th>
                                    بانک مقصد
                                </th>

                                <th>
                                    شماره شبا
                                </th>

                                <th>
                                    وضعیت
                                </th>

                                <th>
                                    تاریخ درخواست
                                </th>

                                <th>
                                    عملیات
                                </th>

                            </tr>

                            </thead>


                            <tbody>

                            @foreach($withdrawals as $withdrawal)

                                <tr>

                                    {{-- مشتری --}}
                                    <td>

                                        <strong>
                                            {{ $withdrawal->account->customer->first_name }}
                                            {{ $withdrawal->account->customer->last_name }}
                                        </strong>

                                    </td>


                                    {{-- شماره حساب --}}
                                    <td>

                                        {{ $withdrawal->account->account_number }}

                                    </td>


                                    {{-- مبلغ --}}
                                    <td>

                                        <strong>
                                            {{ number_format($withdrawal->amount) }}
                                        </strong>

                                        <small>
                                            ریال
                                        </small>

                                    </td>


                                    {{-- بانک مقصد --}}
                                    <td>

                                        {{ \App\Support\Iban::bankName(
                                            $withdrawal->iban
                                        ) }}

                                    </td>


                                    {{-- شماره شبا --}}
                                    <td dir="ltr">

                                        {{ \App\Support\Iban::format(
                                            $withdrawal->iban
                                        ) }}

                                    </td>


                                    {{-- وضعیت --}}
                                    <td>

                                        @if($withdrawal->status instanceof \App\Enums\WithdrawalStatus)

                                            <span
                                                class="badge bg-{{ $withdrawal->status->badge() }}"
                                            >
                                                {{ $withdrawal->status->label() }}
                                            </span>

                                        @else

                                            <span class="badge bg-secondary">
                                                نامشخص
                                            </span>

                                        @endif

                                    </td>


                                    {{-- تاریخ --}}
                                    <td>

                                        {{ \Morilog\Jalali\Jalalian::fromDateTime(
                                            $withdrawal->created_at
                                        )->format('Y/m/d H:i') }}

                                    </td>


                                    {{-- عملیات --}}
                                    <td>

                                        <a
                                            href="{{ route(
                                                'withdrawals.show',
                                                $withdrawal
                                            ) }}"
                                            class="btn btn-sm btn-primary"
                                        >

                                            <i class="bi bi-eye"></i>

                                            مشاهده

                                        </a>

                                    </td>

                                </tr>

                            @endforeach

                            </tbody>

                        </table>

                    </div>


                    {{-- Pagination --}}
                    <div class="mt-3">

                        {{ $withdrawals->links() }}

                    </div>


                @else

                    <div class="alert alert-info mb-0">

                        <i class="bi bi-info-circle"></i>

                        درخواست برداشتی وجود ندارد.

                    </div>

                @endif

            </div>

        </div>

    </div>

@endsection

