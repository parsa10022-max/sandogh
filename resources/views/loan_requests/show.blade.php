@extends('layouts.app')

@section('title', 'مشاهده درخواست وام')

@section('content')

    <div class="container-fluid loan-request-show-page">

        {{-- ========================================================= --}}
        {{-- Header --}}
        {{-- ========================================================= --}}

        <x-page-header title="مشاهده درخواست وام">

            <div class="loan-request-header-actions">

                <a
                    href="{{ route('loan-requests.edit', $loanRequest) }}"
                    class="btn loan-request-edit-btn"
                >
                    <i class="bi bi-pencil-square"></i>
                    <span>ویرایش درخواست</span>
                </a>

                <a
                    href="{{ route('loan-requests.index') }}"
                    class="btn loan-request-back-btn"
                >
                    <i class="bi bi-arrow-right"></i>
                    <span>بازگشت</span>
                </a>

            </div>

        </x-page-header>


        {{-- ========================================================= --}}
        {{-- اطلاعات اصلی درخواست --}}
        {{-- ========================================================= --}}

        <div class="loan-request-section-card">

            <div class="loan-request-section-header">

                <div class="loan-request-section-title">

                    <div class="loan-request-section-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>

                    <div>

                        <h5>
                            اطلاعات درخواست
                        </h5>

                        <span>
                            مشخصات و وضعیت درخواست وام
                        </span>

                    </div>

                </div>

            </div>


            <div class="loan-request-section-body">

                <div class="row g-3">

                    {{-- عضو --}}
                    <div class="col-12 col-md-6">

                        <div class="loan-request-info-item">

                            <span class="loan-request-info-label">
                                <i class="bi bi-person"></i>
                                عضو
                            </span>

                            <strong class="loan-request-info-value">
                                {{ $loanRequest->customer->full_name }}
                            </strong>

                        </div>

                    </div>


                    {{-- مبلغ درخواستی --}}
                    <div class="col-12 col-md-6">

                        <div class="loan-request-info-item">

                            <span class="loan-request-info-label">
                                <i class="bi bi-cash-stack"></i>
                                مبلغ درخواستی
                            </span>

                            <strong class="loan-request-info-value loan-request-money">
                                {{ number_format($loanRequest->requested_amount) }}
                                <small>ریال</small>
                            </strong>

                        </div>

                    </div>


                    {{-- وضعیت --}}
                    <div class="col-12 col-md-6">

                        <div class="loan-request-info-item">

                            <span class="loan-request-info-label">
                                <i class="bi bi-activity"></i>
                                وضعیت
                            </span>

                            <div>

                                @switch($loanRequest->status)

                                    @case(\App\Enums\LoanRequestStatus::PENDING)

                                    <span class="loan-request-status pending">
                                            <span class="loan-request-status-dot"></span>
                                            <i class="bi bi-hourglass-split"></i>
                                            در حال بررسی
                                        </span>

                                    @break

                                    @case(\App\Enums\LoanRequestStatus::APPROVED)

                                    <span class="loan-request-status approved">
                                            <span class="loan-request-status-dot"></span>
                                            <i class="bi bi-check-circle"></i>
                                            تأیید شده
                                        </span>

                                    @break

                                    @case(\App\Enums\LoanRequestStatus::REJECTED)

                                    <span class="loan-request-status rejected">
                                            <span class="loan-request-status-dot"></span>
                                            <i class="bi bi-x-circle"></i>
                                            رد شده
                                        </span>

                                    @break

                                    @case(\App\Enums\LoanRequestStatus::CANCELLED)

                                    <span class="loan-request-status cancelled">
                                            <span class="loan-request-status-dot"></span>
                                            <i class="bi bi-slash-circle"></i>
                                            لغو شده
                                        </span>

                                    @break

                                @endswitch

                            </div>

                        </div>

                    </div>


                    {{-- تاریخ درخواست --}}
                    <div class="col-12 col-md-6">

                        <div class="loan-request-info-item">

                            <span class="loan-request-info-label">
                                <i class="bi bi-calendar3"></i>
                                تاریخ درخواست
                            </span>

                            <strong class="loan-request-info-value">
                                {{ jdate($loanRequest->created_at)->format('Y/m/d') }}
                            </strong>

                        </div>

                    </div>


                    {{-- توضیحات مشتری --}}
                    <div class="col-12">

                        <div class="loan-request-note-box customer-note">

                            <span class="loan-request-info-label">
                                <i class="bi bi-chat-left-text"></i>
                                توضیحات مشتری
                            </span>

                            <div class="loan-request-note-content">
                                {{ $loanRequest->description ?? '---' }}
                            </div>

                        </div>

                    </div>


                    {{-- پیام مدیر --}}
                    @if($loanRequest->review_note)

                        <div class="col-12">

                            <div class="loan-request-note-box manager-note">

                                <span class="loan-request-info-label">
                                    <i class="bi bi-person-badge"></i>
                                    پیام مدیر
                                </span>

                                <div class="loan-request-note-content">
                                    {{ $loanRequest->review_note }}
                                </div>

                            </div>

                        </div>

                    @endif


                    {{-- تاریخ مراجعه مجدد --}}
                    @if($loanRequest->next_review_date)

                        <div class="col-12 col-md-6">

                            <div class="loan-request-info-item">

                                <span class="loan-request-info-label">
                                    <i class="bi bi-calendar-check"></i>
                                    تاریخ مراجعه مجدد
                                </span>

                                <strong class="loan-request-info-value loan-request-review-date">
                                    {{ jdate($loanRequest->next_review_date)->format('Y/m/d') }}
                                </strong>

                            </div>

                        </div>

                    @endif

                </div>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- اطلاعات وام تأیید شده --}}
        {{-- ========================================================= --}}

        @if($loanRequest->status === \App\Enums\LoanRequestStatus::APPROVED)

            <div class="loan-request-section-card approved-loan-card">

                <div class="loan-request-section-header approved-header">

                    <div class="loan-request-section-title">

                        <div class="loan-request-section-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>

                        <div>

                            <h5>
                                اطلاعات وام تأیید شده
                            </h5>

                            <span>
                                مشخصات وام تأیید شده برای این درخواست
                            </span>

                        </div>

                    </div>

                </div>


                <div class="loan-request-section-body">

                    <div class="row g-3">

                        {{-- مبلغ --}}
                        <div class="col-12 col-sm-6 col-lg-3">

                            <div class="loan-request-summary-box">

                                <span>
                                    مبلغ تأیید شده
                                </span>

                                <strong>
                                    {{ number_format($loanRequest->approved_amount) }}
                                    <small>ریال</small>
                                </strong>

                            </div>

                        </div>


                        {{-- نوع وام --}}
                        <div class="col-12 col-sm-6 col-lg-3">

                            <div class="loan-request-summary-box">

                                <span>
                                    نوع وام
                                </span>

                                <strong>
                                    {{ $loanRequest->loanType?->name ?? '-' }}
                                </strong>

                            </div>

                        </div>


                        {{-- تعداد اقساط --}}
                        <div class="col-12 col-sm-6 col-lg-3">

                            <div class="loan-request-summary-box">

                                <span>
                                    تعداد اقساط
                                </span>

                                <strong>
                                    {{ $loanRequest->approved_installment_count }}
                                </strong>

                            </div>

                        </div>


                        {{-- دوره --}}
                        <div class="col-12 col-sm-6 col-lg-3">

                            <div class="loan-request-summary-box">

                                <span>
                                    دوره بازپرداخت
                                </span>

                                <strong>
                                    {{ $loanRequest->approved_installment_interval }}
                                    ماه
                                </strong>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- بررسی مدیر --}}
        {{-- ========================================================= --}}

        @if($loanRequest->status === \App\Enums\LoanRequestStatus::PENDING)

            <div class="loan-request-review-card">

                <div class="loan-request-review-header">

                    <div class="loan-request-section-title">

                        <div class="loan-request-section-icon review-icon">
                            <i class="bi bi-clipboard-check"></i>
                        </div>

                        <div>

                            <h5>
                                بررسی درخواست
                            </h5>

                            <span>
                                تأیید یا رد درخواست و تعیین جزئیات
                            </span>

                        </div>

                    </div>

                </div>


                <div class="loan-request-review-body">

                    {{-- ================================================= --}}
                    {{-- تایید درخواست --}}
                    {{-- ================================================= --}}

                    <div class="loan-review-block approve-block">

                        <div class="loan-review-block-header">

                            <div class="loan-review-block-icon">
                                <i class="bi bi-check-lg"></i>
                            </div>

                            <div>

                                <h6>
                                    تأیید درخواست
                                </h6>

                                <span>
                                    مشخصات وام مورد تأیید را وارد کنید.
                                </span>

                            </div>

                        </div>


                        <form
                            method="POST"
                            action="{{ route('loan-requests.approve', $loanRequest) }}"
                        >

                            @csrf

                            <div class="row g-3">

                                {{-- مبلغ تایید شده --}}
                                <div class="col-12 col-md-6">

                                    <label class="form-label">
                                        مبلغ تأیید شده
                                    </label>

                                    <input
                                        type="number"
                                        name="approved_amount"
                                        class="form-control"
                                        value="{{ old('approved_amount', $loanRequest->requested_amount) }}"
                                        required
                                    >

                                </div>


                                {{-- نوع وام --}}
                                <div class="col-12 col-md-6">

                                    <label class="form-label">
                                        نوع وام
                                    </label>

                                    <select
                                        name="loan_type_id"
                                        class="form-select"
                                        required
                                    >

                                        <option value="">
                                            انتخاب کنید
                                        </option>

                                        @foreach($loanTypes as $loanType)

                                            <option
                                                value="{{ $loanType->id }}"
                                                {{ old(
                                                    'loan_type_id',
                                                    $loanRequest->loan_type_id ?? ''
                                                ) == $loanType->id ? 'selected' : '' }}
                                            >
                                                {{ $loanType->name }}
                                            </option>

                                        @endforeach

                                    </select>

                                </div>


                                {{-- تعداد اقساط --}}
                                <div class="col-12 col-md-6">

                                    <label class="form-label">
                                        تعداد اقساط
                                    </label>

                                    <input
                                        type="number"
                                        name="approved_installment_count"
                                        class="form-control"
                                        min="1"
                                        value="{{ old(
                                            'approved_installment_count',
                                            $loanRequest->approved_installment_count ?? ''
                                        ) }}"
                                        required
                                    >

                                </div>


                                {{-- دوره بازپرداخت --}}
                                <div class="col-12 col-md-6">

                                    <label class="form-label">
                                        دوره بازپرداخت
                                    </label>

                                    <select
                                        name="approved_installment_interval"
                                        class="form-select"
                                        required
                                    >

                                        <option
                                            value="1"
                                            {{ old(
                                                'approved_installment_interval',
                                                $loanRequest->approved_installment_interval ?? ''
                                            ) == 1 ? 'selected' : '' }}
                                        >
                                            ماهانه
                                        </option>

                                        <option
                                            value="2"
                                            {{ old(
                                                'approved_installment_interval',
                                                $loanRequest->approved_installment_interval ?? ''
                                            ) == 2 ? 'selected' : '' }}
                                        >
                                            هر دو ماه
                                        </option>

                                        <option
                                            value="3"
                                            {{ old(
                                                'approved_installment_interval',
                                                $loanRequest->approved_installment_interval ?? ''
                                            ) == 3 ? 'selected' : '' }}
                                        >
                                            هر سه ماه
                                        </option>

                                    </select>

                                </div>


                                {{-- پیام آماده تایید --}}
                                <div class="col-12">

                                    <label class="form-label">
                                        پیام آماده تأیید
                                    </label>

                                    <select
                                        id="approveMessage"
                                        class="form-select"
                                    >

                                        <option value="">
                                            انتخاب کنید
                                        </option>

                                        <option value="با درخواست وام شما موافقت شد. لطفاً جهت تکمیل مراحل و ارائه مدارک لازم به صندوق مراجعه نمایید.">
                                            موافقت کامل
                                        </option>

                                        <option value="با توجه به منابع صندوق، با مبلغ تایید شده موافقت شد. لطفاً جهت ادامه مراحل به صندوق مراجعه نمایید.">
                                            موافقت با مبلغ کمتر
                                        </option>

                                        <option value="با درخواست وام شما موافقت شد، اما به دلیل سابقه تاخیر در پرداخت، ارائه چک صیادی معتبر الزامی می‌باشد.">
                                            نیاز به چک صیادی
                                        </option>

                                        <option value="با درخواست وام شما موافقت شد. لطفاً ضامن‌های مورد نیاز را به صندوق معرفی نمایید.">
                                            نیاز به ضامن
                                        </option>

                                    </select>

                                </div>


                                {{-- پیام تایید --}}
                                <div class="col-12">

                                    <label class="form-label">
                                        پیام مدیر
                                    </label>

                                    <textarea
                                        name="review_note"
                                        id="review_note"
                                        class="form-control"
                                        rows="4"
                                    >{{ old(
                                        'review_note',
                                        'با درخواست وام شما موافقت شد. لطفاً جهت تکمیل مراحل و ارائه مدارک لازم به صندوق مراجعه نمایید.'
                                    ) }}</textarea>

                                </div>


                                <div class="col-12">

                                    <button
                                        type="submit"
                                        class="btn loan-request-approve-btn"
                                    >
                                        <i class="bi bi-check-circle"></i>
                                        <span>تأیید درخواست</span>
                                    </button>

                                </div>

                            </div>

                        </form>

                    </div>


                    <div class="loan-review-divider"></div>


                    {{-- ================================================= --}}
                    {{-- رد درخواست --}}
                    {{-- ================================================= --}}

                    <div class="loan-review-block reject-block">

                        <div class="loan-review-block-header">

                            <div class="loan-review-block-icon">
                                <i class="bi bi-x-lg"></i>
                            </div>

                            <div>

                                <h6>
                                    رد درخواست
                                </h6>

                                <span>
                                    دلیل رد و تاریخ مراجعه مجدد را مشخص کنید.
                                </span>

                            </div>

                        </div>


                        <form
                            method="POST"
                            action="{{ route('loan-requests.reject', $loanRequest) }}"
                        >

                            @csrf

                            <div class="row g-3">

                                {{-- تاریخ مراجعه --}}
                                <div class="col-12 col-md-6">

                                    <label class="form-label">
                                        تاریخ مراجعه مجدد
                                    </label>

                                    <input
                                        type="text"
                                        name="next_review_date"
                                        class="form-control"
                                        placeholder="1405/02/01"
                                        value="{{ old(
                                            'next_review_date',
                                            $loanRequest->next_review_date
                                                ? jdate($loanRequest->next_review_date)->format('Y/m/d')
                                                : ''
                                        ) }}"
                                    >

                                </div>


                                {{-- پیام آماده رد --}}
                                <div class="col-12 col-md-6">

                                    <label class="form-label">
                                        پیام آماده رد
                                    </label>

                                    <select
                                        id="rejectMessage"
                                        class="form-select"
                                    >

                                        <option value="">
                                            انتخاب کنید
                                        </option>

                                        <option value="درخواست وام شما بررسی شد. به دلیل وجود تاخیر در بازپرداخت وام قبلی، در حال حاضر امکان پرداخت وام وجود ندارد. لطفاً در تاریخ تعیین شده مجدداً مراجعه نمایید.">
                                            تأخیر در وام قبلی
                                        </option>

                                        <option value="درخواست وام شما بررسی شد. با توجه به منابع فعلی صندوق، امکان پرداخت وام در حال حاضر وجود ندارد. لطفاً در تاریخ تعیین شده مجدداً مراجعه نمایید.">
                                            محدودیت منابع صندوق
                                        </option>

                                        <option value="درخواست وام شما پس از بررسی مورد موافقت قرار نگرفت. لطفاً در تاریخ تعیین شده مجدداً مراجعه نمایید.">
                                            عدم موافقت عمومی
                                        </option>

                                    </select>

                                </div>


                                {{-- پیام رد --}}
                                <div class="col-12">

                                    <label class="form-label">
                                        پیام مدیر
                                    </label>

                                    <textarea
                                        name="review_note"
                                        id="reject_note"
                                        class="form-control"
                                        rows="4"
                                    >{{ old(
                                        'review_note',
                                        'درخواست وام شما پس از بررسی مورد موافقت قرار نگرفت.'
                                    ) }}</textarea>

                                </div>


                                <div class="col-12">

                                    <button
                                        type="submit"
                                        class="btn loan-request-reject-btn"
                                    >
                                        <i class="bi bi-x-circle"></i>
                                        <span>رد درخواست</span>
                                    </button>

                                </div>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- ایجاد وام پس از تایید --}}
        {{-- ========================================================= --}}

        @if(
            $loanRequest->status === \App\Enums\LoanRequestStatus::APPROVED
            && !$loanRequest->loan_id
        )

            <div class="loan-request-create-loan-card">

                <div class="loan-request-create-loan-icon">
                    <i class="bi bi-bank"></i>
                </div>

                <div class="loan-request-create-loan-content">

                    <h5>
                        درخواست تأیید شده
                    </h5>

                    <p>
                        مبلغ تأیید شده:

                        <strong>
                            {{ number_format($loanRequest->approved_amount) }}
                            ریال
                        </strong>
                    </p>

                    <a
                        href="{{ route('loans.create', [
                            'request' => $loanRequest->id
                        ]) }}"
                        class="btn loan-request-create-loan-btn"
                    >
                        <i class="bi bi-plus-circle"></i>
                        ایجاد وام
                    </a>

                </div>

            </div>

        @endif


        {{-- ========================================================= --}}
        {{-- تغییر تاریخ مراجعه درخواست رد شده --}}
        {{-- ========================================================= --}}

        @if($loanRequest->status === \App\Enums\LoanRequestStatus::REJECTED)

            <div class="loan-request-section-card review-date-card">

                <div class="loan-request-section-header">

                    <div class="loan-request-section-title">

                        <div class="loan-request-section-icon">
                            <i class="bi bi-calendar-check"></i>
                        </div>

                        <div>

                            <h5>
                                تاریخ مراجعه مجدد
                            </h5>

                            <span>
                                در صورت نیاز تاریخ مراجعه عضو را تغییر دهید.
                            </span>

                        </div>

                    </div>

                </div>


                <div class="loan-request-section-body">

                    <form
                        method="POST"
                        action="{{ route(
                            'loan-requests.update-review-date',
                            $loanRequest
                        ) }}"
                    >

                        @csrf
                        @method('PUT')

                        <div class="row g-3 align-items-end">

                            <div class="col-12 col-md-6">

                                <label class="form-label">
                                    تاریخ مراجعه مجدد (شمسی)
                                </label>

                                <input
                                    type="text"
                                    name="next_review_date"
                                    class="form-control"
                                    placeholder="1405/02/01"
                                    value="{{ old(
                                        'next_review_date',
                                        $loanRequest->next_review_date
                                            ? jdate($loanRequest->next_review_date)->format('Y/m/d')
                                            : ''
                                    ) }}"
                                >

                            </div>

                            <div class="col-12 col-md-auto">

                                <button
                                    type="submit"
                                    class="btn loan-request-save-date-btn"
                                >
                                    <i class="bi bi-calendar-check"></i>
                                    ذخیره تاریخ
                                </button>

                            </div>

                        </div>

                    </form>

                </div>

            </div>

        @endif

    </div>


    {{-- ========================================================= --}}
    {{-- JavaScript پیام‌های آماده --}}
    {{-- ========================================================= --}}

    <script>

        document
            .getElementById('approveMessage')
            ?.addEventListener('change', function () {

                document.getElementById('review_note').value = this.value;

            });


        document
            .getElementById('rejectMessage')
            ?.addEventListener('change', function () {

                document.getElementById('reject_note').value = this.value;

            });

    </script>

@endsection
