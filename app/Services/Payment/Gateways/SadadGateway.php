<?php

namespace App\Services\Payment\Gateways;

use Illuminate\Http\Request;

class SadadGateway implements GatewayInterface
{
    /**
     * ارسال درخواست پرداخت به سداد
     */
    public function request(array $paymentData): array
    {
        // TODO:
        // 1. ارسال درخواست به API سداد
        // 2. دریافت Token
        // 3. ساخت آدرس Redirect

        return [
            'success' => false,
            'redirect_url' => null,
            'token' => null,
            'message' => 'Sadad gateway is not implemented.',
        ];
    }

    /**
     * تایید پرداخت
     */
    public function verify(Request $request): array
    {
        // TODO:
        // 1. ارسال درخواست Verify به سداد
        // 2. بررسی نتیجه
        // 3. استخراج اطلاعات تراکنش

        return [
            'success' => false,
            'transaction_id' => null,
            'reference_number' => null,
            'card_number' => null,
            'message' => 'Sadad gateway is not implemented.',
        ];
    }
}
