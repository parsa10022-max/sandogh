<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Services\OtpService;
use App\Http\Controllers\Auth\LoginController;

use App\Http\Controllers\Auth\OtpController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LogoutController;
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::post('/logout', LogoutController::class)
        ->name('logout');

});

Route::get('/otp', [OtpController::class, 'showVerifyForm'])
    ->name('otp.form');

Route::post('/otp', [OtpController::class, 'verify'])
    ->name('otp.verify');

Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->name('login');

Route::post('/login', [LoginController::class, 'login'])
    ->name('login.store');


Route::get('/', function () {
    return view('welcome');
});
Route::view('/test-components', 'test.components');

Route::get('/test-otp', function (OtpService $otpService) {

    $user = User::first();

    return $otpService->generate($user);

});
