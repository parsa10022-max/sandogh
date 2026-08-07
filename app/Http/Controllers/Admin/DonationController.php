<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonationType;
use App\Services\Account\AccountService;
use App\Enums\PaymentMethod;
use Illuminate\Http\Request;
use App\Services\Account\AccountTransactionService;
use App\Enums\AccountType;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Enums\TransactionType;
use App\Enums\TransactionSource;



class DonationController extends Controller
{

    public function __construct(
        private readonly AccountService $accountService,
        private readonly AccountTransactionService $accountTransactionService,
    ) {
    }


    public function index()
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


        $transactions = AccountTransaction::query()

            ->with([
                'account',
                'creator'
            ])

            ->where(
                'transaction_source',
                \App\Enums\TransactionSource::OPERATOR->value
            )

            ->where(
                'transaction_type',
                \App\Enums\TransactionType::DEPOSIT->value
            )

            ->latest('transaction_date')

            ->paginate(15);


        return view(
            'donations.index',
            compact(
                'accounts',
                'transactions'
            )
        );
    }

    public function manualCreate()
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
            'donations.manual-create',
            compact('accounts')
        );
    }

    public function manualStore(Request $request)
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


        $account = Account::query()

            ->where(
                'account_type',
                AccountType::SYSTEM
            )

            ->where(
                'status',
                1
            )

            ->findOrFail(
                $request->account_id
            );


        $balanceBefore = $account->balance;


        $balanceAfter = $balanceBefore + (int) $request->amount;


        $account->update([

            'balance' => $balanceAfter,

        ]);


        $this->accountTransactionService->create(

            account: $account,

            type: \App\Enums\TransactionType::DEPOSIT,

            source: \App\Enums\TransactionSource::OPERATOR,

            paymentMethod: PaymentMethod::CASH,

            amount: (int) $request->amount,

            balanceBefore: $balanceBefore,

            balanceAfter: $balanceAfter,

            createdBy: auth()->id(),

            description:
            'کمک دستی اپراتور - '.$account->name,

        );


        return redirect()

            ->route('donations.manual.create')

            ->with(
                'success',
                'کمک دستی با موفقیت ثبت شد.'
            );
    }

}
