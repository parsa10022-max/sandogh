<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountType;
use App\Enums\PaymentMethod;
use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\DonationType;
use App\Services\Account\AccountService;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    public function __construct(
        private readonly AccountService $accountService,
    ) {
    }

    public function index()
    {
        $accounts = Account::query()
            ->where('account_type', AccountType::SYSTEM)
            ->where('status', 1)
            ->orderBy('account_number')
            ->get();

        $transactions = AccountTransaction::query()
            ->with([
                'account',
                'creator',
            ])
            ->where(
                'transaction_source',
                TransactionSource::OPERATOR->value
            )
            ->where(
                'transaction_type',
                TransactionType::DEPOSIT->value
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
            ->where('account_type', AccountType::SYSTEM)
            ->where('status', 1)
            ->orderBy('account_number')
            ->get();

        return view(
            'donations.manual-create',
            compact('accounts')
        );
    }

    public function manualStore(Request $request)
    {
        $request->merge([
            'amount' => clean_money($request->amount),
        ]);

        $validated = $request->validate(
            [
                'account_id' => [
                    'required',
                    'exists:accounts,id',
                ],

                'amount' => [
                    'required',
                    'integer',
                    'min:10000',
                ],
            ],
            [
                'account_id.required' => 'انتخاب حساب کمک الزامی است.',
                'account_id.exists' => 'حساب انتخاب‌شده معتبر نیست.',

                'amount.required' => 'وارد کردن مبلغ کمک الزامی است.',
                'amount.integer' => 'مبلغ کمک باید به صورت عددی باشد.',
                'amount.min' => 'مبلغ کمک نمی‌تواند کمتر از ۱۰٬۰۰۰ ریال باشد.',
            ]
        );

        $account = Account::query()
            ->where('account_type', AccountType::SYSTEM)
            ->where('status', 1)
            ->findOrFail($validated['account_id']);

        $this->accountService->deposit(
            account: $account,
            amount: (int) $validated['amount'],
            paymentMethod: PaymentMethod::CASH,
            source: TransactionSource::OPERATOR,
            createdBy: auth()->id(),
            description: 'کمک دستی اپراتور - ' . $account->name,
        );

        return redirect()
            ->route('donations.manual.create')
            ->with(
                'success',
                'کمک دستی با موفقیت ثبت شد.'
            );
    }
}
