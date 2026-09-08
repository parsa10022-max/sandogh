{{-- =========================================================
     GUARANTORS
     Design System #1
========================================================= --}}

<div class="loan-guarantors-card">

    {{-- Header --}}
    <div class="loan-section-header">

        <div class="loan-section-title">

            <span class="loan-section-icon">
                <i class="bi bi-people-fill"></i>
            </span>

            <div>
                <h6 class="mb-0">
                    ضامن‌ها
                </h6>

                <small>
                    اطلاعات ضامنین و مدارک ضمانت
                </small>
            </div>

        </div>

        <span class="loan-guarantors-count">
            {{ $loan->guarantors->count() }} ضامن
        </span>

    </div>


    {{-- Body --}}
    <div class="loan-guarantors-body">

        @if($loan->guarantors->isEmpty())

            <div class="loan-guarantors-empty">

                <span class="loan-empty-icon">
                    <i class="bi bi-people"></i>
                </span>

                <div>
                    <strong>ضامنی ثبت نشده است</strong>

                    <small>
                        برای این وام هنوز اطلاعات ضامن ثبت نشده است.
                    </small>
                </div>

            </div>

        @else

            <div class="row g-3">

                @foreach($loan->guarantors->sortBy('guarantor_order') as $guarantor)

                    @php

                        $type = $guarantor->guarantor_type instanceof \App\Enums\GuarantorType
                            ? $guarantor->guarantor_type->value
                            : $guarantor->guarantor_type;

                        /*
                        |--------------------------------------------------------------------------
                        | اطلاعات شخص
                        |--------------------------------------------------------------------------
                        */

                        $person = null;

                        if ($type === 'customer') {

                            $person = $guarantor->customer;

                            $name = trim(
                                ($person?->first_name ?? '')
                                . ' ' .
                                ($person?->last_name ?? '')
                            );

                            $mobile = $person?->mobile;

                            $nationalCode = $person?->national_code;

                            $typeLabel = 'عضو صندوق';

                        } elseif ($type === 'borrower') {

                            $person = $loan->customer;

                            $name = trim(
                                ($person?->first_name ?? '')
                                . ' ' .
                                ($person?->last_name ?? '')
                            );

                            $mobile = $person?->mobile;

                            $nationalCode = $person?->national_code;

                            $typeLabel = 'وام‌گیرنده';

                        } else {

                            $name = trim(
                                ($guarantor->first_name ?? '')
                                . ' ' .
                                ($guarantor->last_name ?? '')
                            );

                            $mobile = $guarantor->mobile;

                            $nationalCode = $guarantor->national_code;

                            $typeLabel = 'شخص خارج از صندوق';

                        }

                        $guaranteeLabel =
                            $guarantor->guarantee_type instanceof \App\Enums\GuaranteeType
                                ? $guarantor->guarantee_type->label()
                                : ($guarantor->guarantee_type ?? '-');

                    @endphp


                    <div class="col-12 col-lg-6">

                        <div class="loan-guarantor-item">

                            {{-- Header --}}
                            <div class="loan-guarantor-header">

                                <div class="loan-guarantor-person">

                                    <span
                                        class="loan-guarantor-avatar
                                        {{ $guarantor->guarantor_order == 1
                                            ? 'loan-guarantor-avatar-primary'
                                            : 'loan-guarantor-avatar-success' }}"
                                    >

                                        @if($guarantor->guarantor_order == 1)
                                            <i class="bi bi-person-badge"></i>
                                        @else
                                            <i class="bi bi-person-check"></i>
                                        @endif

                                    </span>

                                    <div class="loan-guarantor-person-content">

                                        <strong>
                                            ضامن {{ $guarantor->guarantor_order }}
                                        </strong>

                                        <span class="loan-guarantor-type">
                                            {{ $typeLabel }}
                                        </span>

                                    </div>

                                </div>

                                <span class="loan-guarantor-order">
                                    #{{ $guarantor->guarantor_order }}
                                </span>

                            </div>


                            {{-- Information --}}
                            <div class="loan-guarantor-info">

                                {{-- Name --}}
                                <div class="loan-guarantor-row">

                                    <span class="loan-guarantor-label">
                                        <i class="bi bi-person"></i>
                                        نام و نام خانوادگی
                                    </span>

                                    <strong class="loan-guarantor-value">
                                        {{ $name ?: '-' }}
                                    </strong>

                                </div>


                                {{-- Mobile --}}
                                <div class="loan-guarantor-row">

                                    <span class="loan-guarantor-label">
                                        <i class="bi bi-phone"></i>
                                        شماره موبایل
                                    </span>

                                    <strong
                                        class="loan-guarantor-value loan-guarantor-ltr"
                                    >
                                        {{ $mobile ?: '-' }}
                                    </strong>

                                </div>


                                {{-- National Code --}}
                                @if($nationalCode)

                                    <div class="loan-guarantor-row">

                                        <span class="loan-guarantor-label">
                                            <i class="bi bi-person-vcard"></i>
                                            کد ملی
                                        </span>

                                        <strong
                                            class="loan-guarantor-value loan-guarantor-ltr"
                                        >
                                            {{ $nationalCode }}
                                        </strong>

                                    </div>

                                @endif


                                {{-- Guarantee Type --}}
                                <div class="loan-guarantor-row">

                                    <span class="loan-guarantor-label">
                                        <i class="bi bi-file-earmark-text"></i>
                                        نوع ضمانت
                                    </span>

                                    <span class="loan-guarantor-guarantee">
                                        {{ $guaranteeLabel }}
                                    </span>

                                </div>


                                {{-- Guarantee Number --}}
                                @if($guarantor->guarantee_number)

                                    <div class="loan-guarantor-row">

                                        <span class="loan-guarantor-label">
                                            <i class="bi bi-hash"></i>
                                            شماره / سریال
                                        </span>

                                        <strong
                                            class="loan-guarantor-value loan-guarantor-ltr"
                                        >
                                            {{ $guarantor->guarantee_number }}
                                        </strong>

                                    </div>

                                @endif


                                {{-- Guarantee Account --}}
                                @if($guarantor->guarantee_account_number)

                                    <div class="loan-guarantor-row">

                                        <span class="loan-guarantor-label">
                                            <i class="bi bi-bank"></i>
                                            شماره حساب
                                        </span>

                                        <strong
                                            class="loan-guarantor-value loan-guarantor-ltr"
                                        >
                                            {{ $guarantor->guarantee_account_number }}
                                        </strong>

                                    </div>

                                @endif


                                {{-- Guarantee Amount --}}
                                @if($guarantor->guarantee_amount)

                                    <div class="loan-guarantor-row">

                                        <span class="loan-guarantor-label">
                                            <i class="bi bi-cash-stack"></i>
                                            مبلغ تعهد
                                        </span>

                                        <strong class="loan-guarantor-value loan-guarantor-amount">
                                            {{ number_format($guarantor->guarantee_amount) }}
                                            <small>ریال</small>
                                        </strong>

                                    </div>

                                @endif

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

        @endif

    </div>

</div>
