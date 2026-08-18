<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Enums\AccountType;
use Illuminate\Http\Request;
use App\Services\Donation\DonationPaymentService;
use App\Models\DonationPayment;
use App\Enums\AccountStatus;
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
    public function create(Request $request)
    {
        $accounts = Account::query()
            ->where('account_type', AccountType::SYSTEM)
            ->where('status', 1)
            ->orderBy('account_number')
            ->get();

        $selectedAccountId = $request->integer('account_id');

        // حسابی انتخاب نشده است
        if (!$selectedAccountId) {
            return redirect()
                ->route('customer.dashboard')
                ->with('error', 'لطفاً ابتدا حساب مقصد کمک را انتخاب کنید.');
        }

        $selectedAccount = $accounts->firstWhere(
            'id',
            $selectedAccountId
        );

        // حساب انتخاب‌شده معتبر نیست
        if (!$selectedAccount) {
            return redirect()
                ->route('customer.dashboard')
                ->with('error', 'حساب مقصد انتخاب‌شده معتبر نیست.');
        }

        return view(
            'customer.donations.create',
            compact(
                'accounts',
                'selectedAccountId',
                'selectedAccount'
            )
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
                'integer',
                'exists:accounts,id',
            ],

            'amount' => [
                'required',
                'integer',
                'min:50000',
            ],

        ]);


        $customer = auth()->user()->customer;


        $account = Account::query()
            ->where(
                'account_type',
                AccountType::SYSTEM
            )
            ->where(
                'status',
                AccountStatus::ACTIVE
            )
            ->findOrFail(
                $request->account_id
            );


        $result = $this->donationPaymentService
            ->startPayment(

                customer: $customer,

                account: $account,

                amount: (int) $request->amount,

            );


        return redirect()
            ->route(
                'customer.donations.payment',
                $result['payment']->id
            );
    }

    public function payment(
        DonationPayment $donationPayment
    ) {
        abort_if(
            $donationPayment->customer_id
            !== auth()->user()->customer->id,
            403
        );

        return view(
            'customer.donations.payment',
            compact('donationPayment')
        );
    }

    public function pay(
        DonationPayment $donationPayment
    ) {

        abort_if(
            $donationPayment->customer_id
            !== auth()->user()->customer->id,
            403
        );

        $response =
            $this->donationPaymentService
                ->sendToGateway(
                    $donationPayment
                );

        return redirect(
            $response['redirect_url']
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
