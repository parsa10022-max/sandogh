<div class="card shadow-sm mb-4">

    <div class="card-header bg-success text-white">

        <i class="bi bi-calendar-event me-1"></i>

        تاریخ‌های وام

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-4 mb-3">

                <small class="text-muted d-block">

                    تاریخ ثبت وام

                </small>

                <strong>

                    {{ $loan->start_date_jalali }}

                </strong>

            </div>

            <div class="col-md-4 mb-3">

                <small class="text-muted d-block">

                    اولین سررسید

                </small>

                <strong>

                    {{ $loan->first_due_date_jalali }}

                </strong>

            </div>

            <div class="col-md-4 mb-3">

                <small class="text-muted d-block">

                    آخرین سررسید

                </small>

                <strong>

                    {{ $loan->last_due_date_jalali }}

                </strong>

            </div>

        </div>

    </div>

</div>
