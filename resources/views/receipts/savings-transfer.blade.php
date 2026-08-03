@extends('layouts.app')

@section('content')

    <div class="container">

        <div class="alert alert-success">
            واریز به حساب پس‌انداز با موفقیت انجام شد.
        </div>


        <table class="table table-bordered">

            <tr>
                <th>کد رهگیری</th>
                <td>
                    {{ $transfer->tracking_code }}
                </td>
            </tr>


            <tr>
                <th>مبلغ</th>
                <td>
                    {{ number_format($transfer->amount) }}
                    ریال
                </td>
            </tr>


            <tr>
                <th>تاریخ پرداخت</th>
                <td>
                    {{ $transfer->paid_at }}
                </td>
            </tr>

        </table>

    </div>

@endsection
