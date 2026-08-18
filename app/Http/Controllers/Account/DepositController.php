<?php

namespace App\Http\Controllers\Account;

use App\Enums\PaymentMethod;
use App\Enums\TransactionSource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\DepositRequest;
use App\Models\Account;
use App\Services\Account\AccountService;

class DepositController extends Controller
{
    public function __construct(
        private AccountService $accountService
    ) {
    }

    public function create(Account $account)
    {
        $account->load('customer');

        return view(
            'accounts.deposit.create',
            compact('account')
        );
    }

    public function store(DepositRequest $request)
    {
        $data = $request->validated();

        $account = Account::findOrFail(
            $data['account_id']
        );

        $paymentMethod = PaymentMethod::from(
            $data['payment_method']
        );

        $transaction = $this->accountService->deposit(
            $account,
            $data['amount'],
            $paymentMethod,
            TransactionSource::OPERATOR,
            $data['description'] ?? null
        );

        return redirect()
            ->back()
            ->with(
                'success',
                'واریز با موفقیت ثبت شد. شماره تراکنش: '
                . $transaction->transaction_no
            );
    }
}
