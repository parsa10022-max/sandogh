<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Models\LoanPayment;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use App\Services\Savings\SavingsTransferService;
use App\Models\SavingsTransfer;
use App\Services\Payment\PaymentResolverService;


class PaymentController extends Controller
{
    public function __construct(

        private readonly PaymentService $paymentService,

        private readonly PaymentResolverService $paymentResolver,

        private readonly SavingsTransferService $savingsTransferService,

    ) {
    }

    /**
     * شروع فرآیند پرداخت
     */
    public function pay(Installment $installment)
    {
        try {

            $response = $this->paymentService->startPayment($installment);

            if (! $response['success']) {

                return back()->with(
                    'error',
                    $response['message'] ?? 'خطا در اتصال به درگاه پرداخت.'
                );

            }

            return redirect()->away($response['redirect_url']);

        } catch (\Throwable $e) {

            return back()->with(
                'error',
                $e->getMessage()
            );

        }
    }


    /**
     * Callback بانک
     */
    public function callback(Request $request)
    {
        try {

            $payment = $this->paymentResolver->verify(
                $request->all()
            );

            /*
            |--------------------------------------------------------------------------
            | واریز به حساب پس‌انداز
            |--------------------------------------------------------------------------
            */

            if ($payment instanceof \App\Models\SavingsTransfer) {

                return redirect()->route(
                    'customer.savings-transfer.success',
                    $payment
                );

            }

            /*
            |--------------------------------------------------------------------------
            | پرداخت اقساط
            |--------------------------------------------------------------------------
            */

            if ($payment instanceof \App\Models\LoanPayment) {

                return redirect()->route(
                    'payments.success',
                    $payment
                );

            }

            throw new \RuntimeException(
                'نوع پرداخت قابل تشخیص نیست.'
            );

        } catch (\Throwable $e) {

            return redirect()
                ->route('payments.failed', [

                    'reference_id'   => $request->reference_id,

                    'installment_id' => $request->installment_id,

                ])
                ->with(
                    'error',
                    $e->getMessage()
                );

        }
    }

    /**
     * صفحه تست درگاه Fake
     */
    public function fake(Request $request)
    {
        return view('payments.fake', [

            'data' => $request->all(),

        ]);
    }

    /**
     * رسید پرداخت
     */
    public function success(LoanPayment $payment)
    {
        return view('receipts.payment', [

            'title'          => 'رسید پرداخت قسط',

            'receipt_number' => $payment->tracking_code,

            'receipt_date'   => $payment->paid_at_jalali,

            'payment'        => $payment,

        ]);
    }

    /**
     * صفحه خطای پرداخت
     */
    public function failed(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | واریز به حساب پس‌انداز
        |--------------------------------------------------------------------------
        */

        if (
            $request->reference_id
            &&
            \App\Models\SavingsTransfer::where(
                'id',
                $request->reference_id
            )->exists()
        ) {

            return redirect()
                ->route('customer.savings-transfer.create')
                ->with(
                    'error',
                    'پرداخت انجام نشد.'
                );
        }



        /*
        |--------------------------------------------------------------------------
        | پرداخت قسط وام
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('loans.index')
            ->with(
                'error',
                'پرداخت انجام نشد.'
            );
    }
    /**
     * رسید موفقیت واریز پس‌انداز
     */
    public function savingsTransferSuccess(
        \App\Models\SavingsTransfer $transfer
    ) {
        return view(
            'receipts.savings-transfer',
            [
                'transfer' => $transfer
            ]
        );
    }



    public function savingsTransferFailed()
    {
        return view(
            'customer.savings-transfer.failed'
        );
    }
}
