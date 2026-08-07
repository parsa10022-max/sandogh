<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Savings\SavingsTransferService;
use App\Services\Donation\DonationPaymentService;

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


            return match($type) {


                'savings_transfer' =>

                $this->savingsCallback($request),



                'donation' =>

                $this->donationCallback($request),



                default =>

                abort(404)

            };


        } catch(\Throwable $e) {


            return back()
                ->with(
                    'error',
                    $e->getMessage()
                );

        }

    }




    private function savingsCallback(Request $request)
    {

        $transfer =
            $this->savingsService
                ->verifyPayment(
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

        $payment =
            $this->donationService
                ->verifyPayment(
                    $request->all()
                );


        return redirect()
            ->route(
                'customer.donations.success',
                $payment->id
            );

    }

}
