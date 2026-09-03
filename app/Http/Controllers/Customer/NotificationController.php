<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Enums\InstallmentStatus;
use App\Models\Installment;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $customer = $user->customer;

        /*
        |--------------------------------------------------------------------------
        | اقساط معوق واقعی مشتری
        |--------------------------------------------------------------------------
        */

        $overdueInstallments = collect();

        if ($customer) {

            $overdueInstallments = Installment::query()
                ->whereHas('loan', function ($query) use ($customer) {
                    $query->where('customer_id', $customer->id);
                })
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
                ->with([
                    'loan.loanType',
                ])
                ->orderBy('installment_number')
                ->get();
        }

        $overdueCount = $overdueInstallments->count();

        $overdueAmount = $overdueInstallments->sum('amount');

        /*
        |--------------------------------------------------------------------------
        | تعداد اعلان‌های خوانده‌نشده
        |--------------------------------------------------------------------------
        */

        $unreadCount = Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | خوانده شدن اعلان‌ها
        |--------------------------------------------------------------------------
        */

        Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        /*
        |--------------------------------------------------------------------------
        | اعلان‌ها
        |--------------------------------------------------------------------------
        */

        $notifications = Notification::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->paginate(15);

        return view(
            'customer.notifications.index',
            compact(
                'notifications',
                'unreadCount',
                'overdueCount',
                'overdueAmount',
                'overdueInstallments'
            )
        );
    }
}
