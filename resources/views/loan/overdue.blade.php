@extends('layouts.app')

@section('title', 'وام‌های معوق')

@section('content')

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">

            <h5 class="mb-0 fw-bold">

                وام‌های معوق

            </h5>

        </div>

        <div class="table-responsive">
            <div class="row g-3 mb-4">

                <div class="col-md-4">

                    <div class="card border-0 shadow-sm">

                        <div class="card-body text-center">

                            <div class="text-muted">

                                وام‌های معوق

                            </div>

                            <h3 class="fw-bold text-danger">

                                {{ $statistics['loan_count'] }}

                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="card border-0 shadow-sm">

                        <div class="card-body text-center">

                            <div class="text-muted">

                                اقساط معوق

                            </div>

                            <h3 class="fw-bold text-warning">

                                {{ $statistics['installment_count'] }}

                            </h3>

                        </div>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="card border-0 shadow-sm">

                        <div class="card-body text-center">

                            <div class="text-muted">

                                مبلغ کل معوقات

                            </div>

                            <h3 class="fw-bold text-success">

                                {{ number_format($statistics['amount']) }}

                            </h3>

                        </div>

                    </div>

                </div>

            </div>
            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                <tr>

                    <th>شماره وام</th>

                    <th>عضو</th>

                    <th>نوع وام</th>

                    <th>اقساط معوق</th>

                    <th>مبلغ معوق</th>

                    <th>قدیمی‌ترین سررسید</th>

                    <th>بیشترین تأخیر</th>

                    <th class="text-center">عملیات</th>

                </tr>

                </thead>

                <tbody>

                @forelse($loans as $loan)

                    @php

                        $amount = $loan->installments->sum('amount');

                        $oldest = $loan->installments->first();

                        $days = $oldest?->overdue_days ?? 0;

                        $amountColor = match (true) {

                            $amount >= 40000000 => 'danger',

                            $amount >= 10000000 => 'warning',

                            default => 'success',

                        };

                    @endphp

                    <tr>

                        <td>
                            {{ $loan->loan_number }}

                            -
                            {{ $loan->loanType->prefix }}
                        </td>

                        <td>

                            {{ $loan->customer->full_name }}

                        </td>

                        <td>

                            {{ $loan->loanType->name }}

                        </td>

                        <td>

                        <span class="badge bg-danger">

                            {{ $loan->overdue_count }}

                        </span>

                        </td>

                        <td>

    <span class="badge bg-{{ $amountColor }}">

        {{ number_format($amount) }}

    </span>

                        </td>

                        <td>

                            {{ $oldest?->due_date_jalali ?? '-' }}

                        </td>

                        <td>

                            @php

                                $delayColor = match (true) {

                                    $days >= 90 => 'danger',

                                    $days >= 30 => 'warning',

                                    default => 'secondary',

                                };

                            @endphp

                            <span class="badge bg-{{ $delayColor }}">

    {{ $days }}

    روز

</span>

                        </td>

                        <td class="text-center">

                            <a href="{{ route('loans.show', $loan) }}"
                               class="btn btn-sm btn-outline-primary">

                                <i class="bi bi-eye"></i>

                                مشاهده

                            </a>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="8" class="text-center py-4 text-muted">

                            وام معوقی وجود ندارد.

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

@endsection
