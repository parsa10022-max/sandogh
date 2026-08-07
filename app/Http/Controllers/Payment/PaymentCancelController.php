<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentCancelController extends Controller
{

    public function handle(Request $request)
    {
        return match(
        $request->payment_type
        ) {

            'donation_public' => redirect()
                ->route(
                    'donation.create'
                )
                ->with(
                    'error',
                    'پرداخت کمک لغو شد.'
                ),


            'donation_customer' => redirect()
                ->route(
                    'customer.donations.create'
                )
                ->with(
                    'error',
                    'پرداخت کمک لغو شد.'
                ),


            'savings_transfer' => redirect()
                ->route(
                    'customer.savings-transfer.create'
                )
                ->with(
                    'error',
                    'پرداخت واریز پس‌انداز لغو شد.'
                ),


            'loan',
            'loan_payment' => redirect()
                ->route(
                    'customer.installments.others.create'
                )
                ->with(
                    'error',
                    'پرداخت قسط لغو شد.'
                ),


            default => redirect('/')
        };
    }

}
