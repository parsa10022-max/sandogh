<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\Donation\DonationPaymentService;
use Illuminate\Http\Request;

class DonationCallbackController extends Controller
{
    public function __construct(
        private readonly DonationPaymentService $service
    ) {
    }

    public function handle(Request $request)
    {
        try {
            $payment = $this->service->verifyPayment(
                $request->all()
            );

            return redirect()
                ->route(
                    'customer.donations.success',
                    $payment->id
                );

        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('customer.donations.failed')
                ->with(
                    'error',
                    'پرداخت با موفقیت تکمیل نشد. لطفاً دوباره تلاش کنید.'
                );
        }
    }
}
