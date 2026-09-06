@extends('layouts.app')

@section('title', 'مدیریت وام‌ها')

@section('content')

    <div class="container-fluid loans-page">

        {{-- =====================================================
             HEADER
        ====================================================== --}}

        <div class="loans-page-header">

            <div>

                <div class="loans-page-title">

                    <span class="loans-page-title-icon">
                        <i class="bi bi-cash-stack"></i>
                    </span>

                    <div>

                        <h1>
                            مدیریت وام‌ها
                        </h1>

                        <p>
                            مدیریت و پیگیری وام‌های صندوق
                        </p>

                    </div>

                </div>

            </div>


            <a
                href="{{ route('loans.create') }}"
                class="btn loans-add-btn"
            >

                <i class="bi bi-plus-circle"></i>

                <span>
                    ثبت وام جدید
                </span>

            </a>

        </div>


        {{-- =====================================================
             FILTER
        ====================================================== --}}

        <div class="loans-filter-card">

            <form
                method="GET"
                action="{{ route('loans.index') }}"
                class="loans-filter-form"
            >

                {{-- جستجو --}}

                <div class="loans-search-box">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="جستجو شماره وام، مشتری، کد ملی یا موبایل..."
                        value="{{ request('search') }}"
                    >

                </div>


                {{-- فیلتر وضعیت --}}

                <div class="loans-status-box">

                    <select
                        name="status"
                        class="form-select"
                    >

                        <option value="">
                            همه وام‌ها
                        </option>

                        <option
                            value="active"
                            @selected(request('status') === 'active')
                        >
                        وام‌های فعال
                        </option>

                        <option
                            value="finished"
                            @selected(request('status') === 'finished')
                        >
                        وام‌های تسویه‌شده
                        </option>

                        <option
                            value="overdue"
                            @selected(request('status') === 'overdue')
                        >
                        وام‌های معوقه
                        </option>

                    </select>

                </div>


                {{-- جستجو --}}

                <button
                    type="submit"
                    class="btn loans-search-btn"
                >

                    <i class="bi bi-search"></i>

                    <span>
                        جستجو
                    </span>

                </button>


                {{-- پاک کردن --}}

                @if(request('search') || request('status'))

                    <a
                        href="{{ route('loans.index') }}"
                        class="btn loans-clear-btn"
                    >

                        <i class="bi bi-x-circle"></i>

                        <span>
                            پاک کردن
                        </span>

                    </a>

                @endif

            </form>

        </div>


        {{-- =====================================================
             TABLE
        ====================================================== --}}

        <div class="loans-table-card">

            <div class="loans-table-wrapper">

                <table class="table loans-table align-middle">

                    <thead>

                    <tr>

                        <th>
                            #
                        </th>

                        <th>
                            شماره وام
                        </th>

                        <th>
                            مشتری
                        </th>

                        <th>
                            نوع وام
                        </th>

                        <th>
                            مبلغ وام
                        </th>

                        <th>
                            تعداد اقساط
                        </th>

                        <th>
                            وضعیت
                        </th>

                        <th class="loans-actions-column">
                            عملیات
                        </th>

                    </tr>

                    </thead>


                    <tbody>

                    @forelse($loans as $loan)

                        <tr>

                            <td data-label="#">

                                {{ $loans->firstItem() + $loop->index }}

                            </td>


                            <td data-label="شماره وام">

                                <span class="loan-number">
                                    {{ $loan->full_loan_number }}
                                </span>

                            </td>


                            <td data-label="مشتری">

                                <span class="loan-customer-name">

                                    {{ $loan->customer->first_name }}
                                    {{ $loan->customer->last_name }}

                                </span>

                            </td>


                            <td data-label="نوع وام">

                                {{ $loan->loanType->name }}

                            </td>


                            <td data-label="مبلغ وام">

                                <span class="loan-amount">

                                    {{ number_format($loan->loan_amount) }}

                                    <small>
                                        ریال
                                    </small>

                                </span>

                            </td>


                            <td data-label="تعداد اقساط">

                                {{ $loan->installment_count }}

                            </td>


                            <td data-label="وضعیت">

                                @if($loan->status === \App\Enums\LoanStatus::ACTIVE)

                                    <span class="loan-status loan-status--active">
                                        فعال
                                    </span>

                                @elseif($loan->status === \App\Enums\LoanStatus::FINISHED)

                                    <span class="loan-status loan-status--finished">
                                        تسویه‌شده
                                    </span>

                                @elseif($loan->status === \App\Enums\LoanStatus::CANCELLED)

                                    <span class="loan-status loan-status--cancelled">
                                        لغوشده
                                    </span>

                                @else

                                    <span class="loan-status">
                                        {{ $loan->status->label() }}
                                    </span>

                                @endif

                            </td>


                            <td
                                data-label="عملیات"
                                class="loans-actions-column"
                            >

                                <x-action-buttons
                                    :loan="$loan"
                                    :edit-route="route('loans.edit', $loan)"
                                    :show-route="route('loans.show', $loan)"
                                />

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="8"
                                class="loans-empty"
                            >

                                <div class="loans-empty-content">

                                    <i class="bi bi-search"></i>

                                    <span>
                                        با این فیلتر، وامی پیدا نشد.
                                    </span>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>


            {{-- Pagination --}}

            @if($loans->hasPages())

                <div class="loans-pagination">

                    {{ $loans->links() }}

                </div>

            @endif

        </div>

    </div>

@endsection

