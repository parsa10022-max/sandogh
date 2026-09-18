<?php


namespace App\Services;

use App\Enums\CustomerActivationOtpStatus;
use App\Models\Customer;
use App\Models\CustomerActivationOtp;
use Illuminate\Http\Request;
class CustomerActivationOtpService
{
    private const OTP_EXPIRE_MINUTES = 2;

    private const MAX_ATTEMPTS = 5;

    /**
     * ایجاد OTP فعال‌سازی حساب
     */
    public function generate(
        Customer $customer,
        Request $request
    ): CustomerActivationOtp {
        // OTPهای قبلی این عضو لغو شوند
        $this->cancel($customer);

        $code = $this->generateCode();

        return CustomerActivationOtp::create([
            'customer_id' => $customer->id,

            'mobile' => $customer->mobile,

            'code' => $code,

            'status' => CustomerActivationOtpStatus::PENDING,

            'attempts' => 0,

            'expires_at' => now()->addMinutes(
                self::OTP_EXPIRE_MINUTES
            ),

            'ip_address' => $request->ip(),

            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * تأیید OTP
     */
    public function verify(
        Customer $customer,
        string $code
    ): bool {
        $otp = CustomerActivationOtp::query()
            ->forCustomer($customer->id)
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
            $this->cancelOtp($otp);

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
            'status' => CustomerActivationOtpStatus::VERIFIED,
            'verified_at' => now(),
        ]);

        return true;
    }

    /**
     * لغو OTPهای فعال عضو
     */
    public function cancel(Customer $customer): void
    {
        CustomerActivationOtp::query()
            ->forCustomer($customer->id)
            ->pending()
            ->notCancelled()
            ->update([
                'status' => CustomerActivationOtpStatus::CANCELLED,
                'cancelled_at' => now(),
            ]);
    }

    /**
     * آخرین OTP معتبر عضو
     */
    public function getLastPendingOtp(
        Customer $customer
    ): ?CustomerActivationOtp {
        return CustomerActivationOtp::query()
            ->forCustomer($customer->id)
            ->valid()
            ->latest('id')
            ->first();
    }

    /**
     * تولید کد ۶ رقمی
     */
    private function generateCode(): string
    {
        return (string) random_int(100000, 999999);
    }

    /**
     * لغو یک OTP
     */
    private function cancelOtp(
        CustomerActivationOtp $otp
    ): void {
        $otp->update([
            'status' => CustomerActivationOtpStatus::CANCELLED,
            'cancelled_at' => now(),
        ]);
    }
}
