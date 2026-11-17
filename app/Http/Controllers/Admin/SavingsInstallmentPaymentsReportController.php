<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountType;
use App\Http\Controllers\Controller;
use App\Models\LoanPayment;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;

class SavingsInstallmentPaymentsReportController extends Controller
{
    public function index(Request $request)
    {
        $query = LoanPayment::query()
            ->with([
                'loan.loanType',
                'loan.customer.accounts',
                'installment',
                'user',
            ])
            ->whereNotNull('paid_at')
            ->whereNull('gateway')
            ->whereHas('loan.customer.user', function ($userQuery) {
                $userQuery->whereColumn(
                    'users.id',
                    'loan_payments.user_id'
                );
            });

        if ($request->filled('from_date')) {
            $fromDate = Jalalian::fromFormat(
                'Y/m/d',
                trim($request->from_date)
            )->toCarbon()->format('Y-m-d');

            $query->whereDate('paid_at', '>=', $fromDate);
        }

        if ($request->filled('to_date')) {
            $toDate = Jalalian::fromFormat(
                'Y/m/d',
                trim($request->to_date)
            )->toCarbon()->format('Y-m-d');

            $query->whereDate('paid_at', '<=', $toDate);
        }

        $payments = $query
            ->latest('paid_at')
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return view(
            'admin.reports.savings-installment-payments',
            compact('payments')
        );
    }
}
