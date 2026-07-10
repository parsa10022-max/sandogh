<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\DashboardController;
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

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::post('/logout', LogoutController::class)
        ->name('logout');

    Route::resource('customers', CustomerController::class)
        ->except('show');

});

/*
|--------------------------------------------------------------------------
| Development Routes
|--------------------------------------------------------------------------
*/

Route::view('/test-components', 'test.components');

Route::get('/test-otp', function (OtpService $otpService) {

    $user = User::first();

    return $otpService->generate($user);

});
