<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserOtpType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Auth\OtpRequest;
use App\Services\OtpService;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    private const GENERIC_MESSAGE =
        'اگر شماره موبایل در سامانه ثبت شده باشد، مراحل بازیابی رمز ادامه پیدا می‌کند.';

    public function __construct(
        private readonly OtpService $otpService
    ) {
    }

    public function create()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $this->clearRecoverySession($request);

        $mobile = $request->input('mobile');

        if (is_string($mobile)) {
            $mobile = strtr($mobile, [
                '۰' => '0', '۱' => '1', '۲' => '2',
                '۳' => '3', '۴' => '4', '۵' => '5',
                '۶' => '6', '۷' => '7', '۸' => '8',
                '۹' => '9',
                '٠' => '0', '١' => '1', '٢' => '2',
                '٣' => '3', '٤' => '4', '٥' => '5',
                '٦' => '6', '٧' => '7', '٨' => '8',
                '٩' => '9',
            ]);

            $mobile = preg_replace('/\D+/', '', $mobile);
        }

        $validated = validator(
            ['mobile' => $mobile],
            [
                'mobile' => ['required', 'string', 'max:20'],
            ],
            [
                'mobile.required' => 'وارد کردن شماره موبایل الزامی است.',
                'mobile.string' => 'شماره موبایل معتبر نیست.',
                'mobile.max' => 'شماره موبایل معتبر نیست.',
            ]
        )->validate();

        $user = User::query()
            ->where('mobile', $validated['mobile'])
            ->first();

        if (! $user) {
            $request->session()->put('forgot_password_attempt', true);

            return redirect()
                ->route('password.otp.form')
                ->with('success', self::GENERIC_MESSAGE);
        }

        $otp = $this->otpService->generate(
            $user,
            UserOtpType::PASSWORD_RESET,
            $user->mobile,
            $request
        );

        $request->session()->put([
            'forgot_password_user_id' => $user->id,
            'forgot_password_otp_id' => $otp->id,
            'forgot_password_attempt' => true,
        ]);

        return redirect()
            ->route('password.otp.form')
            ->with('success', self::GENERIC_MESSAGE);
    }

    public function showOtpForm(Request $request)
    {
        $user = $this->getUser($request);
        $otpId = $request->session()->get('forgot_password_otp_id');
        $isRecoveryAttempt = $request->session()->get(
            'forgot_password_attempt',
            false
        );

        if (
            ! $isRecoveryAttempt ||
            (
                ! $user ||
                ! $otpId
            )
        ) {
            if (! $isRecoveryAttempt) {
                $this->clearRecoverySession($request);

                return redirect()->route('password.request');
            }
        }

        return view('auth.forgot-password-otp', [
            'testOtp' => $user && $otpId && config('app.debug')
                ? $request->session()->get('test_otp')
                : null,
        ]);
    }

    public function verifyOtp(OtpRequest $request)
    {
        $user = $this->getUser($request);
        $otpId = $request->session()->get('forgot_password_otp_id');

        if (! $user || ! $otpId) {
            return back()->withErrors([
                'code' => 'کد تأیید صحیح نیست یا منقضی شده است.',
            ]);
        }

        $verified = $this->otpService->verify(
            $user,
            $request->validated('code'),
            UserOtpType::PASSWORD_RESET,
            (int) $otpId
        );

        if (! $verified) {
            return back()->withErrors([
                'code' => 'کد تأیید صحیح نیست یا منقضی شده است.',
            ]);
        }

        $request->session()->put([
            'forgot_password_verified' => true,
            'forgot_password_verified_at' => now()->timestamp,
        ]);

        return redirect()->route('password.reset');
    }

    private function getUser(Request $request): ?User
    {
        $userId = $request->session()->get('forgot_password_user_id');

        if (! $userId) {
            return null;
        }

        return User::find($userId);
    }

    private function clearRecoverySession(Request $request): void
    {
        $request->session()->forget([
            'forgot_password_user_id',
            'forgot_password_otp_id',
            'forgot_password_verified',
            'forgot_password_verified_at',
            'forgot_password_attempt',
        ]);
    }
}
