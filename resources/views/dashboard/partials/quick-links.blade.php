<div class="row g-3 mt-2">

    <div class="col-md-4">

        <a href="{{ route('customers.create') }}"
           class="card border-0 shadow-sm text-decoration-none h-100">

            <div class="card-body text-center">

                <i class="bi bi-person-plus fs-2 text-primary"></i>

                <div class="mt-2 fw-bold">
                    ثبت عضو
                </div>

            </div>

        </a>

    </div>

    <div class="col-md-4">

        <a href="{{ route('loans.create') }}"
           class="card border-0 shadow-sm text-decoration-none h-100">

            <div class="card-body text-center">

                <i class="bi bi-cash-stack fs-2 text-success"></i>

                <div class="mt-2 fw-bold">
                    ثبت وام
                </div>

            </div>

        </a>

    </div>

    <div class="col-md-4">

        <a href="{{ route('loans.overdue') }}"
           class="card border-0 shadow-sm text-decoration-none h-100">

            <div class="card-body text-center">

                <i class="bi bi-exclamation-triangle-fill fs-2 text-danger"></i>

                <div class="mt-2 fw-bold">
                    وام‌های معوق
                </div>

            </div>

        </a>

    </div>

</div>
