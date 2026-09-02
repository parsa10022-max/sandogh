<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
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
        | لیست اعلان‌ها
        |--------------------------------------------------------------------------
        */

        $notifications = Notification::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->paginate(15);

        /*
        |--------------------------------------------------------------------------
        | نمایش صفحه اعلان‌ها
        |--------------------------------------------------------------------------
        */

        return view(
            'customer.notifications.index',
            compact(
                'notifications',
                'unreadCount'
            )
        );
    }
}
