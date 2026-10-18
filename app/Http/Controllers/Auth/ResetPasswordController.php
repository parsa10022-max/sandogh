<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserOtpStatus;
use App\Enums\UserOtpType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends Controller
{
    private const RECOVERY_EXPIRE_MINUTES = 10;

    /**
     * نمایش فرم تعیین رمز جدید
     */
    public function create()
    {
        if (! $this->hasValidRecoverySession(request())) {
            $this->clearRecoverySession(request());

            return redirect()->route('password.request')
                ->withErrors([
                    'mobile' => 'مهلت بازیابی رمز پایان یافته است. دوباره درخواست بدهید.',
                ]);
        }

        return view('auth.reset-password');
    }

    /**
     * ذخیره رمز جدید
     */
    public function update(Request $request)
    {
        if (! $this->hasValidRecoverySession($request)) {
            $this->clearRecoverySession($request);

            return redirect()->route('password.request')
                ->withErrors([
                    'mobile' => 'مهلت بازیابی رمز پایان یافته است. دوباره درخواست بدهید.',
                ]);
        }

        $validated = $request->validate(
            [
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],
            ],
            [
                'password.required' => 'وارد کردن رمز عبور جدید الزامی است.',
                'password.string' => 'رمز عبور جدید باید به صورت متن وارد شود.',
                'password.min' => 'رمز عبور جدید باید حداقل ۸ کاراکتر باشد.',
                'password.confirmed' => 'تکرار رمز عبور جدید با رمز وارد شده مطابقت ندارد.',
            ]
        );

        $userId = (int) $request->session()->get('forgot_password_user_id');
        $otpId = (int) $request->session()->get('forgot_password_otp_id');
        $verifiedAt = (int) $request->session()->get('forgot_password_verified_at');

        try {
            DB::transaction(function () use (
                $userId,
                $otpId,
                $verifiedAt,
                $validated
            ) {
                $user = User::query()
                    ->whereKey($userId)
                    ->lockForUpdate()
                    ->first();

                $otp = UserOtp::query()
                    ->whereKey($otpId)
                    ->where('user_id', $userId)
                    ->where('type', UserOtpType::PASSWORD_RESET)
                    ->where('status', UserOtpStatus::VERIFIED)
                    ->whereNotNull('verified_at')
                    ->lockForUpdate()
                    ->first();

                if (
                    ! $user ||
                    ! $otp ||
                    $otp->verified_at->timestamp !== $verifiedAt ||
                    $otp->verified_at->lt(now()->subMinutes(self::RECOVERY_EXPIRE_MINUTES))
                ) {
                    throw ValidationException::withMessages([
                        'password' => 'مجوز بازیابی معتبر نیست یا منقضی شده است. دوباره درخواست بدهید.',
                    ]);
                }

                $user->update([
                    'password' => Hash::make($validated['password']),
                ]);

                $otp->update([
                    'status' => UserOtpStatus::CANCELLED,
                    'cancelled_at' => now(),
                ]);
            });
        } catch (ValidationException $exception) {
            $this->clearRecoverySession($request);

            return redirect()->route('password.request')
                ->withErrors($exception->errors());
        }

        $this->clearRecoverySession($request);
        $request->session()->regenerate();

        return redirect()->route('login')->with(
            'success',
            'رمز عبور با موفقیت تغییر کرد. اکنون می‌توانید وارد شوید.'
        );
    }

    /**
     * بررسی مجوز بازیابی
     */
    private function hasValidRecoverySession(Request $request): bool
    {
        if (
            ! $request->session()->get('forgot_password_verified') ||
            ! $request->session()->get('forgot_password_user_id') ||
            ! $request->session()->get('forgot_password_otp_id') ||
            ! $request->session()->get('forgot_password_verified_at')
        ) {
            return false;
        }

        $verifiedAt = (int) $request->session()->get('forgot_password_verified_at');

        return $verifiedAt <= now()->timestamp
            && $verifiedAt >= now()->subMinutes(self::RECOVERY_EXPIRE_MINUTES)->timestamp;
    }

    /**
     * پاک کردن مجوز بازیابی رمز
     */
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
