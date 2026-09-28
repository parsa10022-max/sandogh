@extends('layouts.app')

@section('title', 'آمار صندوق')

@section('content')

    <div class="container-fluid py-4">

        <div class="fund-statistics-page">

            <div class="fund-statistics-header">
                <div class="fund-statistics-title">

                    <div class="fund-statistics-title-icon">
                        <i class="bi bi-bar-chart-line"></i>
                    </div>

                    <div>
                        <h4>آمار صندوق</h4>
                        <p>
                            اطلاعات آماری صندوق را بر اساس نرم‌افزار حسابداری وارد کنید.
                        </p>
                    </div>

                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success rounded-4 border-0 shadow-sm">
                    <i class="bi bi-check-circle me-1"></i>
                    {{ session('success') }}
                </div>
            @endif

            {{-- آمار ثبت‌شده فعلی --}}
            <div class="fund-statistics-current">

                <div class="fund-statistics-current-title">
                    <i class="bi bi-database-check"></i>
                    آمار ثبت‌شده فعلی
                </div>

                <div class="fund-statistics-current-grid">

                    <div class="fund-statistics-current-item">
                        <span>وام‌های پرداخت‌شده</span>

                        <strong>
                            {{ fa_number($statistic?->paid_loans_count ?? 0) }}
                            <small>فقره</small>
                        </strong>
                    </div>

                    <div class="fund-statistics-current-item">
                        <span>مبلغ وام‌های پرداخت‌شده</span>

                        <strong>
                            {{ fa_money($statistic?->paid_loans_amount ?? 0) }}
                            <small>ریال</small>
                        </strong>
                    </div>

                    <div class="fund-statistics-current-item">
                        <span>کمک‌های ثبت‌شده</span>

                        <strong>
                            {{ fa_number($statistic?->donations_count ?? 0) }}
                            <small>مورد</small>
                        </strong>
                    </div>

                    <div class="fund-statistics-current-item">
                        <span>آخرین تاریخ آمار</span>

                        <strong>
                            @if(
                                $statistic?->statistics_date &&
                                $statistic->statistics_date->format('Y-m-d') !== '0000-00-00'
                            )
                                {{ fa_number(
                                    \Morilog\Jalali\Jalalian::fromCarbon($statistic->statistics_date)->format('Y/m/d')
                                ) }}
                            @else
                                ---
                            @endif
                        </strong>
                    </div>

                </div>

            </div>

            <div class="fund-statistics-card">

                <form method="POST" action="{{ route('admin.fund-statistics.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">

                        {{-- تعداد وام --}}
                        <div class="col-lg-4 col-md-6">

                            <label class="fund-statistics-label">
                                <i class="bi bi-bank"></i>
                                افزایش وام‌های پرداخت‌شده
                            </label>

                            <div class="fund-statistics-input-wrap">

                                <input
                                    type="text"
                                    name="paid_loans_count"
                                    class="form-control fund-statistics-input number-format"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    value="{{ old('paid_loans_count', '0') }}"
                                >

                                <span class="fund-statistics-unit">
                                فقره
                            </span>

                            </div>

                            @error('paid_loans_count')
                            <div class="text-danger small mt-2">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>

                        {{-- مبلغ وام --}}
                        <div class="col-lg-4 col-md-6">

                            <label class="fund-statistics-label">
                                <i class="bi bi-cash-stack"></i>
                                افزایش مبلغ وام‌های پرداخت‌شده
                            </label>

                            <div class="fund-statistics-input-wrap">

                                <input
                                    type="text"
                                    name="paid_loans_amount"
                                    class="form-control fund-statistics-input number-format"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    value="{{ old('paid_loans_amount', '0') }}"
                                >

                                <span class="fund-statistics-unit">
                                ریال
                            </span>

                            </div>

                            @error('paid_loans_amount')
                            <div class="text-danger small mt-2">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>

                        {{-- کمک‌ها --}}
                        <div class="col-lg-4 col-md-6">

                            <label class="fund-statistics-label">
                                <i class="bi bi-heart"></i>
                                افزایش کمک‌های ثبت‌شده
                            </label>

                            <div class="fund-statistics-input-wrap">

                                <input
                                    type="text"
                                    name="donations_count"
                                    class="form-control fund-statistics-input number-format"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    value="{{ old('donations_count', '0') }}"
                                >

                                <span class="fund-statistics-unit">
                                مورد
                            </span>

                            </div>

                            @error('donations_count')
                            <div class="text-danger small mt-2">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>

                        {{-- تاریخ --}}
                        <div class="col-lg-4 col-md-6">

                            <label class="fund-statistics-label">
                                <i class="bi bi-calendar3"></i>
                                تاریخ آمار
                            </label>

                            <div class="fund-statistics-input-wrap">

                                <input
                                    type="text"
                                    name="statistics_date"
                                    class="form-control fund-statistics-input"
                                    data-jdp
                                    autocomplete="off"
                                    placeholder="انتخاب تاریخ"
                                    value="{{ old(
                                    'statistics_date',
                                    isset($statistic) && $statistic->statistics_date
                                        ? \Morilog\Jalali\Jalalian::fromCarbon($statistic->statistics_date)->format('Y/m/d')
                                        : ''
                                ) }}"
                                >

                                <span class="fund-statistics-unit">
                                <i class="bi bi-calendar-date"></i>
                            </span>

                            </div>

                            @error('statistics_date')
                            <div class="text-danger small mt-2">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>

                    </div>

                    <div class="fund-statistics-footer">

                        <div class="fund-statistics-hint">
                            <i class="bi bi-info-circle"></i>
                            مقادیر واردشده به آمار قبلی اضافه خواهند شد.
                        </div>

                        <button type="submit" class="btn fund-statistics-save-btn">
                            <i class="bi bi-check2-circle"></i>
                            ذخیره آمار
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>


    <style>

        .fund-statistics-page {
            max-width: 1200px;
            margin: 0 auto;
        }

        .fund-statistics-header {
            margin-bottom: 20px;
        }

        .fund-statistics-title {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .fund-statistics-title-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0eaff;
            color: #6f42c1;
            font-size: 21px;
        }

        .fund-statistics-title h4 {
            margin: 0 0 4px;
            font-size: 18px;
            font-weight: 700;
            color: #292d38;
        }

        .fund-statistics-title p {
            margin: 0;
            color: #858995;
            font-size: 12px;
        }

        /* آمار فعلی */

        .fund-statistics-current {
            background: #f8f7fc;
            border: 1px solid #e9e6f2;
            border-radius: 16px;
            padding: 18px;
            margin-bottom: 20px;
        }

        .fund-statistics-current-title {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 15px;
            color: #454957;
            font-size: 12px;
            font-weight: 700;
        }

        .fund-statistics-current-title i {
            color: #6f42c1;
            font-size: 15px;
        }

        .fund-statistics-current-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .fund-statistics-current-item {
            background: #fff;
            border: 1px solid #eceaf2;
            border-radius: 12px;
            padding: 13px 15px;
        }

        .fund-statistics-current-item span {
            display: block;
            color: #858995;
            font-size: 11px;
            margin-bottom: 7px;
        }

        .fund-statistics-current-item strong {
            color: #292d38;
            font-size: 16px;
            font-weight: 700;
        }

        .fund-statistics-current-item small {
            color: #858995;
            font-size: 10px;
            font-weight: 400;
        }

        /* فرم */

        .fund-statistics-card {
            background: #fff;
            border: 1px solid #e9e8f0;
            border-radius: 18px;
            padding: 26px;
            box-shadow: 0 8px 28px rgba(40, 43, 55, .05);
        }

        .fund-statistics-label {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 9px;
            color: #454957;
            font-size: 12px;
            font-weight: 700;
        }

        .fund-statistics-label i {
            color: #6f42c1;
            font-size: 14px;
        }

        .fund-statistics-input-wrap {
            position: relative;
        }

        .fund-statistics-input {
            height: 46px;
            padding-left: 58px;
            border: 1px solid #e4e3eb;
            border-radius: 12px;
            background: #fafaff;
            color: #292d38;
            font-size: 13px;
            font-weight: 600;
            direction: ltr;
            text-align: right;
            transition: .2s ease;
        }

        .fund-statistics-input:focus {
            border-color: #8b6dcc;
            box-shadow: 0 0 0 3px rgba(111, 66, 193, .08);
            background: #fff;
        }

        .fund-statistics-unit {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #8b8f9a;
            font-size: 11px;
            pointer-events: none;
        }

        .fund-statistics-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #f0eef5;
        }

        .fund-statistics-hint {
            color: #858995;
            font-size: 11px;
        }

        .fund-statistics-hint i {
            color: #6f42c1;
            margin-left: 4px;
        }

        .fund-statistics-save-btn {
            min-width: 130px;
            height: 42px;
            border: 0;
            border-radius: 11px;
            background: #6f42c1;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
        }

        .fund-statistics-save-btn:hover {
            background: #5f35ad;
            color: #fff;
        }

        @media (max-width: 992px) {

            .fund-statistics-current-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

        }

        @media (max-width: 576px) {

            .fund-statistics-card {
                padding: 18px;
                border-radius: 15px;
            }

            .fund-statistics-title h4 {
                font-size: 16px;
            }

            .fund-statistics-current-grid {
                grid-template-columns: 1fr;
            }

            .fund-statistics-footer {
                align-items: stretch;
                flex-direction: column;
            }

            .fund-statistics-save-btn {
                width: 100%;
            }

        }

    </style>


    <script>

        document.addEventListener('DOMContentLoaded', function () {

            document.querySelectorAll('.number-format').forEach(function (input) {

                input.addEventListener('input', function () {

                    let value = this.value
                        .replace(/[۰-۹٠-٩]/g, function (digit) {
                            return {
                                '۰': '0',
                                '۱': '1',
                                '۲': '2',
                                '۳': '3',
                                '۴': '4',
                                '۵': '5',
                                '۶': '6',
                                '۷': '7',
                                '۸': '8',
                                '۹': '9',
                                '٠': '0',
                                '١': '1',
                                '٢': '2',
                                '٣': '3',
                                '٤': '4',
                                '٥': '5',
                                '٦': '6',
                                '٧': '7',
                                '٨': '8',
                                '٩': '9'
                            }[digit];
                        })
                        .replace(/[^\d]/g, '');

                    if (!value) {
                        this.value = '';
                        return;
                    }

                    this.value = Number(value).toLocaleString('fa-IR');
                });

            });

            document.querySelector('form').addEventListener('submit', function () {

                document.querySelectorAll('.number-format').forEach(function (input) {

                    input.value = input.value
                        .replace(/[۰-۹]/g, function (digit) {
                            return {
                                '۰': '0',
                                '۱': '1',
                                '۲': '2',
                                '۳': '3',
                                '۴': '4',
                                '۵': '5',
                                '۶': '6',
                                '۷': '7',
                                '۸': '8',
                                '۹': '9'
                            }[digit];
                        })
                        .replace(/[٬,،\s]/g, '');

                });

            });

        });

    </script>

@endsection
