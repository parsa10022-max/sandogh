<div class="row g-4">

    <div class="col-xl-3 col-lg-4 col-md-6">
        <x-dashboard.stat-card
            title="وام‌های فعال"
            :value="$dashboard['loan']['active_loans_count']"
            icon="bi-cash-stack"
            color="primary"
        />
    </div>

    <div class="col-xl-3 col-lg-4 col-md-6">
        <x-dashboard.stat-card
            title="مبلغ وام‌های فعال"
            :value="number_format($dashboard['loan']['active_loans_amount'])"
            icon="bi-wallet2"
            color="success"
        />
    </div>

    <div class="col-xl-3 col-lg-4 col-md-6">
        <x-dashboard.stat-card
            title="وام‌های تسویه‌شده"
            :value="$dashboard['loan']['finished_loans_count']"
            icon="bi-check-circle"
            color="info"
        />
    </div>

    <div class="col-xl-3 col-lg-4 col-md-6">
        <x-dashboard.stat-card
            title="اقساط معوق"
            :value="$dashboard['loan']['overdue_installments_count']"
            icon="bi-clock-history"
            color="warning"
        />
    </div>

    <div class="col-xl-3 col-lg-4 col-md-6">
        <x-dashboard.stat-card
            title="مبلغ اقساط معوق"
            :value="number_format($dashboard['loan']['overdue_installments_amount'])"
            icon="bi-exclamation-circle"
            color="danger"
        />
    </div>

    <div class="col-xl-3 col-lg-4 col-md-6">
        <x-dashboard.stat-card
            title="سررسید امروز"
            :value="$dashboard['loan']['today_due_count']"
            subValue="{{ number_format($dashboard['loan']['today_due_amount']) }} تومان"
            icon="bi-calendar-check"
            color="secondary"
        />
    </div>

</div>
