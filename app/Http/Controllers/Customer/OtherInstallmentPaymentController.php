<?php

namespace App\Http\Controllers\Customer;

use App\Enums\InstallmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Installment;
use App\Models\Loan;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;

class OtherInstallmentPaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {
    }


    /**
     * نمایش صفحه پرداخت قسط دیگران
     *
     * جستجو و نمایش نتیجه در همان صفحه
     */
    public function create(Request $request)
    {
        $loan = null;
        $installment = null;


        /*
        |--------------------------------------------------------------------------
        | جستجوی وام
        |--------------------------------------------------------------------------
        */

        if ($request->filled('loan_number')) {

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
                ->where('loan_number', $input)
                ->first();


            /*
            |--------------------------------------------------------------------------
            | وام پیدا نشد
            |--------------------------------------------------------------------------
            */

            if (! $loan) {

                return view(
                    'customer.installments.others.create',
                    [
                        'loan' => null,
                        'installment' => null,
                        'searchError' =>
                            'وامی با این شماره پیدا نشد.',
                    ]
                );
            }


            /*
            |--------------------------------------------------------------------------
            | اولین قسط قابل پرداخت
            |--------------------------------------------------------------------------
            */

            $installment = $loan->installments
                ->where(
                    'status',
                    InstallmentStatus::PENDING
                )
                ->sortBy('installment_number')
                ->first();


            /*
            |--------------------------------------------------------------------------
            | تمام اقساط پرداخت شده
            |--------------------------------------------------------------------------
            */

            if (! $installment) {

                return view(
                    'customer.installments.others.create',
                    [
                        'loan' => $loan,
                        'installment' => null,
                        'searchError' =>
                            'تمام اقساط این وام پرداخت شده است.',
                    ]
                );
            }
        }


        return view(
            'customer.installments.others.create',
            compact(
                'loan',
                'installment'
            )
        );
    }


    /**
     * شروع پرداخت قسط دیگران
     */
    public function pay(Request $request)
    {
        $request->validate([
            'installment_id' => [
                'required',
                'exists:installments,id',
            ],
        ]);


        $installment = Installment::with('loan')
            ->findOrFail(
                $request->installment_id
            );


        /*
        |--------------------------------------------------------------------------
        | جلوگیری از پرداخت قسط خود مشتری
        |--------------------------------------------------------------------------
        */

        $customer = auth()->user()->customer;


        if (
            $customer &&
            $installment->loan->customer_id === $customer->id
        ) {

            return back()->with(
                'error',
                'برای پرداخت قسط خودتان از بخش پرداخت اقساط خود استفاده کنید.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | شروع پرداخت
        |--------------------------------------------------------------------------
        */

        $result = $this->paymentService
            ->startPayment($installment);


        if (! ($result['success'] ?? false)) {

            return back()->with(
                'error',
                $result['message']
                ?? 'خطا در شروع پرداخت.'
            );
        }


        return redirect()->away(
            $result['redirect_url']
        );
    }
}
