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

class CustomerDashboardController extends Controller
{
    public function index()
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
        | فقط اعلان‌هایی که هنوز توسط مشتری دیده نشده‌اند دریافت می‌شوند.
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
        | اعلان در همین بار صفحه نمایش داده می‌شود.
        | بعد از آن دیگر با Refresh نمایش داده نخواهد شد.
        |
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
            )
        );
    }
}
