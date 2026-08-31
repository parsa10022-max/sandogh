<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Customer\LoanController;
use App\Http\Controllers\LoanType\LoanTypeController;
use App\Http\Controllers\PaymentController;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Account\DepositController;
use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\WithdrawalController;
use App\Http\Controllers\Loan\LoanRequestController;
use App\Http\Controllers\Customer\SavingsTransferController;
use App\Http\Controllers\Customer\CustomerDashboardController;
use App\Http\Controllers\Customer\OtherInstallmentPaymentController;
use App\Http\Controllers\Customer\InstallmentController;
use App\Http\Controllers\Admin\DonationController;
use App\Http\Controllers\Customer\DonationController as CustomerDonationController;
use App\Http\Controllers\Account\BalanceAdjustmentController;
use App\Http\Controllers\SystemAccountController;
use App\Http\Controllers\DonationController as PublicDonationController;








/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Website
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});


/*
|--------------------------------------------------------------------------
| Admin Authentication
|--------------------------------------------------------------------------
*/


Route::get(
    '/login',
    [LoginController::class, 'showLoginForm']
)
    ->name('login');


Route::post(
    '/login',
    [LoginController::class, 'login']
)
    ->name('login.store');


Route::get(
    '/otp',
    [OtpController::class, 'showVerifyForm']
)
    ->name('otp.form');


Route::post(
    '/otp',
    [OtpController::class, 'verify']
)
    ->name('otp.verify');


/*
|--------------------------------------------------------------------------
| Public Loan Information
|--------------------------------------------------------------------------
*/



/*
|--------------------------------------------------------------------------
| Public Donation
|--------------------------------------------------------------------------
*/


Route::get(
    '/donation',
    [PublicDonationController::class, 'create']
)
    ->name('donation.create');


Route::post(
    '/donation',
    [PublicDonationController::class, 'store']
)
    ->name('donation.store');


Route::get(
    '/donation/success/{donationPayment}',
    [
        \App\Http\Controllers\DonationController::class,
        'success'
    ]
)
    ->name('donation.success');







