@extends('layouts.app')

@section('title', 'درخواست‌های وام')

@section('content')

    <div class="container-fluid loan-requests-page">

        {{-- Page Header --}}
        <x-page-header title="درخواست‌های وام">

            <a
                href="{{ route('loan-requests.create') }}"
                class="btn loan-request-create-btn"
            >
                <i class="bi bi-plus-circle"></i>
                <span>ثبت درخواست وام</span>
            </a>

        </x-page-header>


        {{-- Table Card --}}
        <div class="loan-requests-card">

            {{-- Card Header --}}
            <div class="loan-requests-card-header">

                <div class="loan-requests-title-wrapper">

                    <div class="loan-requests-icon">
                        <i class="bi bi-file-earmark-text"></i>
                    </div>

                    <div>
                        <h5 class="loan-requests-title">
                            درخواست‌های وام
                        </h5>

                        <span class="loan-requests-subtitle">
                            فهرست درخواست‌های ثبت‌شده و وضعیت بررسی آن‌ها
                        </span>
                    </div>

                </div>

                @if($loanRequests->total() > 0)

                    <div class="loan-requests-count">
                        {{ $loanRequests->total() }} درخواست
                    </div>

                @endif

            </div>


            {{-- Table --}}
            <div class="table-responsive loan-requests-table-wrapper">

                <table class="table align-middle mb-0 loan-requests-table">

                    <thead>

                    <tr>

                        <th>#</th>

                        <th>عضو</th>

                        <th>مبلغ درخواستی</th>

                        <th>مبلغ تأیید شده</th>

                        <th>وضعیت</th>

                        <th>مراجعه مجدد</th>

                        <th>شماره وام</th>

                        <th>وام</th>

                        <th>تاریخ درخواست</th>

                        <th class="text-center">عملیات</th>

                    </tr>

                    </thead>

                    <tbody>

                    @forelse($loanRequests as $loanRequest)

                        <tr>

                            {{-- ID --}}
                            <td>
                                <span class="loan-request-id">
                                    {{ $loanRequest->id }}
                                </span>
                            </td>


                            {{-- Customer --}}
                            <td>

                                <div class="loan-request-customer">

                                    <div class="loan-request-customer-icon">
                                        <i class="bi bi-person"></i>
                                    </div>

                                    <span>
                                        {{ $loanRequest->customer->full_name }}
                                    </span>

                                </div>

                            </td>


                            {{-- Requested Amount --}}
                            <td>

                                <span class="loan-request-amount">
                                    {{ number_format($loanRequest->requested_amount) }}
                                </span>

                                <small class="loan-request-currency">
                                    ریال
                                </small>

                            </td>


                            {{-- Approved Amount --}}
                            <td>

                                @if($loanRequest->approved_amount)

                                    <span class="loan-request-amount approved">
                                        {{ number_format($loanRequest->approved_amount) }}
                                    </span>

                                    <small class="loan-request-currency">
                                        ریال
                                    </small>

                                @else

                                    <span class="loan-request-empty">
                                        ---
                                    </span>

                                @endif

                            </td>


                            {{-- Status --}}
                            <td>

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

                            </td>


                            {{-- Next Review --}}
                            <td>

                                @if($loanRequest->next_review_date)

                                    <span class="loan-request-date">
                                        {{ jdate($loanRequest->next_review_date)->format('Y/m/d') }}
                                    </span>

                                @else

                                    <span class="loan-request-empty">
                                        ---
                                    </span>

                                @endif

                            </td>


                            {{-- Loan Number --}}
                            <td>

                                @if($loanRequest->loan)

                                    <span class="loan-request-number">
                                        {{ $loanRequest->loan->full_loan_number }}
                                    </span>

                                @else

                                    <span class="loan-request-empty">
                                        -
                                    </span>

                                @endif

                            </td>


                            {{-- Loan --}}
                            <td>

                                @if($loanRequest->loan_id)

                                    <a
                                        href="{{ route('loans.show', $loanRequest->loan_id) }}"
                                        class="btn btn-sm loan-request-loan-btn"
                                    >
                                        <i class="bi bi-eye"></i>
                                        مشاهده وام
                                    </a>

                                @else

                                    <span class="loan-request-not-created">
                                        هنوز ایجاد نشده
                                    </span>

                                @endif

                            </td>


                            {{-- Created At --}}
                            <td>

                                <span class="loan-request-date">
                                    {{ jdate($loanRequest->created_at)->format('Y/m/d') }}
                                </span>

                            </td>


                            {{-- Actions --}}
                            <td class="text-center">

                                <a
                                    href="{{ route('loan-requests.show', $loanRequest) }}"
                                    class="btn btn-sm loan-request-view-btn"
                                >
                                    <i class="bi bi-eye"></i>
                                    مشاهده
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="10">

                                <div class="loan-requests-empty">

                                    <div class="loan-requests-empty-icon">
                                        <i class="bi bi-file-earmark-x"></i>
                                    </div>

                                    <h6>
                                        درخواستی ثبت نشده است
                                    </h6>

                                    <p>
                                        هنوز هیچ درخواست وامی در سیستم ثبت نشده است.
                                    </p>

                                    <a
                                        href="{{ route('loan-requests.create') }}"
                                        class="btn btn-primary"
                                    >
                                        <i class="bi bi-plus-circle"></i>
                                        ثبت اولین درخواست
                                    </a>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- Pagination --}}
        @if($loanRequests->hasPages())

            <div class="loan-requests-pagination">
                {{ $loanRequests->links() }}
            </div>

        @endif

    </div>

@endsection
