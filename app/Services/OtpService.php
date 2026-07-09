<?php

namespace App\Services;

use App\Enums\UserOtpStatus;
use App\Enums\UserOtpType;
use App\Models\User;
use App\Models\UserOtp;

class OtpService

{
    private const OTP_EXPIRE_MINUTES = 2;

    public function generate(User $user): UserOtp
    {
        // 1- لغو OTPهای قبلی
        $this->cancel($user);

        // 2- تولید کد
        $code = $this->generateCode();

        // 3- ذخیره در دیتابیس

        $otp = $this->createOtp($user, $code);

        // 4- ارسال (فعلاً خالی)
        // $this->send($otp);

        return $otp;
    }

    public function verify(User $user, string $code): bool
    {
        $otp = UserOtp::query()
            ->forUser($user->id)
            ->valid()
            ->latest()
            ->first();

        if (! $otp) {
            return false;
        }

        if ($otp->code !== $code) {
            return false;
        }

        $otp->status = UserOtpStatus::VERIFIED;
        $otp->verified_at = now();

        $otp->save();
        return true;
    }

    public function cancel(User $user): void
    {
        UserOtp::query()
            ->forUser($user->id)
            ->pending()
            ->notCancelled()
            ->update([
                'status' => UserOtpStatus::CANCELLED,
                'cancelled_at' => now(),
            ]);
    }
    public function getLastPendingOtp(User $user): ?UserOtp
    {
        return UserOtp::query()
            ->forUser($user->id)
            ->valid()
            ->latest()
            ->first();
    }

    public function send(UserOtp $otp): void
    {
        //
    }

    public function canRequest(User $user): bool
    {
        //
    }

    private function generateCode(): string
    {
        return (string) random_int(100000, 999999);
    }
    private function createOtp(User $user, string $code): UserOtp
    {
        return UserOtp::create([
            'user_id'     => $user->id,
            'mobile'      => $user->mobile,
            'code'        => $code,
            'type'        => UserOtpType::LOGIN,
            'status'      => UserOtpStatus::PENDING,
            'attempts'    => 0,
            'expires_at'  => now()->addMinutes(self::OTP_EXPIRE_MINUTES),
        ]);
    }

}
