<div class="admin-dashboard-stats">

    {{-- اعضای فعال --}}
    {{-- اعضای فعال --}}
    <div class="admin-dashboard-stat">
        <a href="{{ url('/customers') }}" class="text-decoration-none">
            <x-dashboard.stat-card
                title="اعضای فعال"
                :value="$dashboard['customer']['active_customers_count']"
                icon="bi-people"
                color="success"
            />
        </a>
    </div>

    {{-- وام‌های فعال --}}
    <div class="admin-dashboard-stat">
        <a href="{{ url('/loans') }}" class="text-decoration-none">
            <x-dashboard.stat-card
                title="وام‌های فعال"
                :value="$dashboard['loan']['active_loans_count']"
                icon="bi-cash-stack"
                color="primary"
            />
        </a>
    </div>

    {{-- اقساط معوق --}}
    <div class="admin-dashboard-stat">
        <a href="{{ url('/loans/overdue') }}" class="text-decoration-none">
            <x-dashboard.stat-card
                title="اقساط معوق"
                :value="$dashboard['loan']['overdue_installments_count']"
                icon="bi-clock-history"
                color="warning"
            />
        </a>
    </div>

    {{-- سررسید امروز --}}
    <div class="admin-dashboard-stat">
        <x-dashboard.stat-card
            title="سررسید امروز"
            :value="$dashboard['loan']['today_due_count']"
            icon="bi-calendar-check"
            color="danger"
        />
    </div>

    {{-- درخواست‌های برداشت --}}
    <div class="admin-dashboard-stat">
        <a href="{{ url('/withdrawals') }}" class="text-decoration-none">
            <x-dashboard.stat-card
                title="درخواست‌های برداشت"
                :value="$dashboard['actionNeeded']['withdrawal_requests_count']"
                icon="bi-arrow-down-circle"
                color="warning"
            />
        </a>
    </div>

</div>
