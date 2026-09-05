<?php

use App\Enums\UserRole;

return [

    /*
    |--------------------------------------------------------------------------
    | داشبورد
    |--------------------------------------------------------------------------
    */

    [
        'title' => 'داشبورد',
        'icon'  => 'speedometer2',
        'children' => [
            [
                'title' => 'داشبورد',
                'icon'  => 'speedometer2',
                'route' => 'dashboard',
                'roles' => [
                    UserRole::ADMIN,
                    UserRole::CEO,
                    UserRole::BOARD_MEMBER,
                    UserRole::OPERATOR,
                ],
            ],
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | اعضا
    |--------------------------------------------------------------------------
    */

    [
        'title' => 'اعضا',
        'icon'  => 'people',
        'children' => [

            [
                'title' => 'اعضا',
                'icon'  => 'people',
                'route' => 'customers.index',
                'active' => [
                    'customers.index',
                    'customers.create',
                    'customers.store',
                    'customers.show',
                    'customers.edit',
                    'customers.update',
                    'customers.destroy',
                    'customers.accounts.create',
                    'customers.accounts.store',
                    'customers.accounts.edit',
                    'customers.accounts.update',
                ],
                'roles' => [
                    UserRole::ADMIN,
                    UserRole::CEO,
                    UserRole::BOARD_MEMBER,
                    UserRole::OPERATOR,
                ],
            ],

            [
                'title' => 'بایگانی',
                'icon'  => 'archive',
                'route' => 'customers.archive',
                'active' => [
                    'customers.archive',
                    'customers.restore',
                ],
                'roles' => [
                    UserRole::ADMIN,
                    UserRole::CEO,
                    UserRole::BOARD_MEMBER,
                    UserRole::OPERATOR,
                ],
            ],

        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | حساب‌ها
    |--------------------------------------------------------------------------
    */

    [
        'title' => 'حساب‌ها',
        'icon'  => 'wallet2',
        'children' => [

            [
                'title' => 'حساب‌های اعضا',
                'icon'  => 'wallet',
                'route' => 'accounts.index',
                'active' => [
                    'accounts.index',
                    'accounts.show',
                    'accounts.adjustment.create',
                    'accounts.adjustment.store',
                ],
                'roles' => [
                    UserRole::ADMIN,
                    UserRole::CEO,
                    UserRole::BOARD_MEMBER,
                    UserRole::OPERATOR,
                ],
            ],

            [
                'title' => 'واریز',
                'icon'  => 'cash-coin',
                'route' => 'accounts.index',
                'active' => [
                    'accounts.deposit.create',
                    'accounts.deposit',
                ],
                'roles' => [
                    UserRole::ADMIN,
                    UserRole::CEO,
                    UserRole::BOARD_MEMBER,
                    UserRole::OPERATOR,
                ],
            ],

            [
                'title' => 'گردش حساب',
                'icon'  => 'arrow-left-right',
                'route' => 'accounts.index',
                'active' => [
                    'accounts.transactions',
                ],
                'roles' => [
                    UserRole::ADMIN,
                    UserRole::CEO,
                    UserRole::BOARD_MEMBER,
                    UserRole::OPERATOR,
                ],
            ],

            [
                'title' => 'درخواست‌های برداشت',
                'icon'  => 'cash-stack',
                'route' => 'withdrawals.index',
                'active' => [
                    'withdrawals.index',
                    'withdrawals.show',
                    'withdrawals.update',
                    'withdrawals.approve',
                    'withdrawals.cancel',
                    'withdrawals.reject',
                ],
                'roles' => [
                    UserRole::ADMIN,
                    UserRole::CEO,
                    UserRole::BOARD_MEMBER,
                    UserRole::OPERATOR,
                ],
            ],

        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | وام
    |--------------------------------------------------------------------------
    */

    [
        'title' => 'وام',
        'icon'  => 'cash-stack',
        'children' => [

            [
                'title' => 'انواع وام',
                'icon'  => 'credit-card-2-front',
                'route' => 'loan-types.index',
                'active' => [
                    'loan-types.index',
                    'loan-types.create',
                    'loan-types.store',
                    'loan-types.edit',
                    'loan-types.update',
                    'loan-types.change-status',
                ],
                'roles' => [
                    UserRole::ADMIN,
                    UserRole::CEO,
                    UserRole::BOARD_MEMBER,
                    UserRole::OPERATOR,
                ],
            ],

            [
                'title' => 'درخواست‌های وام',
                'icon'  => 'file-earmark-text',
                'route' => 'loan-requests.index',
                'active' => [
                    'loan-requests.index',
                    'loan-requests.create',
                    'loan-requests.store',
                    'loan-requests.edit',
                    'loan-requests.update',
                    'loan-requests.show',
                    'loan-requests.approve',
                    'loan-requests.reject',
                    'loan-requests.update-review-date',
                ],
                'roles' => [
                    UserRole::ADMIN,
                    UserRole::CEO,
                    UserRole::BOARD_MEMBER,
                    UserRole::OPERATOR,
                ],
            ],

            [
                'title' => 'وام‌ها',
                'icon'  => 'cash-coin',
                'route' => 'loans.index',
                'active' => [
                    'loans.index',
                    'loans.create',
                    'loans.store',
                    'loans.show',
                    'loans.edit',
                    'loans.update',
                    'loans.destroy',
                    'loans.calculate',
                ],
                'roles' => [
                    UserRole::ADMIN,
                    UserRole::CEO,
                    UserRole::BOARD_MEMBER,
                    UserRole::OPERATOR,
                ],
            ],

            [
                'title' => 'وام‌های معوق',
                'icon'  => 'exclamation-triangle',
                'route' => 'loans.overdue',
                'active' => [
                    'loans.overdue',
                ],
                'roles' => [
                    UserRole::ADMIN,
                    UserRole::CEO,
                    UserRole::BOARD_MEMBER,
                    UserRole::OPERATOR,
                ],
            ],

        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | حساب‌های صندوق
    |--------------------------------------------------------------------------
    */

    /*
|--------------------------------------------------------------------------
| حساب‌های صندوق
|--------------------------------------------------------------------------
*/

    /*
|--------------------------------------------------------------------------
| حساب‌های صندوق
|--------------------------------------------------------------------------
*/

    [
        'title' => 'حساب‌های صندوق',
        'icon'  => 'bank',
        'children' => [

            [
                'title' => 'حساب‌های سیستمی',
                'icon'  => 'wallet2',
                'route' => 'system-accounts.index',
                'active' => [
                    'system-accounts.index',
                    'system-accounts.create',
                    'system-accounts.store',
                    'system-accounts.edit',
                    'system-accounts.update',
                    'system-accounts.change-status',
                ],
                'roles' => [
                    UserRole::ADMIN,
                    UserRole::CEO,
                    UserRole::BOARD_MEMBER,
                    UserRole::OPERATOR,
                ],
            ],

        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | کمک‌های مالی
    |--------------------------------------------------------------------------
    */

    [
        'title' => 'کمک‌های مالی',
        'icon'  => 'heart',
        'children' => [

            [
                'title' => 'کمک‌ها',
                'icon'  => 'heart-fill',
                'route' => 'donations.index',
                'active' => [
                    'donations.index',
                ],
                'roles' => [
                    UserRole::ADMIN,
                    UserRole::CEO,
                    UserRole::BOARD_MEMBER,
                    UserRole::OPERATOR,
                ],
            ],

            [
                'title' => 'ثبت دستی کمک',
                'icon'  => 'plus-circle',
                'route' => 'donations.manual.create',
                'active' => [
                    'donations.manual.create',
                    'donations.manual.store',
                ],
                'roles' => [
                    UserRole::ADMIN,
                    UserRole::CEO,
                    UserRole::BOARD_MEMBER,
                    UserRole::OPERATOR,
                ],
            ],

        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | مدیریت سیستم
    |--------------------------------------------------------------------------
    */

    [
        'title' => 'مدیریت سیستم',
        'icon'  => 'gear',
        'children' => [

            [
                'title' => 'کاربران',
                'icon'  => 'person-gear',
                'route' => 'users.index',
                'active' => [
                    'users.index',
                ],
                'roles' => [
                    UserRole::ADMIN,
                ],
            ],

            [
                'title' => 'تنظیمات',
                'icon'  => 'sliders',
                'route' => 'settings.index',
                'active' => [
                    'settings.index',
                ],
                'roles' => [
                    UserRole::ADMIN,
                ],
            ],

        ],
    ],

];
