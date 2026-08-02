@extends('layouts.app')

@section('title', 'مشاهده درخواست وام')

@section('content')

    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h4 class="mb-0">
                درخواست وام
            </h4>

            <a href="{{ route('loan-requests.index') }}"
               class="btn btn-secondary">

                بازگشت

            </a>

        </div>


        {{-- اطلاعات درخواست --}}

        <div class="card shadow-sm border-0">

            <div class="card-header bg-light">

                <strong>
                    اطلاعات درخواست
                </strong>

            </div>


            <div class="card-body">

                <div class="row">


                    <div class="col-md-6 mb-3">

                        <label class="text-muted">
                            عضو
                        </label>

                        <div class="fw-bold">
                            {{ $loanRequest->customer->full_name }}
                        </div>

                    </div>



                    <div class="col-md-6 mb-3">

                        <label class="text-muted">
                            مبلغ درخواستی
                        </label>

                        <div class="fw-bold">

                            {{ number_format($loanRequest->requested_amount) }}
                            تومان

                        </div>

                    </div>



                    <div class="col-md-6 mb-3">

                        <label class="text-muted">
                            وضعیت
                        </label>

                        <div>

                            @switch($loanRequest->status)

                                @case(\App\Enums\LoanRequestStatus::PENDING)

                                <span class="badge bg-warning text-dark">
                                    {{ $loanRequest->status->label() }}
                                </span>

                                @break


                                @case(\App\Enums\LoanRequestStatus::APPROVED)

                                <span class="badge bg-success">
                                    {{ $loanRequest->status->label() }}
                                </span>

                                @break


                                @case(\App\Enums\LoanRequestStatus::REJECTED)

                                <span class="badge bg-danger">
                                    {{ $loanRequest->status->label() }}
                                </span>

                                @break


                                @case(\App\Enums\LoanRequestStatus::CANCELLED)

                                <span class="badge bg-secondary">
                                    {{ $loanRequest->status->label() }}
                                </span>

                                @break

                            @endswitch

                        </div>

                    </div>



                    <div class="col-md-6 mb-3">

                        <label class="text-muted">
                            تاریخ درخواست
                        </label>

                        <div>

                            {{ jdate($loanRequest->created_at)->format('Y/m/d') }}

                        </div>

                    </div>



                    <div class="col-md-12 mb-3">

                        <label class="text-muted">
                            توضیحات مشتری
                        </label>

                        <div class="border rounded p-3 bg-light">

                            {{ $loanRequest->description ?? '---' }}

                        </div>

                    </div>



                    @if($loanRequest->review_note)

                        <div class="col-md-12 mb-3">

                            <label class="text-muted">
                                پیام مدیر
                            </label>

                            <div class="border rounded p-3">

                                {{ $loanRequest->review_note }}

                            </div>

                        </div>

                    @endif

                    @if($loanRequest->next_review_date)

                        <div class="col-md-6 mb-3">

                            <label class="text-muted">
                                تاریخ مراجعه مجدد
                            </label>

                            <div class="fw-bold">

                                {{ jdate($loanRequest->next_review_date)->format('Y/m/d') }}

                            </div>

                        </div>

                    @endif


                </div>

            </div>

        </div>

        @if($loanRequest->status === \App\Enums\LoanRequestStatus::APPROVED)

            <div class="card shadow-sm border-success mt-4">

                <div class="card-header bg-success text-white">

                    <strong>اطلاعات وام تایید شده</strong>

                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-3 mb-3">
                            <label class="text-muted">مبلغ تایید شده</label>
                            <div class="fw-bold">
                                {{ number_format($loanRequest->approved_amount) }} ریال
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="text-muted">نوع وام</label>
                            <div class="fw-bold">
                                {{ $loanRequest->loanType?->name ?? '-' }}
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="text-muted">تعداد اقساط</label>
                            <div class="fw-bold">
                                {{ $loanRequest->approved_installment_count }}
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="text-muted">دوره پرداخت</label>
                            <div class="fw-bold">
                                {{ $loanRequest->approved_installment_interval }} ماه
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        @endif



        {{-- بررسی مدیر --}}

        @if($loanRequest->status === \App\Enums\LoanRequestStatus::PENDING)


            <div class="card shadow-sm border-0 mt-4">


                <div class="card-header bg-light">

                    <strong>
                        بررسی درخواست
                    </strong>

                </div>


                <div class="card-body">


                    {{-- تایید --}}

                    <form method="POST"
                          action="{{ route('loan-requests.approve', $loanRequest) }}"
                          class="mb-4">

                        @csrf

                        <div class="mb-3">

                            <label class="form-label">
                                مبلغ تایید شده
                            </label>

                            <input type="number"
                                   name="approved_amount"
                                   class="form-control"
                                   value="{{ old('approved_amount', $loanRequest->requested_amount) }}"
                                   required>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                نوع وام
                            </label>

                            <select name="loan_type_id"
                                    class="form-select"
                                    required>

                                <option value="">
                                    انتخاب کنید
                                </option>

                                @foreach($loanTypes as $loanType)

                                    <option value="{{ $loanType->id }}"
                                        {{ old('loan_type_id', $loanRequest->loan_type_id ?? '') == $loanType->id ? 'selected' : '' }}>

                                        {{ $loanType->name }}

                                    </option>

                                @endforeach

                            </select>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                تعداد اقساط
                            </label>

                            <input type="number"
                                   name="approved_installment_count"
                                   class="form-control"
                                   min="1"
                                   value="{{ old('approved_installment_count', $loanRequest->approved_installment_count ?? '') }}"
                                   required>

                        </div>


                        <div class="mb-3">

                            <label class="form-label">
                                دوره بازپرداخت
                            </label>

                            <select name="approved_installment_interval"
                                    class="form-select"
                                    required>

                                <option value="1"
                                    {{ old('approved_installment_interval', $loanRequest->approved_installment_interval ?? '') == 1 ? 'selected' : '' }}>
                                    ماهانه
                                </option>

                                <option value="2"
                                    {{ old('approved_installment_interval', $loanRequest->approved_installment_interval ?? '') == 2 ? 'selected' : '' }}>
                                    هر دو ماه
                                </option>

                                <option value="3"
                                    {{ old('approved_installment_interval', $loanRequest->approved_installment_interval ?? '') == 3 ? 'selected' : '' }}>
                                    هر سه ماه
                                </option>

                            </select>

                        </div>

                        <label class="form-label">
                            پیام تایید
                        </label>
                        <div class="mb-3">

                            <label class="form-label">
                                پیام آماده
                            </label>

                            <select id="approveMessage"
                                    class="form-select">

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


                        <textarea name="review_note"
                                  id="review_note"
                                  class="form-control mb-3"
                                  rows="4">با درخواست وام شما موافقت شد. لطفاً جهت تکمیل مراحل و ارائه مدارک لازم به صندوق مراجعه نمایید.</textarea>

                        <button type="submit"
                                class="btn btn-success">

                            تایید درخواست

                        </button>


                    </form>



                    <hr>



                    {{-- رد --}}

                    <form method="POST"
                          action="{{ route('loan-requests.reject', $loanRequest) }}">


                        @csrf


                        <label class="form-label">
                            پیام رد درخواست
                        </label>
                        <div class="mb-3">

                            <label class="form-label">
                                تاریخ مراجعه مجدد
                            </label>

                            <input type="text"
                                   name="next_review_date"
                                   class="form-control"
                                   placeholder="1405/02/01"
                                   value="{{ old(
                'next_review_date',
                $loanRequest->next_review_date
                ? jdate($loanRequest->next_review_date)->format('Y/m/d')
                : ''
           ) }}">

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                پیام آماده رد
                            </label>


                            <select id="rejectMessage"
                                    class="form-select">


                                <option value="">
                                    انتخاب کنید
                                </option>


                                <option value="درخواست وام شما بررسی شد. به دلیل وجود تاخیر در بازپرداخت وام قبلی، در حال حاضر امکان پرداخت وام وجود ندارد. لطفاً در تاریخ تعیین شده مجدداً مراجعه نمایید.">
                                    تاخیر در وام قبلی
                                </option>


                                <option value="درخواست وام شما بررسی شد. با توجه به منابع فعلی صندوق، امکان پرداخت وام در حال حاضر وجود ندارد. لطفاً در تاریخ تعیین شده مجدداً مراجعه نمایید.">
                                    محدودیت منابع صندوق
                                </option>


                                <option value="درخواست وام شما پس از بررسی مورد موافقت قرار نگرفت. لطفاً در تاریخ تعیین شده مجدداً مراجعه نمایید.">
                                    عدم موافقت عمومی
                                </option>


                            </select>

                        </div>

                        <textarea name="review_note"
                                  id="reject_note"
                                  class="form-control mb-3"
                                  rows="4">درخواست وام شما پس از بررسی مورد موافقت قرار نگرفت.</textarea>

                        <button type="submit"
                                class="btn btn-danger">

                            رد درخواست

                        </button>


                    </form>




                </div>


            </div>



        @endif

        @if($loanRequest->status === \App\Enums\LoanRequestStatus::APPROVED
    && !$loanRequest->loan_id)

            <div class="card mt-4 border-success">

                <div class="card-body">

                    <h6 class="fw-bold">
                        درخواست تایید شده
                    </h6>

                    <p class="mb-3">
                        مبلغ تایید شده:
                        <strong>
                            {{ number_format($loanRequest->approved_amount) }}
                            ریال
                        </strong>
                    </p>






                    <a href="{{ route('loans.create', [
                'request' => $loanRequest->id
            ]) }}"
                       class="btn btn-success">

                        ایجاد وام

                    </a>


                </div>

            </div>

        @endif
        {{-- تاریخ مراجعه مجدد برای درخواست رد شده --}}

        @if($loanRequest->status === \App\Enums\LoanRequestStatus::REJECTED)

            <div class="card mt-4">

                <div class="card-header fw-bold">
                    تاریخ مراجعه مجدد
                </div>

                <div class="card-body">

                    <form method="POST"
                          action="{{ route('loan-requests.update-review-date', $loanRequest) }}">

                        @csrf
                        @method('PUT')

                        <div class="mb-3">

                            <label class="form-label">
                                تاریخ مراجعه مجدد (شمسی)
                            </label>

                            <input type="text"
                                   name="next_review_date"
                                   class="form-control"
                                   placeholder="1405/02/01"
                                   value="{{ old(
                            'next_review_date',
                            $loanRequest->next_review_date
                            ? jdate($loanRequest->next_review_date)->format('Y/m/d')
                            : ''
                       ) }}">

                        </div>


                        <button type="submit"
                                class="btn btn-primary">

                            ذخیره تاریخ

                        </button>

                    </form>

                </div>

            </div>

        @endif


    </div>
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
