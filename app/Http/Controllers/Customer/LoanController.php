<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    /**
     * وام‌های من
     */
    public function index()
    {
        $customer = Auth::user()->customer;

        abort_unless($customer, 403);

        $loans = $customer->loans()
            ->with([
                'loanType',
                'installments',
            ])
            ->latest('id')
            ->paginate(10);

        return view('customer.loans.index', compact(
            'customer',
            'loans'
        ));
    }


    /**
     * جزئیات وام من
     */
    public function show(Loan $loan)
    {
        $customer = Auth::user()->customer;

        abort_unless($customer, 403);

        /*
         * امنیت مهم:
         * مشتری فقط اجازه مشاهده وام خودش را دارد.
         */
        abort_unless(
            $loan->customer_id === $customer->id,
            404
        );

        $loan->load([
            'loanType',
            'installments',
            'guarantors',
        ]);

        return view('customer.loans.show', compact(
            'customer',
            'loan'
        ));
    }
}
