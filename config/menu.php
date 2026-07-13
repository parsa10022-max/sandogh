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

    /*
    |--------------------------------------------------------------------------
    | اطلاعات پایه
    |--------------------------------------------------------------------------
    */

    [
        'title' => 'اطلاعات پایه',
        'icon'  => 'database',

        'children' => [

            [
                'title' => 'لیست مشتریان',
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
                ],

                'roles' => [
                    UserRole::ADMIN,
                    UserRole::CEO,
                    UserRole::BOARD_MEMBER,
                    UserRole::OPERATOR,
                ],
            ],

            [
                'title' => 'آرشیو مشتریان',
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
            [
                'title' => 'انواع وام',
                'icon' => 'credit-card-2-front',
                'route' => 'loan-types.index',

                'active' => [
                    'loan-types.index',
                    'loan-types.create',
                    'loan-types.store',
                    'loan-types.edit',
                    'loan-types.update',
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
