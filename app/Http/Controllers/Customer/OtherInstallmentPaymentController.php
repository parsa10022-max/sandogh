<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\Installment;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;


class OtherInstallmentPaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {
    }


    public function create()
    {
        return view(
            'customer.installments.others.create'
        );
    }


    public function search(Request $request)
    {
        $request->validate([
            'loan_number' => 'required|string',
        ]);


        $input = preg_replace(
            '/\D/',
            '',
            $request->loan_number
        );


        $loan = Loan::with([
            'customer',
            'loanType',
            'installments',
        ])
            ->get()
            ->first(fn ($loan) =>
                $loan->search_loan_number === $input
            );


        if (! $loan) {

            return back()->withErrors([
                'loan_number'
                => 'وامی با این شماره پیدا نشد.'
            ]);

        }


        /*
         | اولین قسط قابل پرداخت
        */

        $installment = $loan->installments
            ->where(
                'status',
                \App\Enums\InstallmentStatus::PENDING
            )
            ->sortBy('installment_number')
            ->first();


        if (! $installment) {

            return back()->withErrors([
                'loan_number'
                => 'تمام اقساط این وام پرداخت شده است.'
            ]);

        }


        return view(
            'customer.installments.others.result',
            compact(
                'loan',
                'installment'
            )
        );
    }



    public function pay(Request $request)
    {
        $request->validate([
            'installment_id' => [
                'required',
                'exists:installments,id'
            ],
        ]);


        $installment = Installment::with('loan')
            ->findOrFail(
                $request->installment_id
            );


        $result = $this->paymentService
            ->startPayment($installment);


        return redirect()->away(
            $result['redirect_url']
        );
    }
}
