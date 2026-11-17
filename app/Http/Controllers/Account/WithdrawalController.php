<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\WithdrawalRequest;
use App\Models\Account;
use App\Models\Withdrawal;
use App\Services\Account\AccountService;
use App\Enums\PaymentMethod;
use App\Enums\WithdrawalStatus;
use Illuminate\Http\Request;
use App\Enums\PaymentBank;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WithdrawalController extends Controller
{
    public function __construct(
        private AccountService $accountService
    ) {
    }

    public function index()
    {
        $withdrawals = Withdrawal::query()
            ->with([
                'account.customer'
            ])
            ->latest()
            ->paginate(20);

        return view(
            'withdrawals.index',
            compact('withdrawals')
        );
    }

    public function create(Account $account)
    {
        $account->load('customer');

        return view(
            'accounts.withdrawal.create',
            compact('account')
        );
    }

    public function store(
        WithdrawalRequest $request,
        Account $account
    ) {
        $paymentMethod = PaymentMethod::from(
            (int) $request->validated('payment_method')
        );

        $this->accountService->withdraw(
            account: $account,
            amount: (int) $request->validated('amount'),
            paymentMethod: $paymentMethod,
            iban: $paymentMethod === PaymentMethod::BANK_TRANSFER
                ? \App\Support\Iban::normalize($request->validated('iban'))
                : null,
            description: $request->validated('description'),
            createdBy: auth()->id(),
            enforceMinimum: false,
    );

        return redirect()
            ->route('accounts.show', $account)
            ->with(
                'success',
                'درخواست برداشت با موفقیت ثبت شد و در انتظار پرداخت قرار گرفت.'
            );
    }

    public function show(Withdrawal $withdrawal)
    {
        $withdrawal->load([
            'account.customer',
            'paidBy',
            'transaction',
        ]);

        return view(
            'withdrawals.show',
            compact('withdrawal')
        );
    }


    public function approve(
        Request $request,
        Withdrawal $withdrawal
    ) {
        DB::transaction(function () use ($request, $withdrawal) {
            $lockedWithdrawal = Withdrawal::query()
                ->lockForUpdate()
                ->findOrFail($withdrawal->id);

            if ($lockedWithdrawal->status !== WithdrawalStatus::PENDING) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'withdrawal' => 'این درخواست دیگر در وضعیت قابل پرداخت نیست.',
                ]);
            }

            $lockedWithdrawal->load('transaction');

            $paymentMethod = $lockedWithdrawal->transaction?->payment_method;

            if (!in_array($paymentMethod, [
                PaymentMethod::CASH,
                PaymentMethod::BANK_TRANSFER,
            ], true)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'withdrawal' => 'روش پرداخت این درخواست معتبر نیست.',
                ]);
            }

            $isBankTransfer = $paymentMethod === PaymentMethod::BANK_TRANSFER;

            $validated = $request->validate(
                [
                    'payment_bank' => $isBankTransfer
                        ? ['required', 'integer', Rule::enum(PaymentBank::class)]
                        : ['nullable'],

                    'payment_tracking_code' => $isBankTransfer
                        ? ['required', 'string', 'max:100']
                        : ['nullable'],
                ],
                [
                    'payment_bank.required' => 'انتخاب بانک پرداخت الزامی است.',
                    'payment_bank.integer' => 'بانک پرداخت معتبر نیست.',
                    'payment_bank.enum' => 'بانک انتخاب‌شده معتبر نیست.',
                    'payment_tracking_code.required' => 'وارد کردن کد پیگیری پرداخت الزامی است.',
                    'payment_tracking_code.string' => 'کد پیگیری پرداخت باید به صورت متن باشد.',
                    'payment_tracking_code.max' => 'کد پیگیری پرداخت نمی‌تواند بیشتر از ۱۰۰ کاراکتر باشد.',
                ]
            );

            $lockedWithdrawal->update([
                'status' => WithdrawalStatus::PAID,
                'payment_bank' => $isBankTransfer
                    ? $validated['payment_bank']
                    : null,
                'payment_tracking_code' => $isBankTransfer
                    ? $validated['payment_tracking_code']
                    : null,
                'paid_by' => auth()->id(),
                'paid_at' => now(),
            ]);
        });

        return redirect()
            ->route('withdrawals.show', $withdrawal)
            ->with('success', 'برداشت با موفقیت پرداخت شد.');
    }


    public function receipt(Withdrawal $withdrawal)
    {
        $withdrawal->load([
            'account.customer',
            'transaction',
            'paidBy',
        ]);

        abort_if(
            $withdrawal->status !== WithdrawalStatus::PAID,
            404
        );

        $receiptNumber = $withdrawal->payment_tracking_code
            ?: $withdrawal->transaction?->transaction_no;

        return view(
            'receipts.saving-withdraw',
            [
                'withdrawal' => $withdrawal,
                'title' => 'رسید برداشت از حساب پس‌انداز',
                'receipt_number' => $receiptNumber,
                'receipt_date' => $withdrawal->paid_at
                    ? \Morilog\Jalali\Jalalian::fromDateTime(
                        $withdrawal->paid_at
                    )->format('Y/m/d H:i')
                    : '-',
            ]
        );
    }

    public function cancel(Withdrawal $withdrawal)
    {
        $this->accountService->cancel(
            withdrawal: $withdrawal,
            customerId: auth()->user()->customer->id,
        );

        return back()->with(
            'success',
            'درخواست برداشت لغو شد.'
        );
    }

    public function myWithdrawals()
    {
        $withdrawals = Withdrawal::whereHas(
            'account',
            function ($query) {
                $query->where(
                    'customer_id',
                    auth()->user()->customer->id
                );
            }
        )
            ->latest()
            ->paginate(15);

        return view(
            'accounts.withdrawal.index',
            compact('withdrawals')
        );
    }

    public function reject(Withdrawal $withdrawal)
    {
        $this->accountService->rejectWithdrawal($withdrawal);

        return redirect()
            ->route('withdrawals.index')
            ->with(
                'success',
                'درخواست برداشت رد شد و مبلغ به حساب عضو بازگردانده شد.'
            );
    }
}
