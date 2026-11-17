<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function __construct(
        private OtpService $otpService
    ) {
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $user = User::query()
            ->where('username', $data['username'])
            ->first();

        if (! $user) {
            return $this->invalidCredentials();
        }

        if (! Hash::check($data['password'], $user->password)) {
            return $this->invalidCredentials();
        }

        if ($user->status !== UserStatus::ACTIVE) {
            return $this->invalidCredentials();
        }

        session()->forget('login_user_id');

        // بررسی پایان عضویت اینترنتی فقط برای مشتریان
        if ($user->role === UserRole::CUSTOMER) {
            if (
                ! $user->customer_id ||
                ! $user->customer ||
                ! $user->created_at ||
                now()->greaterThanOrEqualTo(
                    $user->created_at->copy()->addYears(2)
                )
            ) {
                return back()
                    ->withErrors([
                        'username' => '.مدت عضویت اینترنتی شما به پایان رسیده است. برای تمدید دسترسی، لطفاً با صندوق قرض‌الحسنه به شماره ۰۷۱-۳۲۶۱۳۲۱۷ تماس بگیرید.',
                    ]);
            }
        }

        $this->otpService->generate($user);

        session([
            'login_user_id' => $user->id,
        ]);

        return redirect()
            ->route('otp.form');
    }

    private function invalidCredentials()
    {
        return back()
            ->withErrors([
                'username' => 'نام کاربری یا رمز عبور اشتباه است.',
            ]);
    }
}
