<?php

namespace App\Http\Controllers;

use App\Enums\AccountingStatus;
use App\Enums\LoanRequestStatus;
use App\Enums\WithdrawalStatus;
use App\Models\Customer;
use App\Models\LoanPayment;
use App\Models\LoanRequest;
use App\Models\SavingsTransfer;
use App\Models\Withdrawal;
use App\Services\Installment\InstallmentService;
use App\Services\Loan\LoanService;
use App\Services\Loan\LoanStatisticsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly LoanStatisticsService $loanStatisticsService,
        private readonly LoanService $loanService,
        private readonly InstallmentService $installmentService,
    ) {
    }

    public function index(): View
    {
        return view('dashboard.index', [

            'dashboard' => [

                'customer' => [
                    'active_customers_count' => Customer::query()
                        ->active()
                        ->count(),
                ],

                'loan' => $this->loanStatisticsService->getStatistics(),

                'latestLoans' => $this->loanService->latest(),

                'overdueInstallments' => $this->installmentService->overdue(),

                'latestPayments' => $this->loanStatisticsService->latestPayments(),

                'upcomingInstallments' => $this->installmentService->upcoming(),

                /*
                |--------------------------------------------------------------------------
                | نیاز به اقدام
                |--------------------------------------------------------------------------
                */

                'actionNeeded' => [

                    /*
                    |--------------------------------------------------------------------------
                    | درخواست‌های وام
                    |--------------------------------------------------------------------------
                    */

                    'loan_requests_count' => LoanRequest::query()
                        ->where(
                            'status',
                            LoanRequestStatus::PENDING
                        )
                        ->count(),

                    /*
                    |--------------------------------------------------------------------------
                    | واریز به حساب پس‌انداز
                    |--------------------------------------------------------------------------
                    */

                    'savings_transfers_count' => SavingsTransfer::query()
                        ->where('status', 'paid')
                        ->where(
                            'accounting_status',
                            AccountingStatus::PENDING
                        )
                        ->count(),

                    /*
                    |--------------------------------------------------------------------------
                    | برداشت‌ها
                    |--------------------------------------------------------------------------
                    */

                    'withdrawals_count' => Withdrawal::query()
                        ->where(
                            'status',
                            WithdrawalStatus::PAID
                        )
                        ->where(
                            'accounting_status',
                            AccountingStatus::PENDING
                        )
                        ->count(),

                    /*
                    |--------------------------------------------------------------------------
                    | پرداخت اقساط
                    |--------------------------------------------------------------------------
                    */

                    'loan_payments_count' => LoanPayment::query()
                        ->whereNotNull('paid_at')
                        ->where(
                            'accounting_status',
                            AccountingStatus::PENDING
                        )
                        ->count(),


                    'withdrawal_requests_count' => Withdrawal::query()
                        ->where('status', WithdrawalStatus::PENDING)
                        ->count(),
                ],

            ]

        ]);
    }
}