/*
|--------------------------------------------------------------------------
| Admin Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin.access'])->group(function () {


    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    )
        ->middleware('admin.access')
        ->name('dashboard');

    Route::post(
        '/logout',
        LogoutController::class
    )
        ->name('logout');




    Route::get(
        'loans/overdue',
        [LoanController::class, 'overdue']
    )
        ->name('loans.overdue');

    /*
    |--------------------------------------------------------------------------
    | Accounts Management
    |--------------------------------------------------------------------------
    */


    Route::get(
        '/accounts/{account}/adjustment',
        [BalanceAdjustmentController::class, 'create']
    )
        ->name('accounts.adjustment.create');

    Route::post(
        '/accounts/{account}/adjustment',
        [BalanceAdjustmentController::class, 'store']
    )
        ->name('accounts.adjustment.store');

    Route::get(
        '/accounts',
        [AccountController::class, 'index']
    )
        ->name('accounts.index');


    Route::get(
        '/accounts/{account}',
        [AccountController::class, 'show']
    )
        ->name('accounts.show');


    Route::get(
        '/accounts/{account}/deposit',
        [DepositController::class, 'create']
    )
        ->name('accounts.deposit.create');


    Route::post(
        '/accounts/deposit',
        [DepositController::class, 'store']
    )
        ->name('accounts.deposit');


    Route::get(
        '/accounts/{account}/transactions',
        [AccountController::class, 'transactions']
    )
        ->name('accounts.transactions');



    /*
    |--------------------------------------------------------------------------
    | Withdrawals
    |--------------------------------------------------------------------------
    */


    Route::get(
        '/accounts/{account}/withdrawal',
        [WithdrawalController::class, 'create']
    )
        ->name('accounts.withdrawal.create');


    Route::post(
        '/accounts/{account}/withdrawal',
        [WithdrawalController::class, 'store']
    )
        ->name('accounts.withdrawal.store');


    Route::resource('withdrawals', WithdrawalController::class)
        ->only([
            'index',
            'show',
            'update',
        ]);


    Route::post(
        '/withdrawals/{withdrawal}/approve',
        [WithdrawalController::class, 'approve']
    )
        ->name('withdrawals.approve');


    Route::patch(
        '/withdrawals/{withdrawal}/cancel',
        [WithdrawalController::class, 'cancel']
    )
        ->name('withdrawals.cancel');


    Route::post(
        '/withdrawals/{withdrawal}/reject',
        [WithdrawalController::class, 'reject']
    )
        ->name('withdrawals.reject');


    Route::get(
        '/my-withdrawals',
        [WithdrawalController::class, 'myWithdrawals']
    )
        ->name('withdrawals.mine');



    /*
    |--------------------------------------------------------------------------
    | Customers
    |--------------------------------------------------------------------------
    */


    Route::get(
        'customers/{customer}/accounts/create',
        [\App\Http\Controllers\Account\CustomerAccountController::class, 'create']
    )->name('customers.accounts.create');

    Route::post(
        'customers/{customer}/accounts',
        [\App\Http\Controllers\Account\CustomerAccountController::class, 'store']
    )->name('customers.accounts.store');

    Route::get(
        'customers/{customer}/accounts/{account}/edit',
        [\App\Http\Controllers\Account\CustomerAccountController::class, 'edit']
    )->name('customers.accounts.edit');

    Route::put(
        'customers/{customer}/accounts/{account}',
        [\App\Http\Controllers\Account\CustomerAccountController::class, 'update']
    )->name('customers.accounts.update');

    Route::get(
        'customers/archive',
        [CustomerController::class, 'archive']
    )
        ->name('customers.archive');


    Route::patch(
        'customers/{id}/restore',
        [CustomerController::class, 'restore']
    )
        ->name('customers.restore');


    Route::get(
        'customers/search-code',
        [CustomerController::class, 'searchByCode']
    )
        ->name('customers.search.code');


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
    )
        ->only([
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
            'changeStatus'
        ]
    )
        ->name('system-accounts.change-status');



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
    )
        ->name('loan-types.change-status');



    /*
    |--------------------------------------------------------------------------
    | Loan Requests
    |--------------------------------------------------------------------------
    */


    Route::resource(
        'loan-requests',
        LoanRequestController::class
    )
        ->only([
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
    )
        ->name('loan-requests.approve');


    Route::post(
        'loan-requests/{loanRequest}/reject',
        [LoanRequestController::class, 'reject']
    )
        ->name('loan-requests.reject');


    Route::put(
        'loan-requests/{loanRequest}/update-review-date',
        [LoanRequestController::class, 'updateReviewDate']
    )
        ->name('loan-requests.update-review-date');



    /*
    |--------------------------------------------------------------------------
    | Loans
    |--------------------------------------------------------------------------
    */


    Route::post(
        'loans/calculate',
        [LoanController::class, 'calculate']
    )
        ->name('loans.calculate');


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
    )
        ->name('donations.manual.create');


    Route::post(
        'donations/manual',
        [DonationController::class, 'manualStore']
    )
        ->name('donations.manual.store');


    Route::get(
        'donations',
        [DonationController::class, 'index']
    )
        ->name('donations.index');


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

        Route::match(
            ['GET', 'POST'],
            '/callback',
            [PaymentController::class, 'callback']
        )->name('callback');

        Route::get(
            '/fake',
            [PaymentController::class, 'fake']
        )->name('fake');

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
| Customer Panel Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'customer.access'])
    ->prefix('customer')
    ->name('customer.')
    ->group(function () {

        Route::get('/loans', [LoanController::class, 'index'])
            ->name('loans.index');

        Route::get('/loans/{loan}', [LoanController::class, 'show'])
            ->name('loans.show');


        Route::get(
            'notifications',
            [
                \App\Http\Controllers\Customer\NotificationController::class,
                'index'
            ]
        )->name('notifications.index');


        Route::get(
            'installments/{payment}/success',
            [\App\Http\Controllers\Customer\InstallmentController::class, 'success']
        )->name('installments.payment.success');


        /*
        |--------------------------------------------------------------------------
        | Customer Loan Requests
        |--------------------------------------------------------------------------
        */

        Route::get(
            'loan-request/create',
            [\App\Http\Controllers\Customer\LoanRequestController::class, 'create']
        )->name('loan-request.create');

        Route::post(
            'loan-request',
            [\App\Http\Controllers\Customer\LoanRequestController::class, 'store']
        )->name('loan-request.store');

        Route::get(
            'loan-requests',
            [\App\Http\Controllers\Customer\LoanRequestController::class, 'index']
        )->name('loan-requests.index');



        Route::get(
            'loan-request/{loanRequest}',
            [\App\Http\Controllers\Customer\LoanRequestController::class, 'show']
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
                'index'
            ]
        )
            ->name('dashboard');



        Route::get(
            'installments',
            [
                \App\Http\Controllers\Customer\InstallmentController::class,
                'index'
            ]
        )->name('installments.index');

        /*
        |--------------------------------------------------------------------------
        | Savings Account
        |--------------------------------------------------------------------------
        */


        // واریز به حساب پس‌انداز خود

        Route::get(
            'savings/deposit',
            [SavingsTransferController::class, 'ownDepositCreate']
        )
            ->name('savings.deposit.create');


        Route::post(
            'savings/deposit',
            [SavingsTransferController::class, 'ownDepositStore']
        )
            ->name('savings.deposit.store');



        // برداشت از حساب پس‌انداز

        Route::get(
            'savings/withdrawal',
            [
                \App\Http\Controllers\Customer\SavingsWithdrawalController::class,
                'create'
            ]
        )->name('savings.withdrawal.create');


        Route::post(
            'savings/withdrawal',
            [
                \App\Http\Controllers\Customer\SavingsWithdrawalController::class,
                'store'
            ]
        )->name('savings.withdrawal.store');


        Route::get(
            'savings/withdrawal/success/{withdrawal}',
            [
                \App\Http\Controllers\Customer\SavingsWithdrawalController::class,
                'success'
            ]
        )->name('savings.withdrawal.success');



        // گردش حساب

        Route::get(
            'savings/transactions',
            [
                SavingsTransferController::class,
                'transactions'
            ]
        )
            ->name('savings.transactions');



        /*
        |--------------------------------------------------------------------------
        | Transfer Savings To Other Members
        |--------------------------------------------------------------------------
        */


        Route::get(
            'savings-transfer',
            [SavingsTransferController::class, 'create']
        )
            ->name('savings-transfer.create');


        Route::post(
            'savings-transfer/search',
            [SavingsTransferController::class, 'search']
        )
            ->name('savings-transfer.search');


        Route::post(
            'savings-transfer',
            [SavingsTransferController::class, 'store']
        )
            ->name('savings-transfer.store');


        Route::get(
            'savings/deposit/savings-transfer/success/{transfer}',
            [PaymentController::class, 'savingsTransferSuccess']
        )
            ->name('savings.deposit.savings-transfer.success');


        Route::get(
            'savings-transfer/failed',
            [PaymentController::class, 'savingsTransferFailed']
        )
            ->name('savings-transfer.failed');



        /*
|--------------------------------------------------------------------------
| Other Installments Payment
|--------------------------------------------------------------------------
*/

        Route::get(
            'installments/others',
            [OtherInstallmentPaymentController::class, 'create']
        )
            ->name('installments.others.create');





        Route::post(
            'installments/others/pay',
            [
                OtherInstallmentPaymentController::class,
                'pay'
            ]
        )
            ->name('installments.others.pay');


        Route::get(
            'installments/others/{payment}/success',
            [InstallmentController::class, 'othersPaymentSuccess']
        )
            ->name('installments.others.payment.success');



        /*
        |--------------------------------------------------------------------------
        | Customer Donation
        |--------------------------------------------------------------------------
        */


        Route::get(
            'donations/create',
            [CustomerDonationController::class, 'create']
        )
            ->name('donations.create');


        Route::post(
            'donations',
            [CustomerDonationController::class, 'store']
        )
            ->name('donations.store');


        Route::get(
            'donations/payment/{donationPayment}',
            [CustomerDonationController::class, 'payment']
        )
            ->name('donations.payment');


        Route::post(
            'donations/payment/{donationPayment}/pay',
            [CustomerDonationController::class, 'pay']
        )
            ->name('donations.pay');


        Route::get(
            'donations/success/{donationPayment}',
            [CustomerDonationController::class, 'success']
        )
            ->name('donations.success');


    });



/*
|--------------------------------------------------------------------------
| Development Routes
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Test Components
|--------------------------------------------------------------------------
*/

Route::view(
    '/test-components',
    'test.components'
);



/*
|--------------------------------------------------------------------------
| Test OTP
|--------------------------------------------------------------------------
*/

if (app()->environment('local')) {

    Route::get(
        '/test-otp',
        function (OtpService $otpService) {

            $user = User::first();

            return $otpService->generate($user);

        }
    );

}






