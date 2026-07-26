<?php

namespace App\Services\Payment\Gateways;

interface GatewayInterface
{
    /**
     * ارسال درخواست پرداخت به بانک
     *
     * @param array $paymentData
     * @return array
     */
    public function request(array $paymentData): array;

    /**
     * تایید نتیجه پرداخت
     *
     * @param array $callbackData
     * @return array
     */
    public function verify(array $callbackData): array;
}
