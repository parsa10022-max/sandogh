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
                    'customer.savings.deposit.savings-transfer.success',
                    $payment
                );
            }


            /*
            |--------------------------------------------------------------------------
            | پرداخت قسط وام
            |--------------------------------------------------------------------------
            */

            if ($payment instanceof LoanPayment) {

                if (
                    $payment->loan->customer_id ===
                    auth()->user()->customer?->id
                ) {

                    return redirect()->route(
                        'customer.installments.payment.success',
                        $payment
                    );
                }

                return redirect()->route(
                    'customer.installments.others.payment.success',
                    $payment
                );
            }

            /*
            |--------------------------------------------------------------------------
            | پرداخت کمک
            |--------------------------------------------------------------------------
            */

            if ($payment instanceof DonationPayment) {

                if ($payment->customer_id === null) {

                    return redirect()->route(
                        'donation.success',
                        $payment
                    );
                }

                return redirect()->route(
                    'customer.donations.success',
                    $payment
                );
            }


            throw new \RuntimeException(
                'نوع پرداخت قابل تشخیص نیست.'
            );


        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | پرداخت ناموفق کمک
            |--------------------------------------------------------------------------
            */

            if ($request->payment_type === 'donation_customer') {

                $donationPayment = DonationPayment::find(
                    $request->reference_id
                );

                if ($donationPayment) {

                    return redirect()
                        ->route(
                            'customer.donations.create',
                            [
                                'account_id' =>
                                    $donationPayment->account_id
                            ]
                        )
                        ->with(
                            'error',
                            'پرداخت کمک ناموفق بود.'
                        );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | سایر پرداخت‌ها
            |--------------------------------------------------------------------------
            */

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
     * رسید موفقیت پرداخت قسط برای مشتری
     */
    public function customerSuccess(LoanPayment $payment)
    {
        $customer = auth()->user()->customer;

        if (
            ! $customer ||
            $payment->loan->customer_id !== $customer->id
        ) {
            abort(403);
        }

        return view(
            'customer.payments.success',
            [
                'payment' => $payment,
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
| واریز به حساب پس‌انداز
|--------------------------------------------------------------------------
*/

        if ($request->reference_id) {

            $transfer = SavingsTransfer::find(
                $request->reference_id
            );

            if ($transfer) {

                $customer = auth()->user()->customer;

                /*
                |--------------------------------------------------------------------------
                | واریز به حساب خود مشتری
                |--------------------------------------------------------------------------
                */

                if (
                    $customer
                    && $transfer->receiver_customer_id === $customer->id
                ) {

                    return redirect()
                        ->route(
                            'customer.savings.deposit.create'
                        )
                        ->with(
                            'error',
                            'پرداخت انجام نشد.'
                        );
                }


                /*
                |--------------------------------------------------------------------------
                | واریز به حساب عضو دیگر
                |--------------------------------------------------------------------------
                */

                return redirect()
                    ->route(
                        'customer.savings-transfer.create'
                    )
                    ->with(
                        'error',
                        'پرداخت انجام نشد.'
                    );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | پرداخت قسط
        |--------------------------------------------------------------------------
        */

        if ($request->installment_id) {

            $installment = Installment::with('loan')
                ->find($request->installment_id);

            if ($installment) {

                $customer = auth()->user()->customer;

                // قسط وام خود مشتری
                if (
                    $customer
                    && $installment->loan->customer_id === $customer->id
                ) {

                    return redirect()
                        ->route('customer.installments.index')
                        ->with(
                            'error',
                            'پرداخت قسط انجام نشد.'
                        );
                }

                // قسط وام شخص دیگر
                return redirect()
                    ->route('customer.installments.others.create')
                    ->with(
                        'error',
                        'پرداخت قسط انجام نشد.'
                    );
            }
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
