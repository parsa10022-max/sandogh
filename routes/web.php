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
