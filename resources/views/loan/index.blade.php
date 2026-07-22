@extends('layouts.app')

@section('title', 'مدیریت وام‌ها')

@section('content')

    <div class="container-fluid">

        <div class="card shadow-sm">

            <div class="card-header">

                <div class="d-flex justify-content-between align-items-center">

                    <h5 class="mb-0">

                        <i class="bi bi-cash-stack"></i>

                        مدیریت وام‌ها

                    </h5>

                    <a
                        href="{{ route('loans.create') }}"
                        class="btn btn-success">

                        <i class="bi bi-plus-circle"></i>

                        ثبت وام جدید

                    </a>

                </div>

            </div>

            <div class="card-body">

                {{-- Search --}}

                <form method="GET" action="{{ route('loans.index') }}">

                    <div class="d-flex gap-2 mb-3">

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            style="max-width: 350px;"
                            placeholder="جستجو شماره وام، مشتری..."
                            value="{{ request('search') }}"
                        >

                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i>
                            جستجو
                        </button>

                        @if(request('search'))
                            <a href="{{ route('loans.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i>
                                پاک کردن
                            </a>
                        @endif

                    </div>

                </form>

                <div class="table-responsive">

                    <table class="table table-hover align-middle">

                        <thead class="table-light">

                        <tr>

                            <th>#</th>

                            <th>شماره وام</th>

                            <th>مشتری</th>

                            <th>نوع وام</th>

                            <th>مبلغ وام</th>

                            <th>تعداد اقساط</th>

                            <th>وضعیت</th>

                            <th class="text-center">عملیات</th>

                        </tr>

                        </thead>

                        <tbody>

                        @forelse($loans as $loan)

                            <tr>

                                <td>

                                    {{ $loop->iteration }}

                                </td>

                                <td>

                                    {{ $loan->full_loan_number }}

                                </td>

                                <td>

                                    {{ $loan->customer->first_name }}

                                    {{ $loan->customer->last_name }}

                                </td>

                                <td>

                                    {{ $loan->loanType->name }}

                                </td>

                                <td>

                                    {{ number_format($loan->loan_amount) }}
                                    <small class="text-muted">ریال</small>

                                </td>

                                <td>

                                    {{ $loan->installment_count }}

                                </td>

                                <td>


                                    {{ $loan->status->label() }}


                                </td>

                                <td class="text-center">

                                    <x-action-buttons

                                        :edit-route="route('loans.edit',$loan)"

                                        :show-route="route('loans.show',$loan)"


                                    />

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="8"
                                    class="text-center text-muted">

                                    اطلاعاتی یافت نشد.

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

                {{ $loans->links() }}

            </div>

        </div>

    </div>

@endsection
