<?php

namespace App\Services;

use App\Enums\UserOtpStatus;
use App\Enums\UserOtpType;
use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Http\Request;

class OtpService
{
    private const OTP_EXPIRE_MINUTES = 2;

    private const MAX_ATTEMPTS = 5;

    /**
     * ایجاد OTP
     */
    public function generate(
        User $user,
        UserOtpType $type = UserOtpType::LOGIN,
        ?string $mobile = null,
        ?Request $request = null
    ): UserOtp {
        // فقط OTPهای همان نوع لغو شوند
        $this->cancel($user, $type);

        $code = $this->generateCode();

        return $this->createOtp(
            $user,
            $code,
            $type,
            $mobile,
            $request
        );
    }

    /**
     * تأیید OTP
     */
    public function verify(
        User $user,
        string $code,
        UserOtpType $type = UserOtpType::LOGIN
    ): bool {
        $otp = UserOtp::query()
            ->forUser($user->id)
            ->where('type', $type)
            ->valid()
            ->latest('id')
            ->first();

        if (! $otp) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | محدودیت تعداد تلاش
        |--------------------------------------------------------------------------
        */

        if ($otp->attempts >= self::MAX_ATTEMPTS) {
            $otp->update([
                'status' => UserOtpStatus::CANCELLED,
                'cancelled_at' => now(),
            ]);

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | ثبت تلاش
        |--------------------------------------------------------------------------
        */

        $otp->increment('attempts');

        /*
        |--------------------------------------------------------------------------
        | بررسی کد
        |--------------------------------------------------------------------------
        */

        if ($otp->code !== $code) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | تأیید موفق
        |--------------------------------------------------------------------------
        */

        $otp->update([
            'status' => UserOtpStatus::VERIFIED,
            'verified_at' => now(),
        ]);

        return true;
    }

    /**
     * لغو OTPهای pending
     *
     * اگر type مشخص شود فقط همان نوع لغو می‌شود.
     */
    public function cancel(
        User $user,
        ?UserOtpType $type = null
    ): void {
        $query = UserOtp::query()
            ->forUser($user->id)
            ->pending()
            ->notCancelled();

        if ($type !== null) {
            $query->where('type', $type);
        }

        $query->update([
            'status' => UserOtpStatus::CANCELLED,
            'cancelled_at' => now(),
        ]);
    }

    /**
     * آخرین OTP معتبر
     */
    public function getLastPendingOtp(
        User $user,
        ?UserOtpType $type = null
    ): ?UserOtp {
        $query = UserOtp::query()
            ->forUser($user->id)
            ->valid()
            ->latest('id');

        if ($type !== null) {
            $query->where('type', $type);
        }

        return $query->first();
    }

    /**
     * ارسال OTP
     *
     * فعلاً ارسال پیامک پیاده‌سازی نشده است.
     */
    public function send(UserOtp $otp): void
    {
        // در مرحله اتصال پنل پیامکی تکمیل می‌شود.
    }

    /**
     * امکان درخواست OTP
     */
    public function canRequest(User $user): bool
    {
        return true;
    }

    /**
     * تولید کد ۶ رقمی
     */
    private function generateCode(): string
    {
        return (string) random_int(100000, 999999);
    }

    /**
     * ذخیره OTP
     */
    private function createOtp(
        User $user,
        string $code,
        UserOtpType $type,
        ?string $mobile = null,
        ?Request $request = null
    ): UserOtp {
        return UserOtp::create([
            'user_id' => $user->id,

            'mobile' => $mobile ?? $user->mobile,

            'code' => $code,

            'type' => $type,

            'status' => UserOtpStatus::PENDING,

            'attempts' => 0,

            'expires_at' => now()->addMinutes(
                self::OTP_EXPIRE_MINUTES
            ),

            'ip_address' => $request?->ip(),

            'user_agent' => $request?->userAgent(),
        ]);
    }
}
