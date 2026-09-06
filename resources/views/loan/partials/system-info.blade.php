{{-- =========================================================
     اطلاعات سیستم
     Design System #1
========================================================= --}}

<div class="loan-system-card">

    {{-- Header --}}
    <div class="loan-section-header">

        <div class="loan-section-title">

            <span class="loan-section-icon">
                <i class="bi bi-gear"></i>
            </span>

            <div>

                <h6 class="mb-0">
                    اطلاعات سیستم
                </h6>

                <small>
                    اطلاعات ثبت، ویرایش و وضعیت وام
                </small>

            </div>

        </div>

    </div>


    {{-- Body --}}
    <div class="loan-system-body">

        <div class="row g-3">


            {{-- وضعیت --}}
            <div class="col-12 col-md-6">

                <div class="loan-system-item">

                    <span class="loan-system-label">

                        <i class="bi bi-circle-half"></i>

                        وضعیت

                    </span>

                    <span class="loan-system-value">

                        @if($loan->status->value === \App\Enums\LoanStatus::ACTIVE->value)

                            <span class="loan-system-status loan-system-status-active">

                                <i class="bi bi-check-circle-fill"></i>

                                فعال

                            </span>

                        @elseif($loan->status->value === \App\Enums\LoanStatus::FINISHED->value)

                            <span class="loan-system-status loan-system-status-finished">

                                <i class="bi bi-check2-all"></i>

                                پایان یافته

                            </span>

                        @else

                            <span class="loan-system-status loan-system-status-cancelled">

                                <i class="bi bi-x-circle-fill"></i>

                                لغو شده

                            </span>

                        @endif

                    </span>

                </div>

            </div>


            {{-- ایجاد کننده --}}
            <div class="col-12 col-md-6">

                <div class="loan-system-item">

                    <span class="loan-system-label">

                        <i class="bi bi-person-plus"></i>

                        ایجاد کننده

                    </span>

                    <strong class="loan-system-value">

                        {{ $loan->creator?->name ?? '-' }}

                    </strong>

                </div>

            </div>


            {{-- تاریخ ایجاد --}}
            <div class="col-12 col-md-6">

                <div class="loan-system-item">

                    <span class="loan-system-label">

                        <i class="bi bi-calendar-plus"></i>

                        تاریخ ایجاد

                    </span>

                    <strong class="loan-system-value loan-system-date">

                        {{ $loan->created_at_jalali ?? '-' }}

                    </strong>

                </div>

            </div>


            {{-- آخرین ویرایش --}}
            <div class="col-12 col-md-6">

                <div class="loan-system-item">

                    <span class="loan-system-label">

                        <i class="bi bi-calendar-check"></i>

                        آخرین ویرایش

                    </span>

                    <strong class="loan-system-value loan-system-date">

                        {{ $loan->updated_at_jalali ?? '-' }}

                    </strong>

                </div>

            </div>


            {{-- ویرایش کننده --}}
            <div class="col-12 col-md-6">

                <div class="loan-system-item">

                    <span class="loan-system-label">

                        <i class="bi bi-person-gear"></i>

                        ویرایش کننده

                    </span>

                    <strong class="loan-system-value">

                        {{ $loan->updater?->name ?? '-' }}

                    </strong>

                </div>

            </div>

        </div>

    </div>

</div>
