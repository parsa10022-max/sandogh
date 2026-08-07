<?php

namespace App\Http\Controllers;

use App\Enums\AccountStatus;
use App\Models\Account;
use App\Services\Donation\DonationPaymentService;
use Illuminate\Http\Request;
use App\Models\DonationPayment;

class DonationController extends Controller
{

    public function __construct(
        private readonly DonationPaymentService $service
    ) {
    }



    /**
     * صفحه عمومی کمک
     */
    public function create()
    {

        $accounts = Account::query()

            ->whereNull('customer_id')

            ->where(
                'status',
                AccountStatus::ACTIVE
            )

            ->get();



        return view(
            'donation.create',
            compact('accounts')
        );

    }





    /**
     * شروع پرداخت کمک
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

            'donor_name' => [
                'nullable',
                'string',
                'max:255'
            ],

            'donor_mobile' => [
                'nullable',
                'string',
                'max:20'
            ],

        ]);


        $account = Account::query()

            ->whereNull('customer_id')

            ->where(
                'status',
                AccountStatus::ACTIVE
            )

            ->findOrFail(
                $request->account_id
            );


        $result = $this->service->startPayment(

            customer: null,

            account: $account,

            amount: (int) $request->amount,

            donorName: $request->donor_name,

            donorMobile: $request->donor_mobile,

            paymentType: 'donation_public',

        );


        if (
            !isset($result['gateway']['redirect_url'])
        ) {

            return back()->with(
                'error',
                'لینک پرداخت ایجاد نشد.'
            );

        }


        return redirect()->away(
            $result['gateway']['redirect_url']
        );
    }

    public function success(
        DonationPayment $donationPayment
    )
    {

        return view(
            'donation.success',
            compact('donationPayment')
        );

    }

}
