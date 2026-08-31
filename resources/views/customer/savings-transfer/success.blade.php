@extends('layouts.app')
@section('content')

    <div class="container">
        <div class="card shadow-sm">


            <div class="card-header bg-success text-white">

                پرداخت موفق

            </div>


            <div class="card-body">


                <div class="alert alert-success">

                    واریز با موفقیت انجام شد.

                </div>



                <table class="table">


                    <tr>

                        <td>
                            کد رهگیری صندوق
                        </td>

                        <td>
                            {{ $transfer->tracking_code }}
                        </td>

                    </tr>



                    <tr>

                        <td>
                            مبلغ واریز
                        </td>

                        <td>
                            {{ number_format($transfer->amount) }}
                            ریال
                        </td>

                    </tr>



                    <tr>

                        <td>
                            تاریخ پرداخت
                        </td>

                        <td>
                            {{ $transfer->paid_at?->format('Y/m/d H:i') }}
                        </td>

                    </tr>



                </table>



                <a href="{{ route('customer.dashboard') }}"
                   class="btn btn-primary">

                    بازگشت

                </a>


            </div>


        </div>


    </div>

@endsection
