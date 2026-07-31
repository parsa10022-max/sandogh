<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\DepositRequest;
use App\Models\Account;
use App\Services\Account\AccountService;
use App\Enums\TransactionSource;
use App\Enums\PaymentMethod;

class DepositController extends Controller
{
    public function __construct(
        private AccountService $accountService
    ) {
    }


    public function store(DepositRequest $request)
    {
        $account = Account::findOrFail(
            $request->account_id
        );


        $paymentMethod = PaymentMethod::from(
            $request->payment_method
        );


        $transaction = $this->accountService->deposit(
            $account,
            $request->amount,
            $paymentMethod,
            TransactionSource::OPERATOR,
            $request->description
        );


        return redirect()
            ->back()
            ->with(
                'success',
                'واریز با موفقیت ثبت شد. شماره تراکنش: '
                . $transaction->transaction_no
            );
    }


    public function create(Account $account)
    {
        $account->load('customer');

        return view('accounts.deposit.create', compact('account'));
    }
}
