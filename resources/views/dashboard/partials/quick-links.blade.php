<div class="dashboard-quick-links">

    {{-- Section Header --}}
    <div class="dashboard-quick-links-header">

        <div class="dashboard-quick-links-title">

            <div class="dashboard-section-icon dashboard-quick-links-icon">
                <i class="bi bi-lightning-charge"></i>
            </div>

            <div>
                <h6 class="mb-1 fw-bold">
                    دسترسی سریع
                </h6>

                <small class="text-muted">
                    عملیات پرکاربرد مدیریت صندوق
                </small>
            </div>

        </div>

    </div>


    {{-- Links --}}
    <div class="row g-3">

        {{-- ثبت عضو --}}
        <div class="col-12 col-sm-6 col-xl-3">

            <a href="{{ route('customers.create') }}"
               class="dashboard-quick-link dashboard-quick-link-primary">

                <div class="dashboard-quick-link-icon">
                    <i class="bi bi-person-plus"></i>
                </div>

                <div class="dashboard-quick-link-content">

                    <div class="dashboard-quick-link-title">
                        ثبت عضو
                    </div>

                    <div class="dashboard-quick-link-description">
                        افزودن عضو جدید به صندوق
                    </div>

                </div>

                <div class="dashboard-quick-link-arrow">
                    <i class="bi bi-chevron-left"></i>
                </div>

            </a>

        </div>


        {{-- ثبت وام --}}
        <div class="col-12 col-sm-6 col-xl-3">

            <a href="{{ route('loans.create') }}"
               class="dashboard-quick-link dashboard-quick-link-success">

                <div class="dashboard-quick-link-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>

                <div class="dashboard-quick-link-content">

                    <div class="dashboard-quick-link-title">
                        ثبت وام
                    </div>

                    <div class="dashboard-quick-link-description">
                        ثبت وام جدید برای عضو
                    </div>

                </div>

                <div class="dashboard-quick-link-arrow">
                    <i class="bi bi-chevron-left"></i>
                </div>

            </a>

        </div>


        {{-- وام‌های معوق --}}
        <div class="col-12 col-sm-6 col-xl-3">

            <a href="{{ route('loans.overdue') }}"
               class="dashboard-quick-link dashboard-quick-link-danger">

                <div class="dashboard-quick-link-icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>

                <div class="dashboard-quick-link-content">

                    <div class="dashboard-quick-link-title">
                        وام‌های معوق
                    </div>

                    <div class="dashboard-quick-link-description">
                        بررسی اقساط و وام‌های معوق
                    </div>

                </div>

                <div class="dashboard-quick-link-arrow">
                    <i class="bi bi-chevron-left"></i>
                </div>

            </a>

        </div>


        {{-- پنل مشتری خود کاربر --}}
        @if(auth()->user()->customer_id)

            <div class="col-12 col-sm-6 col-xl-3">

                <a href="{{ route('customer.dashboard') }}"
                   class="dashboard-quick-link dashboard-quick-link-info">

                    <div class="dashboard-quick-link-icon">
                        <i class="bi bi-person-circle"></i>
                    </div>

                    <div class="dashboard-quick-link-content">

                        <div class="dashboard-quick-link-title">
                            پنل مشتری من
                        </div>

                        <div class="dashboard-quick-link-description">
                            مشاهده پنل مشتری خودم
                        </div>

                    </div>

                    <div class="dashboard-quick-link-arrow">
                        <i class="bi bi-chevron-left"></i>
                    </div>

                </a>

            </div>

        @endif

    </div>

</div>
