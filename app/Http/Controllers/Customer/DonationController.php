<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Enums\AccountType;
use App\Services\Account\AccountTransactionService;
use Illuminate\Http\Request;
use App\Services\Donation\DonationPaymentService;
use App\Models\DonationPayment;
use App\Services\Payment\Gateways\GatewayInterface;



class DonationController extends Controller
{

    public function __construct(
        private readonly DonationPaymentService $donationPaymentService,
        private readonly GatewayInterface $gateway,
    ) {
    }


    /**
     * فرم ثبت کمک
     */
    public function create()
    {

        $accounts = Account::query()

            ->where(
                'account_type',
                AccountType::SYSTEM
            )

            ->where(
                'status',
                1
            )

            ->orderBy('account_number')

            ->get();


        return view(
            'customer.donations.create',
            compact('accounts')
        );

    }



    /**
     * ثبت کمک
     */
    public function store(Request $request)
    {

        $request->validate([

            'account_id' => [
                'required',
                'exists:accounts,id'
            ],

            'amount' => [
                'required',
                'integer',
                'min:10000'
            ],

        ]);



        $customer = auth()->user()->customer;


        $account = \App\Models\Account::findOrFail(
            $request->account_id
        );



        $result = $this->donationPaymentService
            ->startPayment(

                customer: $customer,

                account: $account,

                amount: (int)$request->amount

            );



        return redirect()

            ->route(
                'customer.donations.payment',
                $result['payment']->id
            );

    }

    public function payment(
        DonationPayment $donationPayment
    )
    {

        return view(
            'customer.donations.payment',
            compact('donationPayment')
        );

    }

    public function pay(
        DonationPayment $donationPayment
    )
    {

        $response =
            $this->donationPaymentService
                ->startPayment(

                    customer: auth()->user()->customer,

                    account: $donationPayment->account,

                    amount: $donationPayment->amount

                );


        return redirect(
            $response['gateway']['redirect_url']
        );

    }

    public function success(
        \App\Models\DonationPayment $donationPayment
    )
    {

        abort_if(

            $donationPayment->customer_id
            !== auth()->user()->customer->id,

            403

        );


        return view(
            'customer.donations.success',
            compact('donationPayment')
        );

    }
}
