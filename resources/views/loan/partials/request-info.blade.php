{{-- =========================================================
     درخواست وام
========================================================= --}}

@if($loan->loanRequest)

    <div class="loan-request-card">

        {{-- Header --}}
        <div class="loan-request-header">

            <div class="loan-request-title">

                <span class="loan-request-icon">
                    <i class="bi bi-file-earmark-text"></i>
                </span>

                <span>
                    اطلاعات درخواست وام
                </span>

            </div>

        </div>


        {{-- Body --}}
        <div class="loan-request-body">

            <div class="row g-3">

                {{-- شماره درخواست --}}
                <div class="col-12 col-lg-3">

                    <div class="loan-request-item">

                        <span class="loan-request-label">
                            شماره درخواست
                        </span>

                        <span
                            class="loan-request-value"
                            dir="ltr"
                        >
                            {{ $loan->loanRequest->id }}
                        </span>

                    </div>

                </div>


                {{-- مبلغ درخواستی --}}
                <div class="col-12 col-lg-3">

                    <div class="loan-request-item">

                        <span class="loan-request-label">
                            مبلغ درخواستی
                        </span>

                        <span class="loan-request-value loan-request-money">

                            {{ fa_money($loan->loanRequest->requested_amount) }}

                            <small>
                                ریال
                            </small>

                        </span>

                    </div>

                </div>


                {{-- مبلغ تایید شده --}}
                <div class="col-12 col-lg-3">

                    <div class="loan-request-item">

                        <span class="loan-request-label">
                            مبلغ تایید شده
                        </span>

                        <span class="loan-request-value loan-request-approved">

                            {{ fa_money($loan->loanRequest->approved_amount) }}

                            <small>
                                ریال
                            </small>

                        </span>

                    </div>

                </div>


                {{-- مشاهده درخواست --}}
                <div class="col-12 col-lg-3">

                    <div class="loan-request-action">

                        <a
                            href="{{ route('loan-requests.show', $loan->loanRequest) }}"
                            class="loan-request-view-btn">

                            <span class="loan-request-view-icon">
                                <i class="bi bi-eye"></i>
                            </span>

                            <span>
                                مشاهده درخواست
                            </span>

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

@endif

