<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserOtpType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private OtpService $otpService
    ) {
    }

    /**
     * نمایش فرم فراموشی رمز عبور
     */
    public function create()
    {
        return view('auth.forgot-password');
    }

    /**
     * ارسال OTP برای بازیابی رمز عبور
     */
    public function sendOtp(Request $request)
    {
        $validated = $request->validate(
            [
                'mobile' => [
                    'required',
                    'string',
                    'max:20',
                ],
            ],
            [
                'mobile.required' =>
                    'وارد کردن شماره موبایل الزامی است.',

                'mobile.string' =>
                    'شماره موبایل وارد شده معتبر نیست.',

                'mobile.max' =>
                    'شماره موبایل وارد شده معتبر نیست.',
            ]
        );

        $user = User::query()
            ->where('mobile', $validated['mobile'])
            ->first();

        if (! $user) {
            return back()
                ->withErrors([
                    'mobile' =>
                        'کاربری با این شماره موبایل پیدا نشد.',
                ])
                ->withInput();
        }

        $otp = $this->otpService->generate(
            $user,
            UserOtpType::PASSWORD_RESET,
            $user->mobile,
            $request
        );

        session([
            'forgot_password_user_id' => $user->id,
        ]);

        return redirect()
            ->route('password.otp.form')
            ->with('success', 'کد تأیید برای شماره موبایل شما ارسال شد.');
    }

    /**
     * نمایش فرم OTP
     */
    public function showOtpForm()
    {
        $user = $this->getUser();

        if (! $user) {
            return redirect()
                ->route('password.request');
        }

        $otp = null;

        if (config('app.debug')) {
            $otp = $this->otpService->getLastPendingOtp(
                $user,
                UserOtpType::PASSWORD_RESET
            );
        }

        return view(
            'auth.forgot-password-otp',
            compact('otp')
        );
    }

    /**
     * تأیید OTP
     */
    public function verifyOtp(Request $request)
    {
        $user = $this->getUser();

        if (! $user) {
            return redirect()
                ->route('password.request');
        }

        $validated = $request->validate(
            [
                'code' => [
                    'required',
                    'digits:6',
                ],
            ],
            [
                'code.required' =>
                    'وارد کردن کد تأیید الزامی است.',

                'code.digits' =>
                    'کد تأیید باید ۶ رقم باشد.',
            ]
        );

        if (! $this->otpService->verify(
            $user,
            $validated['code'],
            UserOtpType::PASSWORD_RESET
        )) {
            return back()->withErrors([
                'code' =>
                    'کد تأیید صحیح نیست یا منقضی شده است.',
            ]);
        }

        session([
            'forgot_password_verified' => true,
        ]);

        return redirect()
            ->route('password.reset');
    }

    /**
     * کاربر فعلی بازیابی رمز
     */
    private function getUser(): ?User
    {
        $userId = session('forgot_password_user_id');

        if (! $userId) {
            return null;
        }

        return User::find($userId);
    }
}
