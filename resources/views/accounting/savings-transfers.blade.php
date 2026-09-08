@extends('layouts.app')

@section('title', 'واریزهای پس‌انداز')

@section('content')

    @push('styles')
        @vite('resources/css/admin/accounting/savings-transfers.css')
    @endpush

    <div class="container-fluid savings-transfers-page">

        {{-- Header --}}
        <div class="savings-transfers-header">

            <div class="savings-transfers-title-wrapper">

                <div class="savings-transfers-title-icon">
                    <i class="bi bi-wallet2"></i>
                </div>

                <div>
                    <h4 class="savings-transfers-title">
                        واریزهای پس‌انداز
                    </h4>

                    <div class="savings-transfers-subtitle">
                        واریزهای پرداخت‌شده در انتظار ثبت حسابداری
                    </div>
                </div>

            </div>

            <a href="{{ route('admin.accounting.index') }}"
               class="savings-transfers-back-btn">

                <i class="bi bi-arrow-right"></i>

                <span>حسابداری</span>

            </a>

        </div>


        {{-- Main Card --}}
        <div class="savings-transfers-card">

            <div class="savings-transfers-card-header">

                <div class="savings-transfers-card-title">

                    <div class="savings-transfers-card-icon">
                        <i class="bi bi-list-check"></i>
                    </div>

                    <div>
                        <div>
                            واریزهای در انتظار ثبت
                        </div>

                        <small>
                            بررسی و تأیید واریزهای پس‌انداز
                        </small>
                    </div>

                </div>

                <div class="savings-transfers-count">
                    {{ $transfers->total() }} مورد
                </div>

            </div>


            <div class="savings-transfers-card-body">

                <div class="table-responsive savings-transfers-table-wrapper">

                    <table class="table align-middle savings-transfers-table">

                        <thead>

                        <tr>

                            <th>#</th>

                            <th>پرداخت‌کننده</th>

                            <th>صاحب حساب</th>

                            <th>مبلغ</th>

                            <th>تاریخ پرداخت</th>

                            <th>عملیات</th>

                        </tr>

                        </thead>


                        <tbody>

                        @forelse($transfers as $transfer)

                            <tr>

                                {{-- شماره --}}
                                <td>

                                    <span class="savings-transfer-row-number">
                                        {{ $transfers->firstItem() + $loop->index }}
                                    </span>

                                </td>


                                {{-- پرداخت‌کننده --}}
                                <td>

                                    @if($transfer->sender)

                                        <div class="savings-transfer-person">

                                            <div class="savings-transfer-person-icon">
                                                <i class="bi bi-person"></i>
                                            </div>

                                            <div class="savings-transfer-person-name">

                                                {{ $transfer->sender->first_name }}
                                                {{ $transfer->sender->last_name }}

                                            </div>

                                        </div>

                                    @else

                                        <span class="savings-transfer-muted">
                                            ---
                                        </span>

                                    @endif

                                </td>


                                {{-- صاحب حساب --}}
                                <td>

                                    @if($transfer->receiver)

                                        <div class="savings-transfer-person">

                                            <div class="savings-transfer-person-icon">
                                                <i class="bi bi-person-vcard"></i>
                                            </div>

                                            <div class="savings-transfer-person-name">

                                                {{ $transfer->receiver->first_name }}
                                                {{ $transfer->receiver->last_name }}

                                            </div>

                                        </div>

                                    @else

                                        <span class="savings-transfer-muted">
                                            ---
                                        </span>

                                    @endif

                                </td>


                                {{-- مبلغ --}}
                                <td>

                                    <div class="savings-transfer-amount">

                                        <strong>
                                            {{ number_format($transfer->amount ?? 0) }}
                                        </strong>

                                        <small>
                                            تومان
                                        </small>

                                    </div>

                                </td>


                                {{-- تاریخ --}}
                                <td>

                                    @if($transfer->paid_at)

                                        <div class="savings-transfer-date">

                                            <span>
                                                {{ $transfer->paid_at->format('Y/m/d') }}
                                            </span>

                                            <small>
                                                {{ $transfer->paid_at->format('H:i') }}
                                            </small>

                                        </div>

                                    @else

                                        <span class="savings-transfer-muted">
                                            ---
                                        </span>

                                    @endif

                                </td>


                                {{-- عملیات --}}
                                <td class="text-center">

                                    <form method="POST"
                                          action="{{ route(
                                              'admin.accounting.confirm',
                                              [
                                                  'type' => 'savings-transfer',
                                                  'id' => $transfer->id
                                              ]
                                          ) }}"
                                          class="savings-transfer-confirm-form"
                                          onsubmit="return confirm('آیا این عملیات به عنوان ثبت‌شده در حسابداری تأیید شود؟')">

                                        @csrf

                                        <button type="submit"
                                                class="savings-transfer-confirm-btn">

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

                                <td colspan="6">

                                    <div class="savings-transfers-empty">

                                        <div class="savings-transfers-empty-icon">
                                            <i class="bi bi-check-circle"></i>
                                        </div>

                                        <h6>
                                            واریز در انتظاری وجود ندارد
                                        </h6>

                                        <p>
                                            تمام واریزهای پس‌انداز در حسابداری ثبت شده‌اند.
                                        </p>

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
        @if($transfers->hasPages())

            <div class="savings-transfers-pagination">

                {{ $transfers->links() }}

            </div>

        @endif

    </div>

@endsection
