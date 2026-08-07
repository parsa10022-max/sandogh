<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\Account\AccountService;
use App\Enums\AccountType;
use App\Enums\AccountStatus;
use App\Enums\PaymentMethod;
use Illuminate\Http\Request;

class SavingsWithdrawalController extends Controller
{

    public function __construct(
        private readonly AccountService $accountService
    ) {
    }


    public function create()
    {
        $customer = auth()->user()->customer;


        $account = $customer->accounts()
            ->where(
                'account_type',
                AccountType::SAVING->value
            )
            ->where(
                'status',
                AccountStatus::ACTIVE->value
            )
            ->firstOrFail();


        return view(
            'customer.savings.withdrawal.create',
            compact('account')
        );
    }


    public function store(Request $request)
    {
        $request->validate([

            'amount' => [
                'required',
                'integer',
                'min:1000'
            ]

        ]);


        $customer = auth()->user()->customer;


        $account = $customer->accounts()
            ->where(
                'account_type',
                AccountType::SAVING->value
            )
            ->firstOrFail();


        $this->accountService->withdraw(

            account: $account,

            amount: $request->amount,

            paymentMethod: PaymentMethod::BANK_TRANSFER,

            description: 'درخواست برداشت مشتری',

            createdBy: auth()->id(),

        );


        return redirect()
            ->route('customer.savings.transactions')
            ->with(
                'success',
                'درخواست برداشت با موفقیت ثبت شد.'
            );
    }

}
