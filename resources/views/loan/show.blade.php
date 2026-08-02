@extends('layouts.app')

@section('title', 'مشاهده وام')

@section('content')

    @include('loan.partials.summary', ['loan' => $loan])


    <div class="container-fluid">

        <div class="card shadow-sm">

            <div class="card-header d-flex justify-content-between align-items-center">

                <div>

                    <h5 class="mb-1">

                        مشاهده اطلاعات وام

                    </h5>

                    <small class="text-muted">

                        شماره وام :

                        <strong class="loan-number-display">

                            {{ $loan->full_loan_number }}

                        </strong>

                    </small>

                </div>

                <span class="badge bg-success">

                    {{ $loan->status->label() }}

                </span>

            </div>

            <div class="card-body">

                {{-- اطلاعات وام --}}
                @include('loan.partials.loan-info')
                @include('loan.partials.request-info')
                @include('loan.partials.guarantors-show')

                <hr class="my-4">

                {{-- برنامه اقساط --}}
                @include('loan.partials.installments')

                <hr class="my-4">

                {{-- اطلاعات سیستم --}}
                @include('loan.partials.system-info')


            </div>

            <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">

                <a
                    href="{{ route('loans.index') }}"
                    class="btn btn-outline-secondary">

                    <i class="bi bi-arrow-right"></i>

                    بازگشت

                </a>

                <div class="d-flex gap-2">

                    <a
                        href="{{ route('loans.edit', $loan) }}"
                        class="btn btn-warning">

                        <i class="bi bi-pencil-square"></i>

                        ویرایش

                    </a>

                    <button
                        type="button"
                        onclick="window.print()"
                        class="btn btn-primary">

                        <i class="bi bi-printer"></i>

                        چاپ

                    </button>

                </div>

            </div>

        </div>

    </div>

@endsection
