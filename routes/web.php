<?php

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\BalanceAdjustmentController;
use App\Http\Controllers\Account\CustomerAccountController;
use App\Http\Controllers\Account\DepositController;
use App\Http\Controllers\Account\WithdrawalController;
use App\Http\Controllers\Admin\AccountingController;
use App\Http\Controllers\Admin\DailyOperationsReportController;
use App\Http\Controllers\Admin\DonationController;
use App\Http\Controllers\Admin\FundStatisticController;
use App\Http\Controllers\Admin\GatewayTransactionsReportController;
use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Auth\CustomerActivationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\DonationController as CustomerDonationController;
use App\Http\Controllers\Customer\InstallmentController;
use App\Http\Controllers\Customer\LoanController as CustomerLoanController;
use App\Http\Controllers\Customer\OtherInstallmentPaymentController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\SavingsTransferController;
use App\Http\Controllers\Customer\ServicesController;
use App\Http\Controllers\Customer\SettingsController;
use App\Http\Controllers\Customer\SavingsWithdrawalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonationController as PublicDonationController;
use App\Http\Controllers\Loan\LoanController;
use App\Http\Controllers\Loan\LoanRequestController;
use App\Http\Controllers\LoanType\LoanTypeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SystemAccountController;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Website
|--------------------------------------------------------------------------
*/

Route::get('/', function (\App\Services\FundStatistic\FundStatisticService $service) {
    return view('welcome', [
        'stats' => $service->get(),
    ]);
});


/*
/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

Route::get(
    '/login',
    [LoginController::class, 'showLoginForm']
)->name('login');

Route::post(
    '/login',
    [LoginController::class, 'login']
)->name('login.store');

Route::get(
    '/otp',
    [OtpController::class, 'showVerifyForm']
)->name('otp.form');

Route::post(
    '/otp',
    [OtpController::class, 'verify']
)->middleware('throttle:5,1')
    ->name('otp.verify');


/*
|--------------------------------------------------------------------------
| Customer Account Activation
|--------------------------------------------------------------------------
*/

Route::get(
    '/activate-account',
    [CustomerActivationController::class, 'create']
)->name('customer-activation.create');

Route::post(
    '/activate-account',
    [CustomerActivationController::class, 'sendOtp']
)->middleware('throttle:3,1')
    ->name('customer-activation.send-otp');

Route::get(
    '/activate-account/otp',
    [CustomerActivationController::class, 'showOtp']
)->name('customer-activation.otp');

Route::post(
    '/activate-account/otp',
    [CustomerActivationController::class, 'verifyOtp']
)->middleware('throttle:5,1')
    ->name('customer-activation.verify-otp');

Route::get(
    '/activate-account/account',
    [CustomerActivationController::class, 'showAccount']
)->name('customer-activation.account');

Route::post(
    '/activate-account/account',
    [CustomerActivationController::class, 'createAccount']
)->name('customer-activation.create-account');


/*
|--------------------------------------------------------------------------
| Forgot Password
|--------------------------------------------------------------------------
*/

Route::get(
    '/forgot-password',
    [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'create']
)->name('password.request');

