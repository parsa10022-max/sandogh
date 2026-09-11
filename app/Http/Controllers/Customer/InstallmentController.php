<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Enums\InstallmentStatus;
use App\Enums\LoanStatus;
use App\Models\Loan;

class InstallmentController extends Controller
{
    /**
     * نمایش اقساط وام خود مشتری
     */
    public function index()
    {
        $customer = auth()->user()->customer;

        $loan = Loan::query()
            ->where('customer_id', $customer->id)
            ->where('status', LoanStatus::ACTIVE)
            ->with([
                'loanType',

                'installments' => function ($query) {
                    $query->orderBy('installment_number');
                },

                // ضامنین وام
                'guarantors.customer',
            ])
            ->first();

        $installment = $loan?->installments
            ->firstWhere('status', InstallmentStatus::PENDING);

        $savingsAccount = \App\Models\Account::query()
            ->where('customer_id', $customer->id)
            ->where('account_type', \App\Enums\AccountType::SAVING)
            ->first();

        return view(
            'customer.installments.index',
            compact(
                'loan',
                'installment',
                'savingsAccount'
            )
        );
    }

    public function success(\App\Models\LoanPayment $payment)
    {
        $customer = auth()->user()->customer;

        abort_unless(
            $customer &&
            $payment->loan->customer_id === $customer->id,
            403
        );

        return view(
            'customer.installments.success',
            compact('payment')
        );
    }

    public function othersPaymentSuccess(\App\Models\LoanPayment $payment)
    {
        $customer = auth()->user()->customer;

        abort_unless(
            $customer &&
            $payment->loan->customer_id !== $customer->id,
            403
        );

        return view(
            'customer.installments.others.success',
            compact('payment')
        );
    }
}
