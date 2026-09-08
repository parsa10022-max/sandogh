@extends('layouts.app')

@section('title', 'برداشت‌های پس‌انداز')



@section('content')

    <div class="container-fluid savings-withdrawals-page">

        {{-- Header --}}
        <div class="savings-withdrawals-header">

            <div class="savings-withdrawals-title-wrapper">

                <div class="savings-withdrawals-title-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>

                <div>
                    <h1 class="savings-withdrawals-title">
                        برداشت‌های پس‌انداز
                    </h1>

                    <div class="savings-withdrawals-subtitle">
                        برداشت‌های پرداخت‌شده در انتظار ثبت حسابداری
                    </div>
                </div>

            </div>

            <a href="{{ route('admin.accounting.index') }}"
               class="savings-withdrawals-back-btn">

                <i class="bi bi-arrow-right"></i>
                <span>حسابداری</span>

            </a>

        </div>


        {{-- Main Card --}}
        <div class="savings-withdrawals-card">

            <div class="savings-withdrawals-card-header">

                <div class="savings-withdrawals-card-title">

                    <div class="savings-withdrawals-card-icon">
                        <i class="bi bi-list-ul"></i>
                    </div>

                    <span>برداشت‌های در انتظار ثبت</span>

                </div>

                <div class="savings-withdrawals-count">
                    {{ $withdrawals->total() }} مورد
                </div>

            </div>


            <div class="savings-withdrawals-card-body">

                <div class="savings-withdrawals-table-wrapper">

                    <table class="savings-withdrawals-table">

                        <thead>
                        <tr>

                            <th class="col-number">
                                #
                            </th>

                            <th class="col-member">
                                عضو
                            </th>

                            <th class="col-amount">
                                مبلغ
                            </th>

                            <th class="col-payer">
                                پرداخت‌کننده
                            </th>

                            <th class="col-date">
                                تاریخ پرداخت
                            </th>

                            <th class="col-action">
                                عملیات
                            </th>

                        </tr>
                        </thead>


                        <tbody>

                        @forelse($withdrawals as $withdrawal)

                            <tr>

                                {{-- Number --}}
                                <td class="savings-withdrawal-row-number">

                                    {{ $withdrawals->firstItem() + $loop->index }}

                                </td>


                                {{-- Member --}}
                                <td class="savings-withdrawal-person">

                                    <div class="savings-withdrawal-person-wrapper">

                                        <div class="savings-withdrawal-person-icon">
                                            <i class="bi bi-person"></i>
                                        </div>

                                        <div class="savings-withdrawal-person-name">

                                            {{ $withdrawal->account?->customer?->first_name }}
                                            {{ $withdrawal->account?->customer?->last_name }}

                                            @if(
                                                !$withdrawal->account?->customer?->first_name &&
                                                !$withdrawal->account?->customer?->last_name
                                            )
                                                ---
                                            @endif

                                        </div>

                                    </div>

                                </td>


                                {{-- Amount --}}
                                <td class="savings-withdrawal-amount">

                                    <span class="savings-withdrawal-amount-value">
                                        {{ number_format($withdrawal->amount ?? 0) }}
                                    </span>

                                    <span class="savings-withdrawal-amount-unit">
                                        تومان
                                    </span>

                                </td>


                                {{-- Payer --}}
                                <td class="savings-withdrawal-person">

                                    <div class="savings-withdrawal-person-wrapper">

                                        <div class="savings-withdrawal-person-icon">
                                            <i class="bi bi-person-check"></i>
                                        </div>

                                        <div class="savings-withdrawal-person-name">

                                            {{ $withdrawal->paidBy?->first_name }}
                                            {{ $withdrawal->paidBy?->last_name }}

                                            @if(
                                                !$withdrawal->paidBy?->first_name &&
                                                !$withdrawal->paidBy?->last_name
                                            )
                                                ---
                                            @endif

                                        </div>

                                    </div>

                                </td>


                                {{-- Paid date --}}
                                <td class="savings-withdrawal-date">

                                    @if($withdrawal->paid_at)

                                        <span>
                                            {{ $withdrawal->paid_at->format('Y/m/d') }}
                                        </span>

                                        <small>
                                            {{ $withdrawal->paid_at->format('H:i') }}
                                        </small>

                                    @else

                                        <span class="savings-withdrawal-muted">
                                            ---
                                        </span>

                                    @endif

                                </td>


                                {{-- Action --}}
                                <td class="savings-withdrawal-action">

                                    <form method="POST"
                                          action="{{ route('admin.accounting.confirm', [
                                              'type' => 'withdrawal',
                                              'id' => $withdrawal->id
                                          ]) }}"
                                          class="savings-withdrawal-confirm-form"
                                          onsubmit="return confirm('آیا این برداشت به عنوان ثبت‌شده در حسابداری تأیید شود؟')">

                                        @csrf

                                        <button type="submit"
                                                class="savings-withdrawal-confirm-btn">

                                            <i class="bi bi-check2-circle"></i>

                                            <span>
                                                تأیید ثبت
                                            </span>

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6"
                                    class="savings-withdrawals-empty">

                                    <div class="savings-withdrawals-empty-icon">
                                        <i class="bi bi-check-circle"></i>
                                    </div>

                                    <div class="savings-withdrawals-empty-title">
                                        برداشت در انتظاری وجود ندارد
                                    </div>

                                    <div class="savings-withdrawals-empty-text">
                                        تمام برداشت‌های پرداخت‌شده در حسابداری ثبت شده‌اند.
                                    </div>

                                </td>

                            </tr>

                        @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        {{-- Pagination --}}
        @if($withdrawals->hasPages())

            <div class="savings-withdrawals-pagination">
                {{ $withdrawals->links() }}
            </div>

        @endif

    </div>

@endsection

