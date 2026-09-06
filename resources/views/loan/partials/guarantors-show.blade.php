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

    </div>


    {{-- Body --}}
    <div class="loan-guarantors-body">

        @if($loan->guarantors->isEmpty())

            <div class="loan-guarantors-empty">

                <span class="loan-empty-icon">
                    <i class="bi bi-people"></i>
                </span>

                <span>
                    برای این وام ضامنی ثبت نشده است.
                </span>

            </div>

        @else

            <div class="row g-3">

                @foreach($loan->guarantors as $guarantor)

                    @php

                        $type = $guarantor->guarantor_type?->value
                            ?? $guarantor->guarantor_type;

                        /*
                        |--------------------------------------------------------------------------
                        | مشخصات شخص
                        |--------------------------------------------------------------------------
                        */

                        switch ($type) {

                            case 'customer':

                                $person = $guarantor->customer;

                                $name = trim(
                                    ($person?->first_name ?? '')
                                    . ' ' .
                                    ($person?->last_name ?? '')
                                );

                                $mobile = $person?->mobile ?? '-';

                                $typeLabel = 'عضو صندوق';

                                break;


                            case 'borrower':

                                $person = $loan->customer;

                                $name = trim(
                                    ($person?->first_name ?? '')
                                    . ' ' .
                                    ($person?->last_name ?? '')
                                );

                                $mobile = $person?->mobile ?? '-';

                                $typeLabel = 'وام‌گیرنده';

                                break;


                            case 'external':

                                $name = trim(
                                    ($guarantor->first_name ?? '')
                                    . ' ' .
                                    ($guarantor->last_name ?? '')
                                );

                                $mobile = $guarantor->mobile ?? '-';

                                $typeLabel = 'شخص خارج از صندوق';

                                break;


                            default:

                                $name = trim(
                                    ($guarantor->first_name ?? '')
                                    . ' ' .
                                    ($guarantor->last_name ?? '')
                                );

                                $mobile = $guarantor->mobile ?? '-';

                                $typeLabel = 'ضامن';

                                break;
                        }

                    @endphp


                    {{-- =================================================
                         GUARANTOR CARD
                    ================================================== --}}

                    <div class="col-12 col-lg-6">

                        <div class="loan-guarantor-item">

                            {{-- Header --}}
                            <div class="loan-guarantor-header">

                                <div class="loan-guarantor-person">

                                    <span
                                        class="loan-guarantor-avatar
                                        {{ $guarantor->guarantor_order == 1
                                            ? 'loan-guarantor-avatar-primary'
                                            : 'loan-guarantor-avatar-success' }}">

                                        @if($guarantor->guarantor_order == 1)

                                            <i class="bi bi-person-badge"></i>

                                        @else

                                            <i class="bi bi-person-check"></i>

                                        @endif

                                    </span>


                                    <div>

                                        <strong>
                                            ضامن {{ $guarantor->guarantor_order }}
                                        </strong>

                                        <span class="loan-guarantor-type">
                                            {{ $typeLabel }}
                                        </span>

                                    </div>

                                </div>

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

                                    <strong class="loan-guarantor-value">

                                        {{ $mobile }}

                                    </strong>

                                </div>


                                {{-- Guarantee --}}
                                <div class="loan-guarantor-row">

                                    <span class="loan-guarantor-label">

                                        <i class="bi bi-file-earmark-text"></i>

                                        مدرک ضمانت

                                    </span>

                                    <span class="loan-guarantor-guarantee">

                                        {{
                                            $guarantor->guarantee_type?->label()
                                            ?? $guarantor->guarantee_type
                                            ?? '-'
                                        }}

                                    </span>

                                </div>


                                {{-- Guarantee Number --}}
                                @if($guarantor->guarantee_number)

                                    <div class="loan-guarantor-row">

                                        <span class="loan-guarantor-label">

                                            <i class="bi bi-hash"></i>

                                            شماره مدرک

                                        </span>

                                        <strong class="loan-guarantor-value">

                                            {{ $guarantor->guarantee_number }}

                                        </strong>

                                    </div>

                                @endif


                                {{-- Guarantee Amount --}}
                                @if($guarantor->guarantee_amount)

                                    <div class="loan-guarantor-row">

                                        <span class="loan-guarantor-label">

                                            <i class="bi bi-cash-stack"></i>

                                            مبلغ ضمانت

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