Route::post(
    '/forgot-password',
    [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendOtp']
)->middleware('throttle:3,1')
    ->name('password.otp.send');

Route::get(
    '/forgot-password/otp',
    [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showOtpForm']
)->name('password.otp.form');

Route::post(
    '/forgot-password/otp',
    [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'verifyOtp']
)->middleware('throttle:5,1')
    ->name('password.otp.verify');

Route::get(
    '/reset-password',
    [\App\Http\Controllers\Auth\ResetPasswordController::class, 'create']
)->name('password.reset');

Route::put(
    '/reset-password',
    [\App\Http\Controllers\Auth\ResetPasswordController::class, 'update']
)->name('password.reset.update');

/*
|--------------------------------------------------------------------------
| Public Donation
|--------------------------------------------------------------------------
*/

Route::get(
    '/donation',
    [PublicDonationController::class, 'create']
)->name('donation.create');

Route::post(
    '/donation',
    [PublicDonationController::class, 'store']
)->name('donation.store');

Route::get(
    '/donation/success/{donationPayment}',
    [PublicDonationController::class, 'success']
)->name('donation.success');


/*
|--------------------------------------------------------------------------
| Payment Gateway Callback
|--------------------------------------------------------------------------
|
| Callback باید بدون auth قابل دسترسی باشد؛
| اعتبارسنجی واقعی تراکنش داخل Controller/Service انجام می‌شود.
|
*/

Route::match(
    ['GET', 'POST'],
    '/payments/callback',
    [PaymentController::class, 'callback']
)->name('payments.callback');


/*
|--------------------------------------------------------------------------
| Admin Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin.access'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Admin Profile & Password
    |--------------------------------------------------------------------------
    */

    Route::get(
        'admin/password',
        [PasswordController::class, 'edit']
    )->name('admin.password.edit');

    Route::put(
        'admin/password',
        [PasswordController::class, 'update']
    )->name('admin.password.update');

    Route::get(
        'admin/profile',
        [\App\Http\Controllers\Admin\ProfileController::class, 'index']
    )->name('admin.profile.index');

    Route::put(
        'admin/profile',
        [\App\Http\Controllers\Admin\ProfileController::class, 'update']
    )->name('admin.profile.update');


    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard');

    Route::get(
        'loans/overdue',
        [LoanController::class, 'overdue']
    )->name('loans.overdue');


    /*
    |--------------------------------------------------------------------------
    | Accounts Management
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/accounts/{account}/adjustment',
        [BalanceAdjustmentController::class, 'create']
    )->name('accounts.adjustment.create');

    Route::post(
        '/accounts/{account}/adjustment',
        [BalanceAdjustmentController::class, 'store']
    )->name('accounts.adjustment.store');

    Route::get(
        '/accounts',
        [AccountController::class, 'index']
    )->name('accounts.index');

    Route::get(
        '/accounts/{account}',
        [AccountController::class, 'show']
    )->name('accounts.show');

    Route::get(
        '/accounts/{account}/deposit',
        [DepositController::class, 'create']
    )->name('accounts.deposit.create');

    Route::post(
        '/accounts/deposit',
        [DepositController::class, 'store']
    )->name('accounts.deposit');

    Route::get(
        '/accounts/{account}/transactions',
        [AccountController::class, 'transactions']
    )->name('accounts.transactions');


    /*
    |--------------------------------------------------------------------------
    | Withdrawals
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/accounts/{account}/withdrawal',
        [WithdrawalController::class, 'create']
    )->name('accounts.withdrawal.create');

    Route::post(
        '/accounts/{account}/withdrawal',
        [WithdrawalController::class, 'store']
    )->name('accounts.withdrawal.store');

    Route::resource('withdrawals', WithdrawalController::class)
        ->only([
            'index',
            'show',
            'update',
        ]);

    Route::post(
        '/withdrawals/{withdrawal}/approve',
        [WithdrawalController::class, 'approve']
    )->name('withdrawals.approve');

    Route::patch(
        '/withdrawals/{withdrawal}/cancel',
        [WithdrawalController::class, 'cancel']
    )->name('withdrawals.cancel');

    Route::post(
        '/withdrawals/{withdrawal}/reject',
        [WithdrawalController::class, 'reject']
    )->name('withdrawals.reject');

    Route::get(
        '/my-withdrawals',
        [WithdrawalController::class, 'myWithdrawals']
    )->name('withdrawals.mine');

    Route::get(
        '/withdrawals/{withdrawal}/receipt',
        [WithdrawalController::class, 'receipt']
    )->name('withdrawals.receipt');


    /*
    |--------------------------------------------------------------------------
    | Customers
    |--------------------------------------------------------------------------
    */

    Route::get(
        'customers/{customer}/accounts/create',
        [CustomerAccountController::class, 'create']
    )->name('customers.accounts.create');

    Route::post(
        'customers/{customer}/accounts',
        [CustomerAccountController::class, 'store']
    )->name('customers.accounts.store');

    Route::get(
        'customers/{customer}/accounts/{account}/edit',
        [CustomerAccountController::class, 'edit']
    )->name('customers.accounts.edit');

    Route::put(
        'customers/{customer}/accounts/{account}',
        [CustomerAccountController::class, 'update']
    )->name('customers.accounts.update');

    Route::get(
        'customers/archive',
        [CustomerController::class, 'archive']
    )->name('customers.archive');

    Route::patch(
        'customers/{id}/restore',
        [CustomerController::class, 'restore']
    )->name('customers.restore');

    Route::get(
        'customers/search-code',
        [CustomerController::class, 'searchByCode']
    )->name('customers.search.code');

    Route::resource(
        'customers',
        CustomerController::class
    );


    /*
    |--------------------------------------------------------------------------
    | System Accounts
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'system-accounts',
        SystemAccountController::class
    )->only([
        'index',
        'create',
        'store',
        'edit',
        'update',
    ]);

    Route::patch(
        'system-accounts/{systemAccount}/change-status',
        [
            SystemAccountController::class,
            'changeStatus',
        ]
    )->name('system-accounts.change-status');


    /*
    |--------------------------------------------------------------------------
    | Loan Types
    |--------------------------------------------------------------------------
    */

    Route::resource('loan-types', LoanTypeController::class)
        ->parameters([
            'loan-types' => 'loanType',
        ])
        ->only([
            'index',
            'create',
            'store',
            'edit',
            'update',
        ]);

    Route::patch(
        'loan-types/{loanType}/change-status',
        [LoanTypeController::class, 'changeStatus']
    )->name('loan-types.change-status');


    /*
    |--------------------------------------------------------------------------
    | Loan Requests
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'loan-requests',
        LoanRequestController::class
    )->only([
        'index',
        'create',
        'store',
        'edit',
        'update',
        'show',
    ]);

    Route::post(
        'loan-requests/{loanRequest}/approve',
        [LoanRequestController::class, 'approve']
    )->name('loan-requests.approve');

    Route::post(
        'loan-requests/{loanRequest}/reject',
        [LoanRequestController::class, 'reject']
    )->name('loan-requests.reject');

    Route::put(
        'loan-requests/{loanRequest}/update-review-date',
        [LoanRequestController::class, 'updateReviewDate']
    )->name('loan-requests.update-review-date');


    /*
    |--------------------------------------------------------------------------
    | Loans
    |--------------------------------------------------------------------------
    */

    Route::post(
        'loans/calculate',
        [LoanController::class, 'calculate']
    )->name('loans.calculate');

    Route::get(
        'loans/previous-guarantors/{customer}',
        [LoanController::class, 'previousGuarantors']
    )->name('loans.previous-guarantors');

    Route::resource('loans', LoanController::class)
        ->only([
            'index',
            'create',
            'store',
            'edit',
            'update',
            'show',
            'destroy',
        ]);


    /*
    |--------------------------------------------------------------------------
    | Manual Donations
    |--------------------------------------------------------------------------
    */

    Route::get(
        'donations/manual/create',
        [DonationController::class, 'manualCreate']
    )->name('donations.manual.create');

    Route::post(
        'donations/manual',
        [DonationController::class, 'manualStore']
    )->name('donations.manual.store');

    Route::get(
        'donations',
        [DonationController::class, 'index']
    )->name('donations.index');


    /*
    |--------------------------------------------------------------------------
    | Admin Accounting
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin/accounting')
        ->name('admin.accounting.')
        ->group(function () {

            Route::get(
                '/',
                [AccountingController::class, 'index']
            )->name('index');

            Route::get(
                '/savings-transfers',
                [AccountingController::class, 'savingsTransfers']
            )->name('savings-transfers');

            Route::get(
                '/withdrawals',
                [AccountingController::class, 'withdrawals']
            )->name('withdrawals');

            Route::get(
                '/loan-payments',
                [AccountingController::class, 'loanPayments']
            )->name('loan-payments');

            Route::post(
                '/{type}/{id}/confirm',
                [AccountingController::class, 'confirm']
            )->name('confirm');
        });


    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin/reports')
        ->name('admin.reports.')
        ->group(function () {

            Route::get(
                '/daily-operations',
                [
                    DailyOperationsReportController::class,
                    'index',
                ]
            )->name('daily-operations');

            Route::get(
                '/gateway-transactions',
                [
                    GatewayTransactionsReportController::class,
                    'index',
                ]
            )->name('gateway-transactions');

            Route::get(
                '/gateway-transactions/export',
                [
                    GatewayTransactionsReportController::class,
                    'export',
                ]
            )->name('gateway-transactions.export');
        });
});


/*
|--------------------------------------------------------------------------
| Fund Statistics
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin.access'])
    ->prefix('admin/fund-statistics')
    ->name('admin.fund-statistics.')
    ->group(function () {

        Route::get(
            '/',
            [FundStatisticController::class, 'edit']
        )->name('edit');

        Route::put(
            '/',
            [FundStatisticController::class, 'update']
        )->name('update');
    });


/*
|--------------------------------------------------------------------------
| Payments
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->prefix('payments')
    ->name('payments.')
    ->group(function () {

        Route::post(
            '/{installment}/pay',
            [PaymentController::class, 'pay']
        )->name('pay');

        Route::get(
            '/{payment}/success',
            [PaymentController::class, 'success']
        )->name('success');

        Route::get(
            '/failed',
            [PaymentController::class, 'failed']
        )->name('failed');
    });


/*
|--------------------------------------------------------------------------
| Fake Payment Gateway - Local Only
|--------------------------------------------------------------------------
*/

if (app()->environment('local')) {
    Route::get(
        '/payments/fake',
        [PaymentController::class, 'fake']
    )->middleware('auth')->name('payments.fake');
}


/*
|--------------------------------------------------------------------------
| Customer Panel
|--------------------------------------------------------------------------
*/

Route::post(
    '/logout',
    LogoutController::class
)->middleware('auth')->name('logout');


Route::middleware(['auth', 'customer.access'])
    ->prefix('customer')
    ->name('customer.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Settings
        |--------------------------------------------------------------------------
        */

        Route::put(
            'settings/password',
            [SettingsController::class, 'updatePassword']
        )->name('settings.password.update');

        Route::put(
            'settings/account',
            [SettingsController::class, 'updateAccount']
        )->name('settings.account.update');

        Route::get(
            'settings/mobile/verify',
            [SettingsController::class, 'showMobileVerification']
        )->name('settings.mobile.verify');

        Route::post(
            'settings/mobile/verify',
            [SettingsController::class, 'verifyMobile']
        )->name('settings.mobile.verify.submit');

        Route::get(
            'settings/password/verify',
            [SettingsController::class, 'showPasswordVerification']
        )->name('settings.password.verify');

        Route::post(
            'settings/password/verify',
            [SettingsController::class, 'verifyPassword']
        )->name('settings.password.verify.submit');

        Route::get(
            'settings',
            [SettingsController::class, 'index']
        )->name('settings.index');


        /*
        |--------------------------------------------------------------------------
        | Profile
        |--------------------------------------------------------------------------
        */

        Route::get(
            'profile',
            [ProfileController::class, 'index']
        )->name('profile.index');


        /*
        |--------------------------------------------------------------------------
        | Customer Loans
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/loans',
            [CustomerLoanController::class, 'index']
        )->name('loans.index');

        Route::get(
            '/loans/{loan}',
            [CustomerLoanController::class, 'show']
        )->name('loans.show');


        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        */

        Route::get(
            'notifications',
            [
                \App\Http\Controllers\Customer\NotificationController::class,
                'index',
            ]
        )->name('notifications.index');


        /*
        |--------------------------------------------------------------------------
        | Customer Services
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/services',
            [ServicesController::class, 'index']
        )->name('services');


        /*
        |--------------------------------------------------------------------------
        | Customer Loan Requests
        |--------------------------------------------------------------------------
        */

        Route::get(
            'loan-request/create',
            [
                \App\Http\Controllers\Customer\LoanRequestController::class,
                'create',
            ]
        )->name('loan-request.create');

        Route::post(
            'loan-request',
            [
                \App\Http\Controllers\Customer\LoanRequestController::class,
                'store',
            ]
        )->name('loan-request.store');

        Route::get(
            'loan-requests',
            [
                \App\Http\Controllers\Customer\LoanRequestController::class,
                'index',
            ]
        )->name('loan-requests.index');

        Route::get(
            'loan-request/{loanRequest}',
            [
                \App\Http\Controllers\Customer\LoanRequestController::class,
                'show',
            ]
        )->name('loan-request.show');


        /*
        |--------------------------------------------------------------------------
        | Customer Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/dashboard',
            [
                CustomerDashboardController::class,
                'index',
            ]
        )->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Installments
        |--------------------------------------------------------------------------
        */

        Route::get(
            'installments',
            [InstallmentController::class, 'index']
        )->name('installments.index');

        Route::get(
            'installments/{payment}/success',
            [InstallmentController::class, 'success']
        )->name('installments.payment.success');


        /*
        |--------------------------------------------------------------------------
        | Savings Account
        |--------------------------------------------------------------------------
        */

        Route::get(
            'savings/deposit',
            [SavingsTransferController::class, 'ownDepositCreate']
        )->name('savings.deposit.create');

        Route::post(
            'savings/deposit',
            [SavingsTransferController::class, 'ownDepositStore']
        )->name('savings.deposit.store');

        Route::get(
            'savings/withdrawal',
            [SavingsWithdrawalController::class, 'create']
        )->name('savings.withdrawal.create');

        Route::post(
            'savings/withdrawal',
            [SavingsWithdrawalController::class, 'store']
        )->name('savings.withdrawal.store');

        Route::get(
            'savings/withdrawal/success/{withdrawal}',
            [SavingsWithdrawalController::class, 'success']
        )->name('savings.withdrawal.success');


        /*
        |--------------------------------------------------------------------------
        | Savings Transactions
        |--------------------------------------------------------------------------
        */

        Route::get(
            'savings/transactions',
            [
                SavingsTransferController::class,
                'transactions',
            ]
        )->name('savings.transactions');


        /*
        |--------------------------------------------------------------------------
        | Savings Transfer To Other Members
        |--------------------------------------------------------------------------
        */

        Route::get(
            'savings-transfer',
            [SavingsTransferController::class, 'create']
        )->name('savings-transfer.create');

        Route::post(
            'savings-transfer/search',
            [SavingsTransferController::class, 'search']
        )->name('savings-transfer.search');

        Route::post(
            'savings-transfer',
            [SavingsTransferController::class, 'store']
        )->name('savings-transfer.store');

        Route::get(
            'savings/deposit/savings-transfer/success/{transfer}',
            [PaymentController::class, 'savingsTransferSuccess']
        )->name('savings.deposit.savings-transfer.success');

        Route::get(
            'savings-transfer/failed',
            [PaymentController::class, 'savingsTransferFailed']
        )->name('savings-transfer.failed');


        /*
        |--------------------------------------------------------------------------
        | Installment Payment From Savings
        |--------------------------------------------------------------------------
        */

        Route::post(
            'installments/{installment}/pay-from-savings',
            [PaymentController::class, 'payFromSavings']
        )->name('installments.pay-from-savings');


        /*
        |--------------------------------------------------------------------------
        | Other Installments Payment
        |--------------------------------------------------------------------------
        */

        Route::get(
            'installments/others',
            [OtherInstallmentPaymentController::class, 'create']
        )->name('installments.others.create');

        Route::post(
            'installments/others/pay',
            [
                OtherInstallmentPaymentController::class,
                'pay',
            ]
        )->name('installments.others.pay');

        Route::get(
            'installments/others/{payment}/success',
            [InstallmentController::class, 'othersPaymentSuccess']
        )->name('installments.others.payment.success');


        /*
        |--------------------------------------------------------------------------
        | Customer Donations
        |--------------------------------------------------------------------------
        */

        Route::get(
            'donations/create',
            [CustomerDonationController::class, 'create']
        )->name('donations.create');

        Route::post(
            'donations',
            [CustomerDonationController::class, 'store']
        )->name('donations.store');

        Route::get(
            'donations/payment/{donationPayment}',
            [CustomerDonationController::class, 'payment']
        )->name('donations.payment');

        Route::post(
            'donations/payment/{donationPayment}/pay',
            [CustomerDonationController::class, 'pay']
        )->name('donations.pay');

        Route::get(
            'donations/success/{donationPayment}',
            [CustomerDonationController::class, 'success']
        )->name('donations.success');
    });


/*
|--------------------------------------------------------------------------
| Development Routes
|--------------------------------------------------------------------------
*/

if (app()->environment('local')) {

    Route::view(
        '/test-components',
        'test.components'
    );

    Route::get(
        '/test-otp',
        function (OtpService $otpService) {

            $user = User::first();

            return $otpService->generate($user);
        }
    );
}
