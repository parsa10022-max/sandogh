<section class="admin-dashboard-action-needed">

    <div class="admin-dashboard-section-header">
        <div>
            <h2>نیاز به اقدام</h2>
            <p>عملیات‌هایی که نیاز به بررسی یا پیگیری دارند</p>
        </div>
    </div>

    <div class="admin-action-grid">

        {{-- =====================================================
             درخواست‌های وام
             ===================================================== --}}
        <a
            href="{{ route('loan-requests.index') }}"
            class="admin-action-card"
        >
            <span class="admin-action-icon admin-action-icon-primary">
                <i class="bi bi-file-earmark-text"></i>
            </span>

            <span class="admin-action-content">
                <strong>درخواست‌های وام</strong>

                <small>
                    در انتظار بررسی
                    <span class="admin-action-count">
                        {{ $dashboard['actionNeeded']['loan_requests_count'] }}
                    </span>
                </small>
            </span>

            <span class="admin-action-arrow">
                <i class="bi bi-chevron-left"></i>
            </span>
        </a>


        {{-- =====================================================
             واریز به حساب پس‌انداز
             ===================================================== --}}
        <a
            href="{{ route('admin.accounting.savings-transfers') }}"
            class="admin-action-card"
        >
            <span class="admin-action-icon admin-action-icon-success">
                <i class="bi bi-wallet2"></i>
            </span>

            <span class="admin-action-content">
                <strong>واریز به حساب پس‌انداز</strong>

                <small>
                    در انتظار تأیید حسابداری
                    <span class="admin-action-count">
                        {{ $dashboard['actionNeeded']['savings_transfers_count'] }}
                    </span>
                </small>
            </span>

            <span class="admin-action-arrow">
                <i class="bi bi-chevron-left"></i>
            </span>
        </a>


        {{-- =====================================================
             برداشت‌ها
             ===================================================== --}}
        <a
            href="{{ route('admin.accounting.withdrawals') }}"
            class="admin-action-card"
        >
            <span class="admin-action-icon admin-action-icon-warning">
                <i class="bi bi-arrow-down-circle"></i>
            </span>

            <span class="admin-action-content">
                <strong>برداشت‌ها</strong>

                <small>
                    در انتظار تأیید حسابداری
                    <span class="admin-action-count">
                        {{ $dashboard['actionNeeded']['withdrawals_count'] }}
                    </span>
                </small>
            </span>

            <span class="admin-action-arrow">
                <i class="bi bi-chevron-left"></i>
            </span>
        </a>


        {{-- =====================================================
             پرداخت اقساط
             ===================================================== --}}
        <a
            href="{{ route('admin.accounting.loan-payments') }}"
            class="admin-action-card"
        >
            <span class="admin-action-icon admin-action-icon-info">
                <i class="bi bi-credit-card"></i>
            </span>

            <span class="admin-action-content">
                <strong>پرداخت اقساط</strong>

                <small>
                    در انتظار تأیید حسابداری
                    <span class="admin-action-count">
                        {{ $dashboard['actionNeeded']['loan_payments_count'] }}
                    </span>
                </small>
            </span>

            <span class="admin-action-arrow">
                <i class="bi bi-chevron-left"></i>
            </span>
        </a>

    </div>

</section>
