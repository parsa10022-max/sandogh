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
        'icon' => 'speedometer2',
        'children' => [
            [
                'title' => 'داشبورد',
                'icon' => 'speedometer2',
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
    | اطلاعات پایه
    |--------------------------------------------------------------------------
    */

    [
        'title' => 'اطلاعات پایه',
        'icon' => 'database',

        'children' => [

            [
                'title' => 'مشتریان',
                'icon' => 'people',
                'route' => 'customers.index',

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
    | عملیات صندوق
    |--------------------------------------------------------------------------
    */

    [
        'title' => 'عملیات صندوق',
        'icon' => 'wallet2',

        'children' => [

            [
                'title' => 'وام ها',
                'icon' => 'cash-coin',
                'route' => 'loans.index',

                'roles' => [
                    UserRole::ADMIN,
                    UserRole::CEO,
                    UserRole::BOARD_MEMBER,
                    UserRole::OPERATOR,
                ],
            ],

            [
                'title' => 'اقساط',
                'icon' => 'calendar-check',
                'route' => 'installments.index',

                'roles' => [
                    UserRole::ADMIN,
                    UserRole::CEO,
                    UserRole::BOARD_MEMBER,
                    UserRole::OPERATOR,
                ],
            ],

            [
                'title' => 'پرداخت ها',
                'icon' => 'wallet',

                'route' => 'payments.index',

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
    | گزارش ها
    |--------------------------------------------------------------------------
    */

    [
        'title' => 'گزارش ها',
        'icon' => 'bar-chart',

        'children' => [

            [
                'title' => 'گزارش مشتریان',
                'icon' => 'people',
                'route' => 'reports.customers',

                'roles' => [
                    UserRole::ADMIN,
                    UserRole::CEO,
                    UserRole::BOARD_MEMBER,
                ],
            ],

            [
                'title' => 'گزارش وام ها',
                'icon' => 'cash-coin',
                'route' => 'reports.loans',

                'roles' => [
                    UserRole::ADMIN,
                    UserRole::CEO,
                    UserRole::BOARD_MEMBER,
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
        'icon' => 'gear',

        'children' => [

            [
                'title' => 'کاربران',
                'icon' => 'person-gear',
                'route' => 'users.index',

                'roles' => [
                    UserRole::ADMIN,
                ],
            ],

            [
                'title' => 'تنظیمات',
                'icon' => 'sliders',
                'route' => 'settings.index',

                'roles' => [
                    UserRole::ADMIN,
                ],
            ],

        ],

    ],

];
