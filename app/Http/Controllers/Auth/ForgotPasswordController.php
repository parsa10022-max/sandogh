<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserOtpType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OtpRequest;
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
        $request->merge([
            'mobile' => $request->mobile
                ? preg_replace(
                    '/\D/u',
                    '',
                    strtr($request->mobile, [
                        '۰' => '0',
                        '۱' => '1',
                        '۲' => '2',
                        '۳' => '3',
                        '۴' => '4',
                        '۵' => '5',
                        '۶' => '6',
                        '۷' => '7',
                        '۸' => '8',
                        '۹' => '9',
                        '٠' => '0',
                        '١' => '1',
                        '٢' => '2',
                        '٣' => '3',
                        '٤' => '4',
                        '٥' => '5',
                        '٦' => '6',
                        '٧' => '7',
                        '٨' => '8',
                        '٩' => '9',
                    ])
                )
                : null,
        ]);

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

        $this->otpService->generate(
            $user,
            UserOtpType::PASSWORD_RESET,
            $user->mobile,     $request );

        session([
            'forgot_password_user_id' => $user->id,
        ]);

        return redirect()
            ->route('password.otp.form')
            ->with(
                'success',
                'کد تأیید برای شماره موبایل شما ارسال شد.'
            );
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

        return view(
            'auth.forgot-password-otp',
            [
                'testOtp' => config('app.debug')
                    ? session('test_otp')
                    : null,
            ]
        );
    }
    /**
     * تأیید OTP
     */
    public function verifyOtp(OtpRequest $request)
    {
        $user = $this->getUser();

        if (! $user) {
            return redirect()
                ->route('password.request');
        }

        if (! $this->otpService->verify(
            $user,
            $request->validated('code'),
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
