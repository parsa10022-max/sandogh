<?php

namespace App\Services\Payment\Gateways;

use App\Enums\FakeGatewayResult;
use Illuminate\Support\Str;

class FakeGateway implements GatewayInterface
{
    /**
     * ارسال کاربر به صفحه تست درگاه
     */
    public function request(array $paymentData): array
    {
        $token = Str::uuid()->toString();

        return [
            'success' => true,

            'token' => $token,

            'redirect_url' => route('payments.fake', [

                'token' => $token,

                'payment_type' => $paymentData['payment_type'],

                'reference_id' => $paymentData['reference_id'],

                'tracking_code' => $paymentData['tracking_code'],

                'amount' => $paymentData['amount'],



            ]),

            'message' => null,
        ];
    }

    /**
     * شبیه‌سازی پاسخ بانک
     */
    public function verify(array $callbackData): array
    {
        $result = FakeGatewayResult::tryFrom(
                $callbackData['result'] ?? FakeGatewayResult::SUCCESS->value
            ) ?? FakeGatewayResult::SUCCESS;

        return match ($result) {

            FakeGatewayResult::SUCCESS => [

                'success' => true,

                'transaction_id' => 'TX' . strtoupper(Str::random(12)),

                'reference_number' => now()->format('YmdHis') . rand(1000, 9999),

                'card_number' => '603799******1234',

                'message' => 'پرداخت با موفقیت انجام شد.',

            ],

            FakeGatewayResult::FAILED => [

                'success' => false,

                'message' => 'پرداخت ناموفق بود.',

            ],

            FakeGatewayResult::CANCELED => [

                'success' => false,

                'message' => 'کاربر پرداخت را لغو کرد.',

            ],

            FakeGatewayResult::TIMEOUT => [

                'success' => false,

                'message' => 'مهلت پرداخت به پایان رسید.',

            ],

            FakeGatewayResult::VERIFY_FAILED => [

                'success' => false,

                'message' => 'بانک پرداخت را تأیید نکرد.',

            ],

            FakeGatewayResult::DUPLICATE_CALLBACK => [

                'success' => false,

                'message' => 'Callback تکراری دریافت شد.',

            ],

            FakeGatewayResult::INVALID_AMOUNT => [

                'success' => false,

                'message' => 'مبلغ پرداخت نامعتبر است.',

            ],

            FakeGatewayResult::INVALID_TOKEN => [

                'success' => false,

                'message' => 'توکن پرداخت نامعتبر است.',

            ],

            FakeGatewayResult::INVALID_SIGNATURE => [

                'success' => false,

                'message' => 'امضای دیجیتال نامعتبر است.',

            ],

            FakeGatewayResult::CONNECTION_ERROR => [

                'success' => false,

                'message' => 'ارتباط با درگاه بانکی برقرار نشد.',

            ],
        };
    }
}
