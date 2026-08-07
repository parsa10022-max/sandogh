<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Models\LoanPayment;
use App\Models\SavingsTransfer;
use App\Services\Payment\PaymentResolverService;
use App\Services\Payment\PaymentService;
use App\Services\Savings\SavingsTransferService;
use Illuminate\Http\Request;
use App\Models\DonationPayment;



class PaymentController extends Controller
{
    public function __construct(

        private readonly PaymentService $paymentService,

        private readonly PaymentResolverService $paymentResolver,

        private readonly SavingsTransferService $savingsTransferService,

    ) {
    }


    /**
     * شروع فرآیند پرداخت قسط
     */
    public function pay(Installment $installment)
    {
        try {

            $response = $this->paymentService->startPayment(
                $installment
            );


            if (! $response['success']) {

                return back()->with(
                    'error',
                    $response['message']
                    ?? 'خطا در اتصال به درگاه پرداخت.'
                );

            }


            return redirect()->away(
                $response['redirect_url']
            );


        } catch (\Throwable $e) {


            return back()->with(
                'error',
                $e->getMessage()
            );

        }
    }



    /**
     * Callback درگاه پرداخت
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

            if ($payment instanceof SavingsTransfer) {


                return redirect()->route(
                    'customer.savings-transfer.success',
                    $payment
                );

            }



            /*
            |--------------------------------------------------------------------------
            | پرداخت قسط وام
            |--------------------------------------------------------------------------
            */

            if ($payment instanceof LoanPayment) {


                return redirect()->route(
                    'payments.success',
                    $payment
                );

            }

            /*
            |--------------------------------------------------------------------------
            | پرداخت حساب سیستمی
            |--------------------------------------------------------------------------
            */

            if ($payment instanceof DonationPayment) {


                // کمک عمومی (خارج از صندوق)
                if ($payment->customer_id === null) {

                    return redirect()
                        ->route(
                            'donation.success',
                            $payment
                        );

                }


                // کمک عضو صندوق
                return redirect()
                    ->route(
                        'customer.donations.success',
                        $payment
                    );

            }



            throw new \RuntimeException(
                'نوع پرداخت قابل تشخیص نیست.'
            );



        } catch (\Throwable $e) {


            return redirect()
                ->route('payments.failed', [

                    'reference_id' =>
                        $request->reference_id,

                    'installment_id' =>
                        $request->installment_id,

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
        $data = $request->all();


        if (
            in_array(
                $data['payment_type'] ?? null,
                [
                    'donation_customer',
                    'donation_public'
                ]
            )
        ) {


            $payment = DonationPayment::with('account')
                ->find(
                    $data['reference_id'] ?? null
                );


            if ($payment) {

                $data['account_name'] =
                    $payment->account?->name;


                $data['account_number'] =
                    $payment->account?->account_number;

            }

        }


        return view(
            'payments.fake',
            compact('data')
        );

    }




    /**
     * رسید پرداخت قسط
     */
    public function success(LoanPayment $payment)
    {

        return view(
            'receipts.payment',
            [

                'title' =>
                    'رسید پرداخت قسط',

                'receipt_number' =>
                    $payment->tracking_code,

                'receipt_date' =>
                    $payment->paid_at_jalali,

                'payment' =>
                    $payment,

            ]
        );

    }




    /**
     * صفحه خطای پرداخت
     */
    public function failed(Request $request)
    {
        if (
            ($request->payment_type ?? null) === 'donation'
        ) {

            return redirect()
                ->route('donation.create')
                ->with(
                    'error',
                    'پرداخت کمک انجام نشد.'
                );

        }

        /*
        |--------------------------------------------------------------------------
        | واریز پس‌انداز
        |--------------------------------------------------------------------------
        */

        if (

            $request->reference_id

            &&

            SavingsTransfer::where(
                'id',
                $request->reference_id
            )->exists()

        ) {

            return redirect()
                ->route(
                    'customer.savings-transfer.create'
                )
                ->with(
                    'error',
                    'پرداخت انجام نشد.'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | پرداخت قسط
        |--------------------------------------------------------------------------
        */

        if (
            $request->installment_id
            &&
            \App\Models\Installment::where(
                'id',
                $request->installment_id
            )->exists()
        ) {

            return redirect()
                ->route(
                    'customer.installments.others.create'
                )
                ->with(
                    'error',
                    'پرداخت قسط انجام نشد.'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | پرداخت‌های مدیریتی
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
        SavingsTransfer $transfer
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
