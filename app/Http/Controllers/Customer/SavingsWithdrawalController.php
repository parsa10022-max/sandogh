<?php

namespace App\Http\Controllers\Customer;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Withdrawal;
use App\Services\Account\AccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavingsWithdrawalController extends Controller
{
    public function __construct(
        private readonly AccountService $accountService
    ) {
    }

    /**
     * فرم درخواست برداشت
     */
    public function create(): View
    {
        $customer = auth()->user()->customer;

        if (! $customer) {
            abort(403);
        }

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

    /**
     * ثبت درخواست برداشت
     */
    public function store(Request $request): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | پاکسازی مبلغ
        |--------------------------------------------------------------------------
        */

        $request->merge([
            'amount' => clean_money($request->amount),
        ]);

        $request->validate([
            'amount' => [
                'required',
                'integer',
                'min:500000',
            ],
        ]);

        $customer = auth()->user()->customer;

        if (! $customer) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | حساب پس‌انداز فعال مشتری
        |--------------------------------------------------------------------------
        */

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
            /*
            |--------------------------------------------------------------------------
            | ثبت درخواست برداشت
            |--------------------------------------------------------------------------
            |
            | موجودی در AccountService رزرو/کسر می‌شود
            | و درخواست با وضعیت PENDING ایجاد می‌شود.
            |
            */

            $withdrawal = $this->accountService->withdraw(
                account: $account,
                amount: (int) $request->amount,
                paymentMethod: PaymentMethod::BANK_TRANSFER,
                description: 'درخواست برداشت مشتری',
                createdBy: auth()->id(),
            );

            /*
            |--------------------------------------------------------------------------
            | اعلان ثبت درخواست
            |--------------------------------------------------------------------------
            */

            Notification::create([
                'user_id' => auth()->id(),

                'type' => 'savings_withdrawal_request',

                'title' => 'درخواست برداشت ثبت شد.',

                'message' =>
                    'درخواست برداشت مبلغ ' .
                    fa_money($withdrawal->amount) .
                    ' ریال از حساب پس‌انداز شما ثبت شد و در انتظار بررسی است.',

                'data' => [
                    'amount' => $withdrawal->amount,

                    'withdrawal_id' => $withdrawal->id,

                    'account_id' => $account->id,

                    'account_number' => $account->account_number,

                    'status' => $withdrawal->status->value,
                ],

                'read_at' => null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | بازگشت به فرم با پیام ثبت موفق درخواست
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('customer.savings.withdrawal.create')
                ->with(
                    'success',
                    'درخواست برداشت شما با موفقیت ثبت شد و در انتظار بررسی است.'
                );

        } catch (\InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'amount' => $e->getMessage(),
                ]);
        }
    }

    /**
     * نمایش نتیجه برداشت
     */
    public function success(Withdrawal $withdrawal): View
    {
        $customer = auth()->user()->customer;

        if (! $customer) {
            abort(403);
        }

        /*
        |--------------------------------------------------------------------------
        | مالکیت درخواست برداشت
        |--------------------------------------------------------------------------
        */

        abort_if(
            $withdrawal->account->customer_id !== $customer->id,
            403
        );

        /*
        |--------------------------------------------------------------------------
        | نمایش رسید فقط برای برداشت پرداخت‌شده
        |--------------------------------------------------------------------------
        |
        | درخواست‌های PENDING نباید به عنوان برداشت موفق نمایش داده شوند.
        |
        */

        abort_unless(
            $withdrawal->status->value === 'paid',
            404
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
