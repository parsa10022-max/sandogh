@extends('layouts.app')

@section('title', 'درخواست‌های وام')

@section('content')

    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">درخواست‌های وام</h4>

            <a href="{{ route('loan-requests.create') }}" class="btn btn-primary">
                ثبت درخواست
            </a>
        </div>

        <div class="card shadow-sm border-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>عضو</th>
                        <th>مبلغ درخواستی</th>
                        <th>مبلغ تایید شده</th>
                        <th>وضعیت</th>
                        <th>
                            مراجعه مجدد
                        </th>
                        <th>
                            شماره وام
                        </th>
                        <th>
                            وام
                        </th>
                        <th>تاریخ درخواست</th>
                        <th width="120">عملیات</th>
                    </tr>
                    </thead>

                    <tbody>

                    @forelse($loanRequests as $loanRequest)

                        <tr>

                            <td>{{ $loanRequest->id }}</td>

                            <td>{{ $loanRequest->customer->full_name }}</td>

                            <td>{{ number_format($loanRequest->requested_amount) }}</td>
                            <td>

                                @if($loanRequest->approved_amount)

                                    {{ number_format($loanRequest->approved_amount) }}

                                    ریال

                                @else

                                    ---

                                @endif

                            </td>

                            <td>

                                @switch($loanRequest->status)

                                    @case(\App\Enums\LoanRequestStatus::PENDING)

                                    <span class="badge bg-warning text-dark">
                <i class="bi bi-hourglass-split"></i>
                در حال بررسی
            </span>

                                    @break


                                    @case(\App\Enums\LoanRequestStatus::APPROVED)

                                    <span class="badge bg-success">
                <i class="bi bi-check-circle"></i>
                تایید شده
            </span>

                                    @break


                                    @case(\App\Enums\LoanRequestStatus::REJECTED)

                                    <span class="badge bg-danger">
                <i class="bi bi-x-circle"></i>
                رد شده
            </span>

                                    @break


                                    @case(\App\Enums\LoanRequestStatus::CANCELLED)

                                    <span class="badge bg-secondary">
                <i class="bi bi-slash-circle"></i>
                لغو شده
            </span>

                                    @break

                                @endswitch

                            </td>



                            <td>

                                @if($loanRequest->next_review_date)

                                    {{ jdate($loanRequest->next_review_date)->format('Y/m/d') }}

                                @else

                                    ---

                                @endif

                            </td>

                            <td>

                                @if($loanRequest->loan)

                                    {{ $loanRequest->loan->full_loan_number }}

                                @else

                                    <span class="text-muted">
            -
        </span>

                                @endif

                            </td>
                            <td>

                                @if($loanRequest->loan_id)

                                    <a href="{{ route('loans.show',$loanRequest->loan_id) }}"
                                       class="btn btn-sm btn-primary">

                                        مشاهده وام

                                    </a>

                                @else

                                    <span class="text-muted">
            هنوز ایجاد نشده
        </span>

                                @endif

                            </td>

                            <td>{{ jdate($loanRequest->created_at)->format('Y/m/d') }}</td>



                            <td>

                                <a href="{{ route('loan-requests.show', $loanRequest) }}"
                                   class="btn btn-sm btn-outline-primary">

                                    مشاهده

                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6" class="text-center py-4">

                                درخواستی ثبت نشده است.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        <div class="mt-3">

            {{ $loanRequests->links() }}

        </div>

    </div>

@endsection
