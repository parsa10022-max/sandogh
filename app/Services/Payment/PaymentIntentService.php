<?php

namespace App\Services\Payment;

use App\Enums\PaymentGateway;
use App\Models\PaymentIntent;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentIntentService
{
    /**
     * ایجاد Payment Intent جدید.
     */
    public function create(
        string $paymentType,
        int $referenceId,
        int $amount,
        string $trackingCode,
        PaymentGateway $gateway,
        ?int $payerUserId = null,
        ?string $gatewayToken = null,
        ?int $expiresInMinutes = 30,
): PaymentIntent {
        if ($referenceId <= 0) {
            throw new RuntimeException('شناسه مرجع پرداخت نامعتبر است.');
        }

        if ($amount <= 0) {
            throw new RuntimeException('مبلغ پرداخت باید بیشتر از صفر باشد.');
        }

        if ($trackingCode === '') {
            throw new RuntimeException('کد پیگیری پرداخت الزامی است.');
        }

        if ($expiresInMinutes !== null && $expiresInMinutes <= 0) {
            throw new RuntimeException('مدت اعتبار پرداخت نامعتبر است.');
        }

        return PaymentIntent::create([
            'payment_type' => $paymentType,
            'reference_id' => $referenceId,
            'amount' => $amount,
            'tracking_code' => $trackingCode,
            'gateway' => $gateway,
            'payer_user_id' => $payerUserId,
            'gateway_token' => $gatewayToken,
            'status' => 'pending',
            'expires_at' => $expiresInMinutes !== null
                ? now()->addMinutes($expiresInMinutes)
                : null,
        ]);
    }

    /**
     * پیدا کردن Intent بر اساس ID.
     */
    public function findById(int $id): PaymentIntent
    {
        return PaymentIntent::query()->findOrFail($id);
    }

    /**
     * پیدا کردن Intent بر اساس نوع و مرجع.
     */
    public function findByReference(
        string $paymentType,
        int $referenceId
    ): PaymentIntent {
        return PaymentIntent::query()
            ->forReference($paymentType, $referenceId)
            ->latest('id')
            ->firstOrFail();
    }

    /**
     * پیدا کردن Intent و قفل کردن رکورد در تراکنش دیتابیس.
     */
    public function findForUpdate(
        string $paymentType,
        int $referenceId
    ): PaymentIntent {
        return PaymentIntent::query()
            ->where('payment_type', $paymentType)
            ->where('reference_id', $referenceId)
            ->latest('id')
            ->lockForUpdate()
            ->firstOrFail();
    }

    /**
     * علامت‌گذاری Intent به عنوان Redirected.
     */
    public function markRedirected(
        PaymentIntent $intent,
        ?string $gatewayToken = null
    ): PaymentIntent {
        $this->ensureStatus($intent, ['pending']);

        $intent->update([
            'status' => 'redirected',
            'gateway_token' => $gatewayToken ?? $intent->gateway_token,
        ]);

        return $intent->refresh();
    }

    /**
     * علامت‌گذاری Intent به عنوان Verifying.
     */
    public function markVerifying(PaymentIntent $intent): PaymentIntent
    {
        $this->ensureStatus($intent, ['redirected']);

        if ($intent->isExpiredByTime()) {
            $this->markExpired($intent);

            throw new RuntimeException('مهلت پرداخت به پایان رسیده است.');
        }

        $intent->update([
            'status' => 'verifying',
        ]);

        return $intent->refresh();
    }

    /**
     * ثبت موفقیت نهایی پرداخت.
     */
    public function markPaid(
        PaymentIntent $intent,
        ?string $transactionId = null,
        ?string $referenceNumber = null,
    ): PaymentIntent {
        $this->ensureStatus($intent, ['verifying']);

        $intent->update([
            'status' => 'paid',
            'gateway_transaction_id' => $transactionId,
            'gateway_reference_number' => $referenceNumber,
            'paid_at' => now(),
        ]);

        return $intent->refresh();
    }

    /**
     * ثبت شکست پرداخت.
     */
    public function markFailed(PaymentIntent $intent): PaymentIntent
    {
        if ($intent->isPaid()) {
            throw new RuntimeException(
                'Payment Intent موفق را نمی‌توان ناموفق کرد.'
            );
        }

        if ($intent->isExpired()) {
            return $intent;
        }

        $intent->update([
            'status' => 'failed',
        ]);

        return $intent->refresh();
    }

    /**
     * منقضی کردن Intent.
     */
    public function markExpired(PaymentIntent $intent): PaymentIntent
    {
        if ($intent->isPaid()) {
            throw new RuntimeException(
                'Payment Intent پرداخت‌شده را نمی‌توان منقضی کرد.'
            );
        }

        $intent->update([
            'status' => 'expired',
        ]);

        return $intent->refresh();
    }

    /**
     * بررسی اینکه Intent برای پرداخت مشخص معتبر است.
     */
    public function validateForPayment(
        PaymentIntent $intent,
        string $paymentType,
        int $referenceId,
        int $amount,
        PaymentGateway $gateway,
    ): void {
        if ($intent->payment_type !== $paymentType) {
            throw new RuntimeException(
                'نوع پرداخت با Payment Intent مطابقت ندارد.'
            );
        }

        if ((int) $intent->reference_id !== $referenceId) {
            throw new RuntimeException(
                'مرجع پرداخت با Payment Intent مطابقت ندارد.'
            );
        }

        if ((int) $intent->amount !== $amount) {
            throw new RuntimeException(
                'مبلغ پرداخت با Payment Intent مطابقت ندارد.'
            );
        }

        if ($intent->gateway !== $gateway) {
            throw new RuntimeException(
                'درگاه پرداخت با Payment Intent مطابقت ندارد.'
            );
        }

        if ($intent->isPaid()) {
            throw new RuntimeException(
                'این Payment Intent قبلاً پرداخت شده است.'
            );
        }

        if ($intent->isExpiredByTime()) {
            throw new RuntimeException(
                'مهلت این Payment Intent به پایان رسیده است.'
            );
        }

        if (! in_array(
            $intent->status,
            ['pending', 'redirected', 'verifying'],
            true
        )) {
            throw new RuntimeException(
                'وضعیت Payment Intent برای ادامه پرداخت معتبر نیست.'
            );
        }
    }

    /**
     * اجرای عملیات با قفل روی Intent.
     */
    public function transaction(
        string $paymentType,
        int $referenceId,
        callable $callback
    ): mixed {
        return DB::transaction(function () use (
            $paymentType,
            $referenceId,
            $callback
        ) {
            $intent = $this->findForUpdate(
                $paymentType,
                $referenceId
            );

            return $callback($intent);
        });
    }

    /**
     * بررسی وضعیت مجاز برای تغییر.
     */
    private function ensureStatus(
        PaymentIntent $intent,
        array $allowedStatuses
    ): void {
        if (! in_array($intent->status, $allowedStatuses, true)) {
            throw new RuntimeException(
                sprintf(
                    'تغییر وضعیت Payment Intent از "%s" مجاز نیست.',
                    $intent->status
                )
            );
        }
    }
}
