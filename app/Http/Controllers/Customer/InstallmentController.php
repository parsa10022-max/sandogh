<?php

namespace App\Http\Controllers\Customer;

use App\Enums\AccountType;
use App\Enums\InstallmentStatus;
use App\Enums\LoanStatus;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Loan;
use App\Models\LoanPayment;
use Illuminate\View\View;

class InstallmentController extends Controller
{
    /**
     * نمایش اقساط وام خود مشتری
     */
    public function index(): View
    {
        $customer = auth()->user()->customer;

        abort_unless($customer, 403);

        $loan = Loan::query()
            ->where('customer_id', $customer->id)
            ->where('status', LoanStatus::ACTIVE)
            ->with([
                'loanType',

                'installments' => function ($query) {
                    $query->orderBy('installment_number');
                },

                'guarantors.customer',
            ])
            ->first();

        $installment = $loan?->installments
            ->firstWhere(
                'status',
                InstallmentStatus::PENDING
            );

        $savingsAccount = Account::query()
            ->where('customer_id', $customer->id)
            ->where('account_type', AccountType::SAVING)
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

    /**
     * صفحه موفقیت پرداخت قسط خود مشتری
     */
    public function success(
        LoanPayment $payment
    ): View {
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

    /**
     * صفحه موفقیت پرداخت قسط شخص دیگر
     */
    public function othersPaymentSuccess(
        LoanPayment $payment
    ): View {
        abort_unless(
            $payment->user_id === auth()->id(),
            403
        );

        return view(
            'customer.installments.others.success',
            compact('payment')
        );
    }
}
