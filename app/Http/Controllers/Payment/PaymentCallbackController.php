<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Services\Donation\DonationPaymentService;
use App\Services\Savings\SavingsTransferService;
use Illuminate\Http\Request;

class PaymentCallbackController extends Controller
{
    public function __construct(
        private readonly SavingsTransferService $savingsService,
        private readonly DonationPaymentService $donationService,
    ) {
    }

    public function handle(Request $request)
    {
        $type = $request->payment_type;

        try {
            return match ($type) {
                'savings_transfer' => $this->savingsCallback($request),
                'donation' => $this->donationCallback($request),
                default => abort(404),
            };

        } catch (\Throwable $e) {
            report($e);

            return back()
                ->with(
                    'error',
                    'پرداخت با موفقیت تکمیل نشد. لطفاً دوباره تلاش کنید.'
                );
        }
    }

    private function savingsCallback(Request $request)
    {
        $transfer = $this->savingsService->verifyPayment(
            $request->all()
        );

        return redirect()
            ->route(
                'customer.savings-transfer.success',
                $transfer->id
            );
    }

    private function donationCallback(Request $request)
    {
        $payment = $this->donationService->verifyPayment(
            $request->all()
        );

        return redirect()
            ->route(
                'customer.donations.success',
                $payment->id
            );
    }
}
