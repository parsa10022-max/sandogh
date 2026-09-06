@extends('layouts.app')

@section('title', 'ویرایش درخواست وام')

@section('content')

    <div class="container-fluid loan-request-edit-page">

        {{-- Header --}}
        <x-page-header title="ویرایش درخواست وام">
            <a href="{{ route('loan-requests.show', $loanRequest) }}"
               class="btn loan-request-back-btn">

                <i class="bi bi-arrow-right"></i>

                <span>بازگشت به درخواست</span>

            </a>
        </x-page-header>


        {{-- Validation Errors --}}
        @if($errors->any())

            <div class="loan-request-validation-card">

                <div class="loan-request-validation-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>

                <div class="loan-request-validation-content">

                    <strong>خطا در اطلاعات وارد شده</strong>

                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>

                </div>

            </div>

        @endif


        <form method="POST"
              action="{{ route('loan-requests.update', $loanRequest) }}">

            @csrf
            @method('PUT')


            {{-- اطلاعات اصلی درخواست --}}
            <div class="loan-request-edit-card">

                <div class="loan-request-edit-card-header">

                    <div class="loan-request-edit-section-icon loan-icon-purple">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>

                    <div>
                        <h5>اطلاعات درخواست</h5>
                        <p>اطلاعات اصلی درخواست وام</p>
                    </div>

                </div>


                <div class="loan-request-edit-card-body">

                    <div class="row g-3">

                        {{-- مشتری --}}
                        <div class="col-12 col-md-6">

                            <div class="loan-request-readonly-field">

                                <label class="form-label">
                                    عضو
                                </label>

                                <div class="loan-request-readonly-input">

                                    <i class="bi bi-person"></i>

                                    <span>
                                        {{ $loanRequest->customer->full_name }}
                                    </span>

                                </div>

                            </div>

                        </div>


                        {{-- مبلغ درخواستی --}}
                        <div class="col-12 col-md-6">

                            <div class="loan-request-readonly-field">

                                <label class="form-label">
                                    مبلغ درخواستی
                                </label>

                                <div class="loan-request-readonly-input">

                                    <i class="bi bi-cash-stack"></i>

                                    <span>
                                        {{ number_format($loanRequest->requested_amount) }}
                                        ریال
                                    </span>

                                </div>

                            </div>

                        </div>


                        {{-- توضیحات مشتری --}}
                        <div class="col-12">

                            <div class="loan-request-readonly-field">

                                <label class="form-label">
                                    توضیحات مشتری
                                </label>

                                <textarea class="form-control loan-request-readonly-textarea"
                                          rows="3"
                                          disabled>{{ $loanRequest->description }}</textarea>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- اطلاعات بررسی مدیر --}}
            <div class="loan-request-edit-card">

                <div class="loan-request-edit-card-header">

                    <div class="loan-request-edit-section-icon loan-icon-blue">
                        <i class="bi bi-clipboard-check"></i>
                    </div>

                    <div>
                        <h5>اطلاعات بررسی مدیر</h5>
                        <p>وضعیت و اطلاعات تأیید درخواست را مدیریت کنید.</p>
                    </div>

                </div>


                <div class="loan-request-edit-card-body">

                    <div class="row g-3">

                        {{-- وضعیت --}}
                        <div class="col-12 col-md-6">

                            <label for="status"
                                   class="form-label">

                                وضعیت
                                <span class="text-danger">*</span>

                            </label>

                            <select name="status"
                                    id="status"
                                    class="form-select @error('status') is-invalid @enderror"
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

                            @error('status')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>


                        {{-- مبلغ تایید شده --}}
                        <div class="col-12 col-md-6">

                            <label for="approved_amount"
                                   class="form-label">

                                مبلغ تایید شده

                            </label>

                            <div class="loan-request-money-input">

                                <input type="number"
                                       id="approved_amount"
                                       name="approved_amount"
                                       min="0"
                                       inputmode="numeric"
                                       class="form-control @error('approved_amount') is-invalid @enderror"
                                       value="{{ old('approved_amount', $loanRequest->approved_amount) }}"
                                       placeholder="مثلاً 10000000">

                                <span>ریال</span>

                            </div>

                            @error('approved_amount')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>


                        {{-- نوع وام --}}
                        <div class="col-12 col-md-6">

                            <label for="loan_type_id"
                                   class="form-label">

                                نوع وام

                            </label>

                            <select name="loan_type_id"
                                    id="loan_type_id"
                                    class="form-select @error('loan_type_id') is-invalid @enderror">

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

                            @error('loan_type_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>


                        {{-- تعداد اقساط --}}
                        <div class="col-12 col-md-6">

                            <label for="approved_installment_count"
                                   class="form-label">

                                تعداد اقساط

                            </label>

                            <input type="number"
                                   id="approved_installment_count"
                                   name="approved_installment_count"
                                   min="1"
                                   class="form-control @error('approved_installment_count') is-invalid @enderror"
                                   value="{{ old(
                                       'approved_installment_count',
                                       $loanRequest->approved_installment_count
                                   ) }}"
                                   placeholder="مثلاً 10">

                            @error('approved_installment_count')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>


                        {{-- دوره بازپرداخت --}}
                        <div class="col-12 col-md-6">

                            <label for="approved_installment_interval"
                                   class="form-label">

                                دوره بازپرداخت

                            </label>

                            <select name="approved_installment_interval"
                                    id="approved_installment_interval"
                                    class="form-select @error('approved_installment_interval') is-invalid @enderror">

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

                            @error('approved_installment_interval')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>


                        {{-- تاریخ مراجعه مجدد --}}
                        <div class="col-12 col-md-6">

                            <label for="next_review_date"
                                   class="form-label">

                                تاریخ مراجعه مجدد

                            </label>

                            <input type="text"
                                   id="next_review_date"
                                   name="next_review_date"
                                   class="form-control @error('next_review_date') is-invalid @enderror"
                                   placeholder="۱۴۰۵/۱۰/۱۲"
                                   value="{{ old(
                                       'next_review_date',
                                       $loanRequest->next_review_date
                                           ? jdate($loanRequest->next_review_date)->format('Y/m/d')
                                           : ''
                                   ) }}">

                            <div class="form-text">
                                تاریخ را به صورت شمسی وارد کنید.
                            </div>

                            @error('next_review_date')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>


                        {{-- پیام مدیر --}}
                        <div class="col-12">

                            <label for="review_note"
                                   class="form-label">

                                پیام مدیر

                            </label>

                            <textarea name="review_note"
                                      id="review_note"
                                      rows="5"
                                      class="form-control @error('review_note') is-invalid @enderror"
                                      placeholder="پیام یا توضیحات مربوط به بررسی درخواست را وارد کنید...">{{ old(
                                          'review_note',
                                          $loanRequest->review_note
                                      ) }}</textarea>

                            @error('review_note')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                            @enderror

                        </div>

                    </div>

                </div>

            </div>


            {{-- هشدار --}}
            <div class="loan-request-edit-warning">

                <div class="loan-request-edit-warning-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>

                <div>

                    <strong>توجه</strong>

                    <p>
                        تغییر وضعیت یا اطلاعات تأیید درخواست می‌تواند
                        روی روند ایجاد وام تأثیر بگذارد.
                    </p>

                </div>

            </div>


            {{-- Actions --}}
            <div class="loan-request-edit-actions">

                <a href="{{ route('loan-requests.show', $loanRequest) }}"
                   class="btn loan-request-edit-cancel-btn">

                    <i class="bi bi-x-lg"></i>

                    <span>انصراف</span>

                </a>


                <button type="submit"
                        class="btn loan-request-edit-save-btn">

                    <i class="bi bi-check-lg"></i>

                    <span>ذخیره تغییرات</span>

                </button>

            </div>

        </form>

    </div>

@endsection
