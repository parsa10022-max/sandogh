<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Loan\LoanController;
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
use App\Http\Controllers\Payment\SavingsTransferCallbackController;




Route::post(
    '/accounts/deposit',
    [DepositController::class, 'store']
)->name('accounts.deposit');



/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->name('login');

Route::get(
    'loans/overdue',
    [LoanController::class, 'overdue']
)->name('loans.overdue');


Route::post('/login', [LoginController::class, 'login'])
    ->name('login.store');


Route::get('/otp', [OtpController::class, 'showVerifyForm'])
    ->name('otp.form');


Route::post('/otp', [OtpController::class, 'verify'])
    ->name('otp.verify');



/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {


    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');


    Route::post('/logout', LogoutController::class)
        ->name('logout');




    /*
    |--------------------------------------------------------------------------
    | Accounts
    |--------------------------------------------------------------------------
    */

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
       | براشت
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

    Route::get(
        '/my-withdrawals',
        [WithdrawalController::class, 'myWithdrawals']
    )->name('withdrawals.mine');

    Route::post(
        '/withdrawals/{withdrawal}/reject',
        [WithdrawalController::class, 'reject']
    )->name('withdrawals.reject');


    /*
    |--------------------------------------------------------------------------
    | LoanRequest
    |--------------------------------------------------------------------------
    */
    Route::resource('loan-requests', LoanRequestController::class)
        ->only([
            'index',
            'create',
            'store',
            'show',
        ]);

    Route::post('loan-requests/{loanRequest}/approve',
        [LoanRequestController::class, 'approve'])
        ->name('loan-requests.approve');


    Route::post('loan-requests/{loanRequest}/reject',
        [LoanRequestController::class, 'reject'])
        ->name('loan-requests.reject');

    Route::put(
        'loan-requests/{loanRequest}/update-review-date',
        [LoanRequestController::class, 'updateReviewDate']
    )
        ->name('loan-requests.update-review-date');



    /*
    |--------------------------------------------------------------------------
    | Customers
    |--------------------------------------------------------------------------
    */


    Route::get(
        'customers/archive',
        [CustomerController::class, 'archive']
    )->name('customers.archive');


    Route::patch(
        'customers/{id}/restore',
        [CustomerController::class, 'restore']
    )->name('customers.restore');


    /*
     | جستجوی مشتری با کد مشتری
     | برای انتخاب ضامن
     */
    Route::get(
        'customers/search-code',
        [CustomerController::class, 'searchByCode']
    )->name('customers.search.code');


    Route::resource('customers', CustomerController::class);



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
    | Loans
    |--------------------------------------------------------------------------
    */


    Route::post(
        'loans/calculate',
        [LoanController::class, 'calculate']
    )->name('loans.calculate');


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
    | Payments
    |--------------------------------------------------------------------------
    */


    Route::prefix('payments')
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

});



/*
|--------------------------------------------------------------------------
| Development Routes
|--------------------------------------------------------------------------
*/


Route::view(
    '/test-components',
    'test.components'
);



Route::get('/test-otp', function (OtpService $otpService) {

    $user = User::first();

    return $otpService->generate($user);

});


/*
        |--------------------------------------------------------------------------
        | CustomerSavingsTransfer
        |--------------------------------------------------------------------------
        */




Route::middleware(['auth'])
    ->prefix('customer')
    ->name('customer.')
    ->group(function () {

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
            'savings-transfer/success/{transfer}',
            [PaymentController::class, 'savingsTransferSuccess']
        )->name('savings-transfer.success');

        Route::get(
            'savings-transfer/failed',
            [PaymentController::class, 'savingsTransferFailed']
        )->name('savings-transfer.failed');
    });

Route::post(
    'payments/savings-transfer/callback',
    [
        SavingsTransferCallbackController::class,
        'handle'
    ]
)->name('savings-transfer.callback');
