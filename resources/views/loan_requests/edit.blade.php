@extends('layouts.app')

@section('title', 'ویرایش درخواست وام')

@section('content')

    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h4 class="mb-0">
                ویرایش درخواست وام
            </h4>

            <div class="d-flex gap-2">

                <a href="{{ route('loan-requests.show', $loanRequest) }}"
                   class="btn btn-secondary">

                    <i class="bi bi-arrow-right"></i>

                    بازگشت

                </a>

            </div>

        </div>


        {{-- پیام خطاهای Validation --}}

        @if($errors->any())

            <div class="alert alert-danger">

                <ul class="mb-0">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        <form method="POST"
              action="{{ route('loan-requests.update', $loanRequest) }}">

            @csrf

            @method('PUT')


            {{-- اطلاعات اصلی درخواست --}}

            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header bg-light">

                    <strong>
                        اطلاعات درخواست
                    </strong>

                </div>

                <div class="card-body">

                    <div class="row">


                        {{-- مشتری --}}

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                عضو
                            </label>

                            <input type="text"
                                   class="form-control"
                                   value="{{ $loanRequest->customer->full_name }}"
                                   disabled>

                        </div>


                        {{-- مبلغ درخواستی --}}

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                مبلغ درخواستی
                            </label>

                            <input type="text"
                                   class="form-control"
                                   value="{{ number_format($loanRequest->requested_amount) }} ریال"
                                   disabled>

                        </div>


                        {{-- توضیحات مشتری --}}

                        <div class="col-md-12 mb-3">

                            <label class="form-label">
                                توضیحات مشتری
                            </label>

                            <textarea class="form-control"
                                      rows="3"
                                      disabled>{{ $loanRequest->description }}</textarea>

                        </div>

                    </div>

                </div>

            </div>


            {{-- وضعیت بررسی --}}

            <div class="card shadow-sm border-0 mb-4">

                <div class="card-header bg-light">

                    <strong>
                        اطلاعات بررسی مدیر
                    </strong>

                </div>

                <div class="card-body">

                    <div class="row">


                        {{-- وضعیت --}}

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                وضعیت
                            </label>

                            <select name="status"
                                    id="status"
                                    class="form-select"
                                    required>

                                <option value="pending"
                                    {{ old('status', $loanRequest->status->value) === 'pending' ? 'selected' : '' }}>

                                    در حال بررسی

                                </option>

                                <option value="approved"
                                    {{ old('status', $loanRequest->status->value) === 'approved' ? 'selected' : '' }}>

                                    تایید شده

                                </option>

                                <option value="rejected"
                                    {{ old('status', $loanRequest->status->value) === 'rejected' ? 'selected' : '' }}>

                                    رد شده

                                </option>

                            </select>

                        </div>


                        {{-- مبلغ تایید شده --}}

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                مبلغ تایید شده
                            </label>

                            <input type="number"
                                   name="approved_amount"
                                   class="form-control"
                                   min="0"
                                   value="{{ old('approved_amount', $loanRequest->approved_amount) }}">

                        </div>


                        {{-- نوع وام --}}

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                نوع وام
                            </label>

                            <select name="loan_type_id"
                                    class="form-select">

                                <option value="">
                                    انتخاب کنید
                                </option>

                                @foreach($loanTypes as $loanType)

                                    <option value="{{ $loanType->id }}"
                                        {{ old(
                                            'loan_type_id',
                                            $loanRequest->loan_type_id
                                        ) == $loanType->id ? 'selected' : '' }}>

                                        {{ $loanType->name }}

                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- تعداد اقساط --}}

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                تعداد اقساط
                            </label>

                            <input type="number"
                                   name="approved_installment_count"
                                   class="form-control"
                                   min="1"
                                   value="{{ old(
                                       'approved_installment_count',
                                       $loanRequest->approved_installment_count
                                   ) }}">

                        </div>


                        {{-- دوره بازپرداخت --}}

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                دوره بازپرداخت
                            </label>

                            <select name="approved_installment_interval"
                                    class="form-select">

                                <option value="">
                                    انتخاب کنید
                                </option>

                                <option value="1"
                                    {{ old(
                                        'approved_installment_interval',
                                        $loanRequest->approved_installment_interval
                                    ) == 1 ? 'selected' : '' }}>

                                    ماهانه

                                </option>

                                <option value="2"
                                    {{ old(
                                        'approved_installment_interval',
                                        $loanRequest->approved_installment_interval
                                    ) == 2 ? 'selected' : '' }}>

                                    هر دو ماه

                                </option>

                                <option value="3"
                                    {{ old(
                                        'approved_installment_interval',
                                        $loanRequest->approved_installment_interval
                                    ) == 3 ? 'selected' : '' }}>

                                    هر سه ماه

                                </option>

                            </select>

                        </div>


                        {{-- تاریخ مراجعه مجدد --}}

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                تاریخ مراجعه مجدد
                            </label>

                            <input type="text"
                                   name="next_review_date"
                                   class="form-control"
                                   placeholder="1405/10/12"
                                   value="{{ old(
                                       'next_review_date',
                                       $loanRequest->next_review_date
                                           ? jdate($loanRequest->next_review_date)->format('Y/m/d')
                                           : ''
                                   ) }}">

                            <div class="form-text">

                                تاریخ را به صورت شمسی وارد کنید.

                            </div>

                        </div>


                        {{-- پیام مدیر --}}

                        <div class="col-md-12 mb-3">

                            <label class="form-label">
                                پیام مدیر
                            </label>

                            <textarea name="review_note"
                                      class="form-control"
                                      rows="5">{{ old(
                                          'review_note',
                                          $loanRequest->review_note
                                      ) }}</textarea>

                        </div>

                    </div>

                </div>

            </div>


            {{-- هشدار --}}

            <div class="alert alert-warning">

                <i class="bi bi-exclamation-triangle"></i>

                توجه:
                تغییر وضعیت یا اطلاعات تایید درخواست می‌تواند
                روی روند ایجاد وام تأثیر بگذارد.

            </div>


            {{-- دکمه‌ها --}}

            <div class="d-flex justify-content-between">

                <a href="{{ route('loan-requests.show', $loanRequest) }}"
                   class="btn btn-secondary">

                    انصراف

                </a>


                <button type="submit"
                        class="btn btn-primary">

                    <i class="bi bi-check-lg"></i>

                    ذخیره تغییرات

                </button>

            </div>

        </form>

    </div>

@endsection
