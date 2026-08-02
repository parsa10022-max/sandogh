{{-- درخواست وام --}}
@if($loan->loanRequest)

    <div class="card border-info shadow-sm mt-4">

        <div class="card-header bg-info-subtle">

            <h6 class="mb-0 fw-bold">

                <i class="bi bi-file-earmark-text me-1"></i>

                اطلاعات درخواست وام

            </h6>

        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-3 mb-3">

                    <small class="text-muted">
                        شماره درخواست
                    </small>

                    <div class="fw-bold">

                        {{ $loan->loanRequest->id }}

                    </div>

                </div>

                <div class="col-md-3 mb-3">

                    <small class="text-muted">
                        مبلغ درخواستی
                    </small>

                    <div class="fw-bold">

                        {{ number_format($loan->loanRequest->requested_amount) }}
                        ریال

                    </div>

                </div>

                <div class="col-md-3 mb-3">

                    <small class="text-muted">
                        مبلغ تایید شده
                    </small>

                    <div class="fw-bold">

                        {{ number_format($loan->loanRequest->approved_amount) }}
                        ریال

                    </div>

                </div>

                <div class="col-md-3 mb-3 d-flex align-items-end">

                    <a href="{{ route('loan-requests.show',$loan->loanRequest) }}"
                       class="btn btn-outline-primary">

                        <i class="bi bi-eye"></i>

                        مشاهده درخواست

                    </a>

                </div>

            </div>

        </div>

    </div>

@endif
