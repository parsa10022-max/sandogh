@extends('layouts.app')

@section('title', 'مشاهده وام')

@section('content')

    {{-- خلاصه وام --}}
    @include('loan.partials.summary', ['loan' => $loan])

    <div class="container-fluid loan-show-page">

        <div class="loan-show-card">

            {{-- =========================================
                 HEADER
            ========================================== --}}

            <div class="loan-show-header">

                <div class="loan-show-title">

                    <div class="loan-show-icon">
                        <i class="bi bi-cash-coin"></i>
                    </div>

                    <div>

                        <h5 class="mb-1">
                            مشاهده اطلاعات وام
                        </h5>

                        <div class="loan-show-number">

                            <span>
                                شماره وام:
                            </span>

                            <strong class="loan-number-display">
                                {{ $loan->full_loan_number }}
                            </strong>

                        </div>

                    </div>

                </div>

                <span class="loan-status-badge">
                    {{ $loan->status->label() }}
                </span>

            </div>


            {{-- =========================================
                 BODY
            ========================================== --}}

            <div class="loan-show-body">

                {{-- اطلاعات وام --}}
                <section class="loan-show-section">

                    @include('loan.partials.loan-info')

                </section>


                {{-- اطلاعات درخواست --}}
                <section class="loan-show-section">

                    @include('loan.partials.request-info')

                </section>


                {{-- ضامنین --}}
                <section class="loan-show-section">

                    @include('loan.partials.guarantors-show')

                </section>


                {{-- جداکننده --}}
                <div class="loan-show-divider"></div>


                {{-- برنامه اقساط --}}
                <section class="loan-show-section">

                    @include('loan.partials.installments')

                </section>


                {{-- جداکننده --}}
                <div class="loan-show-divider"></div>


                {{-- اطلاعات سیستم --}}
                <section class="loan-show-section">

                    @include('loan.partials.system-info')

                </section>

            </div>


            {{-- =========================================
                 FOOTER / ACTIONS
            ========================================== --}}

            <div class="loan-show-footer">

                <a
                    href="{{ route('loans.index') }}"
                    class="loan-show-action loan-show-action-back">

                    <span class="loan-show-action-icon">
                        <i class="bi bi-arrow-right"></i>
                    </span>

                    <span>
                        بازگشت
                    </span>

                </a>


                <div class="loan-show-actions">

                    <a
                        href="{{ route('loans.edit', $loan) }}"
                        class="loan-show-action loan-show-action-edit">

                        <span class="loan-show-action-icon">
                            <i class="bi bi-pencil-square"></i>
                        </span>

                        <span>
                            ویرایش
                        </span>

                    </a>


                    <button
                        type="button"
                        onclick="window.print()"
                        class="loan-show-action loan-show-action-print">

                        <span class="loan-show-action-icon">
                            <i class="bi bi-printer"></i>
                        </span>

                        <span>
                            چاپ
                        </span>

                    </button>

                </div>

            </div>

        </div>

    </div>

@endsection
