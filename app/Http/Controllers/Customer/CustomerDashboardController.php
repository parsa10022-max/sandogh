<?php

namespace App\Http\Controllers\Customer;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\InstallmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Loan;
use App\Models\LoanRequest;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use App\Models\AccountTransaction;
use App\Services\Date\JalaliDateService;

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

                $transaction->jalali_transaction_date =
                    $jalaliDateService->fromDatabase(
                        $transaction->transaction_date->toDateString()
                    );

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

        $overdueInstallmentsCount = 0;

        if ($activeLoan) {

            $overdueInstallmentsCount = $activeLoan
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
                ->count();
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
        |
        | این متغیر برای نمایش وضعیت درخواست فعلی استفاده می‌شود.
        |
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
|
| اعلان‌ها فقط یک بار در بالای داشبورد نمایش داده می‌شوند.
|
*/

        $notifications = Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->latest('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | علامت‌گذاری اعلان‌ها به عنوان خوانده‌شده
        |--------------------------------------------------------------------------
        |
        | اعلان‌ها در همین بار صفحه نمایش داده می‌شوند.
        | اما بعد از آن دیگر در داشبورد نمایش داده نخواهند شد.
        |
        */

        if ($notifications->isNotEmpty()) {

            Notification::query()
                ->where('user_id', $user->id)
                ->whereNull('read_at')
                ->update([
                    'read_at' => now(),
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | تعداد اعلان‌های خوانده‌نشده
        |--------------------------------------------------------------------------
        */

        $unreadNotificationsCount = Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();


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
