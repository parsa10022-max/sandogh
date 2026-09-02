<?php

namespace App\Http\Controllers\Customer;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\InstallmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Installment;
use App\Models\Loan;
use App\Models\LoanRequest;
use App\Models\Notification;
use App\Services\Date\JalaliDateService;
use Illuminate\Support\Facades\Auth;

class CustomerDashboardController extends Controller
{
    public function index(JalaliDateService $jalaliDateService)
    {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | حساب‌های فعال مشتری
        |--------------------------------------------------------------------------
        */

        $accounts = Account::query()
            ->where('customer_id', $user->customer_id)
            ->whereIn('account_type', [
                AccountType::SAVING,
                AccountType::CURRENT,
            ])
            ->where('status', AccountStatus::ACTIVE)
            ->get();

        $savingsAccount = $accounts->firstWhere(
            'account_type',
            AccountType::SAVING
        );

        $currentAccount = $accounts->firstWhere(
            'account_type',
            AccountType::CURRENT
        );

        /*
        |--------------------------------------------------------------------------
        | آخرین تراکنش‌های مشتری
        |--------------------------------------------------------------------------
        */

        $latestTransactions = AccountTransaction::query()
            ->whereIn('account_id', $accounts->pluck('id'))
            ->latest('transaction_date')
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(function (AccountTransaction $transaction) use ($jalaliDateService) {

                if ($transaction->transaction_date) {

                    $transaction->jalali_transaction_date =
                        $jalaliDateService->fromDatabase(
                            $transaction->transaction_date->toDateString()
                        );
                }

                return $transaction;
            });

        /*
        |--------------------------------------------------------------------------
        | مجموع موجودی
        |--------------------------------------------------------------------------
        */

        $totalBalance = $accounts->sum('balance');

        /*
        |--------------------------------------------------------------------------
        | وام فعال مشتری
        |--------------------------------------------------------------------------
        */

        $activeLoan = Loan::query()
            ->where('customer_id', $user->customer_id)
            ->active()
            ->with('loanType')
            ->latest('id')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | اقساط معوق
        |--------------------------------------------------------------------------
        */

        $overdueInstallments = collect();

        if ($activeLoan) {

            $overdueInstallments = $activeLoan
                ->installments()
                ->where(
                    'status',
                    '!=',
                    InstallmentStatus::PAID->value
                )
                ->whereDate(
                    'due_date',
                    '<',
                    now()->toDateString()
                )
                ->orderBy('installment_number')
                ->get();
        }

        $overdueInstallmentsCount = $overdueInstallments->count();

        /*
        |--------------------------------------------------------------------------
        | ایجاد اعلان برای اقساط معوق
        |--------------------------------------------------------------------------
        |
        | برای هر قسط فقط یک اعلان معوق ایجاد می‌شود.
        | شناسه قسط داخل data ذخیره می‌شود تا اعلان تکراری ساخته نشود.
        |
        */

        foreach ($overdueInstallments as $installment) {

            $alreadyNotified = Notification::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'type',
                    'overdue_installment'
                )
                ->where(
                    'data->installment_id',
                    $installment->id
                )
                ->exists();

            if ($alreadyNotified) {
                continue;
            }

            Notification::create([

                'user_id' => $user->id,

                'type' => 'overdue_installment',

                'title' => 'قسط شما معوق شده است.',

                'message' =>
                    'قسط شماره ' .
                    $installment->installment_number .
                    ' وام شما از تاریخ سررسید گذشته و هنوز پرداخت نشده است.',

                'data' => [

                    'amount' =>
                        $installment->amount,

                    'loan_id' =>
                        $installment->loan_id,

                    'installment_id' =>
                        $installment->id,

                    'installment_number' =>
                        $installment->installment_number,

                    'due_date' =>
                        $installment->due_date?->toDateString(),

                    'overdue_days' =>
                        $installment->overdue_days,

                ],

                'read_at' => null,

            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | حساب‌های سیستمی / کمک‌ها
        |--------------------------------------------------------------------------
        */

        $donationAccounts = Account::query()
            ->where(
                'account_type',
                AccountType::SYSTEM
            )
            ->where(
                'status',
                AccountStatus::ACTIVE
            )
            ->orderBy('account_number')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | آخرین درخواست وام مشتری
        |--------------------------------------------------------------------------
        */

        $latestLoanRequest = LoanRequest::query()
            ->where(
                'customer_id',
                $user->customer_id
            )
            ->latest('id')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | اعلان‌های خوانده‌نشده مشتری
        |--------------------------------------------------------------------------
        */

        $notifications = Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->latest('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | تعداد اعلان‌های خوانده‌نشده
        |--------------------------------------------------------------------------
        */

        $unreadNotificationsCount = $notifications->count();

        /*
        |--------------------------------------------------------------------------
        | علامت‌گذاری اعلان‌ها به عنوان خوانده‌شده
        |--------------------------------------------------------------------------
        |
        | اعلان‌ها در همین بار داشبورد نمایش داده می‌شوند.
        |
        */



        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        return view(
            'customer.dashboard',
            compact(
                'user',
                'accounts',
                'savingsAccount',
                'currentAccount',
                'totalBalance',
                'activeLoan',
                'overdueInstallments',
                'overdueInstallmentsCount',
                'donationAccounts',
                'latestLoanRequest',
                'notifications',
                'unreadNotificationsCount',
                'latestTransactions'
            )
        );
    }
}
