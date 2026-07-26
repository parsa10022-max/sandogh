<?php

namespace App\Http\Controllers;

use App\Services\Loan\LoanStatisticsService;
use Illuminate\View\View;
use App\Services\Installment\InstallmentService;
use App\Services\Loan\LoanService;

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

                'loan' => $this->loanStatisticsService->getStatistics(),

                'latestLoans' => $this->loanService->latest(),

                'overdueInstallments' => $this->installmentService->overdue(),

                // این خط را اضافه کن
                'latestPayments' => $this->loanStatisticsService->latestPayments(),

                'upcomingInstallments' => $this->installmentService->upcoming(),

            ]

        ]);
    }
}
