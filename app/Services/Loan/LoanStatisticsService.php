<?php

namespace App\Services\Loan;

use App\Enums\InstallmentStatus;
use App\Enums\LoanStatus;
use App\Models\Installment;
use App\Models\Loan;
use App\Models\LoanPayment;
use Illuminate\Database\Eloquent\Collection;

class LoanStatisticsService
{
    /**
     * آمار داشبورد وام
     */
    public function getStatistics(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | وام‌ها
            |--------------------------------------------------------------------------
            */

            'active_loans_count' => Loan::query()
                ->where('status', LoanStatus::ACTIVE)
                ->count(),

            'active_loans_amount' => Loan::query()
                ->where('status', LoanStatus::ACTIVE)
                ->sum('loan_amount'),

            'finished_loans_count' => Loan::query()
                ->where('status', LoanStatus::FINISHED)
                ->count(),

            /*
            |--------------------------------------------------------------------------
            | اقساط معوق
            |--------------------------------------------------------------------------
            */

            'overdue_installments_count' => Installment::query()
                ->where('status', InstallmentStatus::OVERDUE)
                ->count(),

            'overdue_installments_amount' => Installment::query()
                ->where('status', InstallmentStatus::OVERDUE)
                ->sum('amount'),

            /*
            |--------------------------------------------------------------------------
            | سررسید امروز
            |--------------------------------------------------------------------------
            */

            'today_due_count' => Installment::query()
                ->whereDate('due_date', today())
                ->where('status', InstallmentStatus::PENDING)
                ->count(),

            'today_due_amount' => Installment::query()
                ->whereDate('due_date', today())
                ->where('status', InstallmentStatus::PENDING)
                ->sum('amount'),
        ];
    }

    /**
     * آخرین پرداخت‌های موفق
     */
    public function latestPayments(
        int $limit = 5
    ): Collection {
        return LoanPayment::query()
            ->whereNotNull('paid_at')
            ->whereHas('loan')
            ->with([
                'loan.customer',
                'loan.loanType',
            ])
            ->latest('paid_at')
            ->limit($limit)
            ->get();
    }
}
