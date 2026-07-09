<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\OtpService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class LoginController extends Controller
{
    public function __construct(
        private OtpService $otpService
    )
    {
    }

    /**
     * نمایش فرم ورود
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * بررسی نام کاربری و رمز عبور
     */
    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $user = User::query()
            ->where('username', $data['username'])
            ->first();

        if (!$user) {
            return $this->invalidCredentials();
        }

        if (!Hash::check($data['password'], $user->password)) {
            return $this->invalidCredentials();
        }

        $this->otpService->generate($user);
        session([
            'login_user_id' => $user->id,
        ]);

        return redirect()->route('otp.form');
    }

    private function invalidCredentials()
    {
        return back()->withErrors([
            'username' => 'نام کاربری یا رمز عبور اشتباه است.',
        ]);
    }
}
