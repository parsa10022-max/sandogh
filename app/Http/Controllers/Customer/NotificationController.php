<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * لیست اعلان‌های مشتری
     */
    public function index()
    {
        $user = Auth::user();

        /*
        |--------------------------------------------------------------------------
        | اعلان‌های مشتری
        |--------------------------------------------------------------------------
        */

        $notifications = Notification::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->paginate(15);


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
        | خوانده‌شدن اعلان‌ها
        |--------------------------------------------------------------------------
        |
        | فقط زمانی که مشتری وارد صفحه اعلان‌ها می‌شود
        | اعلان‌های خوانده‌نشده خوانده‌شده می‌شوند.
        |
        */

        Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);


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

