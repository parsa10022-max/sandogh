<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Models\LoanPayment;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
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

            $payment = $this->paymentService->verifyPayment(
                $request->all()
            );

            return redirect()->route(
                'payments.success',
                $payment
            );

        } catch (\Throwable $e) {

            return redirect()
                ->route('payments.failed')
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
    public function failed()
    {
        return redirect()
            ->route('loans.index')
            ->with(
                'error',
                session(
                    'error',
                    'پرداخت انجام نشد.'
                )
            );
    }
}
