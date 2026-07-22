<div class="card shadow-sm border-0 mt-4">

    <div class="card-header bg-light">

        <h6 class="mb-0 fw-bold">

            اطلاعات سیستم

        </h6>

    </div>

    <div class="card-body">

        <div class="row g-3">

            <div class="col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">

                        وضعیت :

                    </span>

                    <span class="loan-info-value">

                        @if($loan->status->value == 1)

                            <span class="badge bg-success">

                                فعال

                            </span>

                        @elseif($loan->status->value == 2)

                            <span class="badge bg-primary">

                                پایان یافته

                            </span>

                        @else

                            <span class="badge bg-danger">

                                لغو شده

                            </span>

                        @endif

                    </span>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">

                        ایجاد کننده :

                    </span>

                    <span class="loan-info-value">

                        {{ $loan->creator?->name ?? '-' }}

                    </span>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">

                        تاریخ ایجاد :

                    </span>

                    <span class="loan-info-value">

                        {{ $loan->created_at_jalali }}

                    </span>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">

                        آخرین ویرایش :

                    </span>

                    <span class="loan-info-value">

                        {{ $loan->updated_at_jalali }}

                    </span>

                </div>

            </div>

            <div class="col-lg-6">

                <div class="loan-info-item">

                    <span class="loan-info-title">

                        ویرایش کننده :

                    </span>

                    <span class="loan-info-value">

                        {{ $loan->updater?->name ?? '-' }}

                    </span>

                </div>

            </div>

        </div>

    </div>

</div>
