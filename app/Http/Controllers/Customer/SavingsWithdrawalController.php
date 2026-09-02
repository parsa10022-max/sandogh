<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\Account\AccountService;
use App\Enums\AccountType;
use App\Enums\AccountStatus;
use App\Enums\PaymentMethod;
use Illuminate\Http\Request;
use App\Models\Withdrawal;
use App\Models\Notification;

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
            ->first();

        return view(
            'customer.savings.withdrawal.create',
            compact(
                'account',
                'customer'
            )
        );
    }


    public function store(Request $request)
    {
        $request->validate([
            'amount' => [
                'required',
                'integer',
                'min:500000',
            ],
        ]);

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

        try {

            $withdrawal = $this->accountService->withdraw(

                account: $account,

                amount: (int) $request->amount,

                paymentMethod: PaymentMethod::BANK_TRANSFER,

                description: 'درخواست برداشت مشتری',

                createdBy: auth()->id(),

        );


            /*
            |--------------------------------------------------------------------------
            | اعلان برداشت موفق
            |--------------------------------------------------------------------------
            */

            Notification::create([

                'user_id' => auth()->id(),

                'type' => 'savings_withdrawal_success',

                'title' => 'برداشت با موفقیت ثبت شد.',

                'message' =>
                    'مبلغ ' .
                    number_format($withdrawal->amount) .
                    ' ریال از حساب پس‌انداز شما برداشت شد.',

                'data' => [

                    'amount' =>
                        $withdrawal->amount,

                    'withdrawal_id' =>
                        $withdrawal->id,

                    'account_id' =>
                        $account->id,

                    'account_number' =>
                        $account->account_number,

                ],

                'read_at' => null,

            ]);


            return redirect()->route(
                'customer.savings.withdrawal.success',
                $withdrawal
            );

        } catch (\InvalidArgumentException $e) {

            return back()
                ->withInput()
                ->withErrors([
                    'amount' => $e->getMessage(),
                ]);
        }
    }

    public function success(Withdrawal $withdrawal)
    {
        $customer = auth()->user()->customer;

        abort_if(
            ! $customer,
            403
        );

        abort_if(
            $withdrawal->account->customer_id !== $customer->id,
            403
        );

        return view(
            'customer.savings.withdrawal.success',
            compact(
                'withdrawal',
                'customer'
            )
        );
    }

}
