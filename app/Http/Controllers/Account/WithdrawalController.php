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


        $this->accountService->withdraw(

            account: $account,

            amount: $request->amount,

            paymentMethod: PaymentMethod::BANK_TRANSFER,

            iban: \App\Support\Iban::normalize($request->iban),

            description: $request->description,

            createdBy: auth()->id(),

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
            'account.customer'
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

        $request->validate([

            'payment_bank' => [
                'required',
                'integer',
            ],

            'payment_tracking_code' => [
                'required',
                'string',
                'max:100',
            ],

        ]);


        $withdrawal->update([

            'status' => WithdrawalStatus::PAID,

            'payment_bank' => $request->payment_bank,

            'payment_tracking_code' => $request->payment_tracking_code,

            'paid_by' => auth()->id(),

        ]);


        return redirect()
            ->route('withdrawals.show', $withdrawal)
            ->with(
                'success',
                'برداشت با موفقیت پرداخت شد.'
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
            ->route('withdrawals.show', $withdrawal)
            ->with(
                'success',
                'درخواست برداشت رد شد و مبلغ به حساب عضو بازگردانده شد.'
            );
    }
}
