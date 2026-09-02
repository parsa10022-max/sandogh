<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Installment;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * لیست اعلان‌های مشتری
     */
    public function index(): View
    {
        $user = Auth::user();

        $customer = $user->customer;

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
        | تعداد اقساط معوق
        |--------------------------------------------------------------------------
        */

        $overdueCount = 0;

        if ($customer) {
            $overdueCount = Installment::query()
                ->whereHas('loan', function ($query) use ($customer) {
                    $query->where('customer_id', $customer->id);
                })
                ->where('due_date', '<', now()->toDateString())
                ->where('status', '!=', 'paid')
                ->count();
        }

        /*
        |--------------------------------------------------------------------------
        | لیست اعلان‌ها
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
                'overdueCount'
            )
        );
    }
}
