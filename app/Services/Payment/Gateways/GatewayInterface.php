<?php

namespace App\Services\Payment\Gateways;

interface GatewayInterface
{
    /**
     * ارسال درخواست پرداخت به درگاه بانکی.
     *
     * قرارداد پاسخ:
     * - success=true: درخواست پرداخت با موفقیت ایجاد شده است.
     * - success=false: درگاه به‌صورت قطعی درخواست را رد کرده است.
     * - در صورت نامشخص بودن نتیجه (مانند قطع ارتباط یا timeout)،
     *   باید Exception ایجاد شود؛ نه اینکه success=false برگردد.
     *
     * @param array $paymentData
     * @return array
     *
     * @throws \Throwable در صورت نامشخص بودن نتیجه درخواست
     */
    public function request(array $paymentData): array;

    /**
     * تأیید نتیجه پرداخت از درگاه بانکی.
     *
     * قرارداد پاسخ:
     * - success=true: پرداخت توسط درگاه تأیید شده است.
     * - success=false: درگاه به‌صورت قطعی پرداخت را رد کرده است.
     * - در صورت نامشخص بودن نتیجه (مانند خطای ارتباطی یا timeout)،
     *   باید Exception ایجاد شود؛ نه اینکه success=false برگردد.
     *
     * @param array $callbackData
     * @return array
     *
     * @throws \Throwable در صورت نامشخص بودن نتیجه تأیید
     */
    public function verify(array $callbackData): array;
}
