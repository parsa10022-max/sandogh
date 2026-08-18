<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\Account\AccountService;
use Illuminate\Http\Request;

class BalanceAdjustmentController extends Controller
{
    public function __construct(
        private readonly AccountService $accountService
    ) {
    }

    public function create(Account $account)
    {
        $account->load('customer');

        return view(
            'accounts.adjustment.create',
            compact('account')
        );
    }

    public function store(
        Request $request,
        Account $account
    ) {
        $request->merge([
            'new_balance' => str_replace(
                ',',
                '',
                $request->new_balance
            ),
        ]);

        $data = $request->validate(
            [
                'new_balance' => [
                    'required',
                    'integer',
                    'min:0',
                ],

                'description' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
            ],
            [
                'new_balance.required' => 'موجودی جدید الزامی است.',
                'new_balance.integer' => 'موجودی باید عدد صحیح باشد.',
                'new_balance.min' => 'موجودی نمی‌تواند منفی باشد.',

                'description.max' =>
                    'توضیحات نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            ]
        );

        $transaction = $this->accountService->adjustBalance(
            account: $account,
            newBalance: $data['new_balance'],
            description: $data['description'] ?? null,
            createdBy: auth()->id(),
        );

        return redirect()
            ->route('accounts.show', $account)
            ->with(
                'success',
                'موجودی با موفقیت اصلاح شد. شماره تراکنش: '
                . $transaction->transaction_no
            );
    }
}
