@extends('layouts.app')

@section('title', 'درگاه پرداخت آزمایشی')

@section('content')

    <div class="container py-4">

        <div class="row justify-content-center">

            <div class="col-lg-7">

                <div class="card shadow">

                    <div class="card-header bg-primary text-white">

                        <h5 class="mb-0">
                            <i class="bi bi-credit-card"></i>
                            درگاه پرداخت آزمایشی
                        </h5>

                    </div>


                    <div class="card-body">


                        <div class="alert alert-info">
                            این صفحه فقط برای تست سیستم پرداخت صندوق است.
                        </div>


                        @php
                            $paymentType = $data['payment_type'] ?? null;
                        @endphp



                        <table class="table table-bordered align-middle">


                            {{-- ======================= وام ======================= --}}
                            @if(in_array($paymentType, ['loan', 'loan_payment', 'installment']))


                                <tr>
                                    <th width="220">
                                        نوع پرداخت
                                    </th>

                                    <td>
                                        پرداخت قسط وام
                                    </td>
                                </tr>


                                <tr>
                                    <th>
                                        شماره وام
                                    </th>

                                    <td>
                                        {{ $data['loan_id'] ?? '-' }}
                                    </td>
                                </tr>


                                <tr>
                                    <th>
                                        شناسه قسط
                                    </th>

                                    <td>
                                        {{ $data['installment_id'] ?? '-' }}
                                    </td>
                                </tr>



                                {{-- ======================= پس انداز ======================= --}}
                            @elseif($paymentType === 'savings_transfer')


                                <tr>
                                    <th width="220">
                                        نوع پرداخت
                                    </th>

                                    <td>
                                        واریز به حساب پس‌انداز عضو
                                    </td>
                                </tr>


                                <tr>
                                    <th>
                                        شماره پیگیری
                                    </th>

                                    <td>
                                        {{ $data['tracking_code'] ?? '-' }}
                                    </td>
                                </tr>




                                {{-- ======================= کمک ======================= --}}
                            @elseif(in_array($paymentType, [
                                'donation',
                                'donation_customer',
                                'donation_public'
                            ]))


                                <tr>
                                    <th width="220">
                                        نوع پرداخت
                                    </th>

                                    <td>
                                        کمک به:

                                        <strong class="text-success">

                                            {{ $data['account_name'] ?? '-' }}

                                        </strong>

                                    </td>
                                </tr>



                                <tr>
                                    <th>
                                        شماره حساب مقصد
                                    </th>

                                    <td>
                                        {{ $data['account_number'] ?? '-' }}
                                    </td>
                                </tr>



                                <tr>
                                    <th>
                                        شماره پیگیری
                                    </th>

                                    <td>
                                        {{ $data['tracking_code'] ?? '-' }}
                                    </td>
                                </tr>


                            @endif




                            <tr>
                                <th>
                                    مبلغ
                                </th>

                                <td>

                                    {{ number_format($data['amount'] ?? 0) }}

                                    ریال

                                </td>
                            </tr>



                            <tr>
                                <th>
                                    کد رهگیری صندوق
                                </th>

                                <td>
                                    {{ $data['tracking_code'] ?? '-' }}
                                </td>
                            </tr>



                            <tr>
                                <th>
                                    توکن پرداخت
                                </th>

                                <td>
                                    {{ $data['token'] ?? '-' }}
                                </td>
                            </tr>


                        </table>



                        <hr>



                        <div class="d-flex justify-content-center gap-2 flex-wrap">


                            <a href="{{ route('payments.callback', array_merge($data, [
                            'result' => 'success'
                        ])) }}"
                               class="btn btn-success">


                                <i class="bi bi-check-circle"></i>

                                پرداخت موفق


                            </a>




                            <a href="{{ route('payments.callback', array_merge($data, [
                            'result' => 'failed'
                        ])) }}"
                               class="btn btn-danger">


                                <i class="bi bi-x-circle"></i>

                                پرداخت ناموفق


                            </a>




                            @if(in_array($paymentType, [
         'donation',
         'donation_customer',
         'donation_public'
     ]))

                                <a href="{{ route('customer.donations.payment', [
        'donationPayment' => $data['reference_id']
    ]) }}"
                                   class="btn btn-warning">

                                    <i class="bi bi-arrow-counterclockwise"></i>

                                    انصراف

                                </a>

                            @else

                                <button type="button"
                                        onclick="history.back()"
                                        class="btn btn-warning">

                                    <i class="bi bi-arrow-counterclockwise"></i>

                                    انصراف

                                </button>

                            @endif



                        </div>


                    </div>

                </div>

            </div>

        </div>

    </div>

@endsection
