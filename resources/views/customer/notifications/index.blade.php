@extends('customer.layouts.app')

@section('title', 'اعلان‌ها')

@section('header_title', 'اعلان‌ها')

@section('header_subtitle', 'پیام‌ها و اعلان‌های شما')

@section('content')

    <div class="customer-notifications">

        {{-- عنوان و تعداد اعلان‌های خوانده‌نشده --}}

        <div class="mb-4">

            @if($unreadCount > 0)

                <div class="text-muted small">
                    {{ $unreadCount }} اعلان خوانده‌نشده دارید.
                </div>

            @else

                <div class="text-muted small">
                    همه اعلان‌ها خوانده شده‌اند.
                </div>

            @endif

        </div>


        {{-- لیست اعلان‌ها --}}

        @forelse($notifications as $notification)

            <div class="loan-request-notice">

                <div class="loan-request-notice-icon">

                    @if($notification->type === 'loan_request_approved')

                        <i class="bi bi-check-lg"></i>

                    @elseif($notification->type === 'loan_request_rejected')

                        <i class="bi bi-x-lg"></i>

                    @elseif($notification->type === 'loan_disbursed')

                        <i class="bi bi-wallet2"></i>

                    @else

                        <i class="bi bi-bell"></i>

                    @endif

                </div>


                <div class="loan-request-notice-content">

                    {{-- عنوان --}}

                    <div class="loan-request-notice-title">

                        {{ $notification->title }}

                    </div>


                    {{-- پیام اصلی --}}

                    @if($notification->message)

                        <div class="loan-request-notice-text">

                            {{ $notification->message }}

                        </div>

                    @endif


                    {{-- =========================================
                         تأیید درخواست وام
                    ========================================== --}}

                    @if(
                        $notification->type === 'loan_request_approved'
                        && is_array($notification->data)
                    )

                        <div class="loan-request-notice-details">

                            @if(isset($notification->data['approved_amount']))

                                <div class="loan-request-detail">

                                <span class="loan-request-detail-label">
                                    مبلغ وام
                                </span>

                                    <strong>
                                        {{ number_format($notification->data['approved_amount']) }}
                                        ریال
                                    </strong>

                                </div>

                            @endif


                            @if(isset($notification->data['approved_installment_count']))

                                <div class="loan-request-detail">

                                <span class="loan-request-detail-label">
                                    تعداد اقساط
                                </span>

                                    <strong>
                                        {{ $notification->data['approved_installment_count'] }}
                                        قسط
                                    </strong>

                                </div>

                            @endif


                            @if(isset($notification->data['approved_installment_interval']))

                                <div class="loan-request-detail">

                                <span class="loan-request-detail-label">
                                    دوره بازپرداخت
                                </span>

                                    <strong>

                                        @switch($notification->data['approved_installment_interval'])

                                            @case(1)
                                            ماهانه
                                            @break

                                            @case(2)
                                            هر دو ماه
                                            @break

                                            @case(3)
                                            هر سه ماه
                                            @break

                                            @default
                                            ---
                                        @endswitch

                                    </strong>

                                </div>

                            @endif

                        </div>


                        {{-- پیام مدیر --}}

                        @if(!empty($notification->data['review_note']))

                            <div class="loan-request-notice-note">

                                <i class="bi bi-chat-left-text"></i>

                                <span>
                                پیام مدیر:
                            </span>

                                <strong>
                                    {{ $notification->data['review_note'] }}
                                </strong>

                            </div>

                        @endif

                    @endif


                    {{-- =========================================
                         رد درخواست وام
                    ========================================== --}}

                    @if(
                        $notification->type === 'loan_request_rejected'
                        && is_array($notification->data)
                    )

                        <div class="loan-request-notice-details">

                            @if(isset($notification->data['requested_amount']))

                                <div class="loan-request-detail">

                                <span class="loan-request-detail-label">
                                    مبلغ درخواستی
                                </span>

                                    <strong>
                                        {{ number_format($notification->data['requested_amount']) }}
                                        ریال
                                    </strong>

                                </div>

                            @endif


                            @if(!empty($notification->data['next_review_date']))

                                <div class="loan-request-detail">

                                <span class="loan-request-detail-label">
                                    تاریخ مراجعه مجدد
                                </span>

                                    <strong>
                                        {{ $notification->data['next_review_date'] }}
                                    </strong>

                                </div>

                            @endif

                        </div>


                        {{-- پیام مدیر --}}

                        @if(!empty($notification->data['review_note']))

                            <div class="loan-request-notice-note">

                                <i class="bi bi-chat-left-text"></i>

                                <span>
                                پیام مدیر:
                            </span>

                                <strong>
                                    {{ $notification->data['review_note'] }}
                                </strong>

                            </div>

                        @endif

                    @endif


                    {{-- =========================================
                         وام واریز شده — اقدام برداشت
                    ========================================== --}}

                    @if($notification->type === 'loan_disbursed')

                        <div class="loan-notification-action mt-3">

                            <a href="{{ route('customer.savings.withdrawal.create') }}"
                               class="loan-notification-withdraw-btn">

                            <span class="loan-notification-withdraw-icon">
                                <i class="bi bi-wallet2"></i>
                            </span>

                                <span class="loan-notification-withdraw-content">

                                <strong>
                                    درخواست برداشت وجه
                                </strong>

                                <small>
                                    برای دریافت مبلغ وام، درخواست برداشت ثبت کنید.
                                </small>

                            </span>

                                <i class="bi bi-chevron-left loan-notification-withdraw-arrow"></i>

                            </a>

                        </div>

                    @endif


                    {{-- تاریخ اعلان --}}

                    <div class="text-muted small mt-3">

                        <i class="bi bi-calendar-event"></i>

                        {{ jdate($notification->created_at)->format('Y/m/d H:i') }}

                    </div>

                </div>

            </div>

        @empty

            <div class="text-center py-5">

                <i class="bi bi-bell-slash fs-1 text-muted"></i>

                <div class="mt-3 text-muted">
                    اعلانی برای نمایش وجود ندارد.
                </div>

            </div>

        @endforelse


        {{-- صفحه‌بندی --}}

        @if($notifications->hasPages())

            <div class="mt-4">

                {{ $notifications->links() }}

            </div>

        @endif

    </div>


@endsection
