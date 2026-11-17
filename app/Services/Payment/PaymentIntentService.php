<?php

namespace App\Services\Payment;

use App\Enums\PaymentGateway;
use App\Models\DonationPayment;
use App\Models\Installment;
use App\Models\PaymentIntent;
use App\Models\SavingsTransfer;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentIntentService
{
    private const ACTIVE_STATUSES = [
        'pending',
        'redirected',
        'verifying',
        'review_required',
        'paid',
    ];

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
        if ($paymentType === '') {
            throw new RuntimeException('نوع پرداخت الزامی است.');
        }

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

        $paymentTypes = $this->getReferenceTypes($paymentType);

        return DB::transaction(function () use (
            $paymentType,
            $paymentTypes,
            $referenceId,
            $amount,
            $trackingCode,
            $gateway,
            $payerUserId,
            $gatewayToken,
            $expiresInMinutes,
        ) {
            // ابتدا مرجع قفل می‌شود.
            $this->lockReference($paymentType, $referenceId);

            // فقط درخواست‌های شروع‌شده و هدایت‌شده منقضی می‌شوند.
            PaymentIntent::query()
                ->whereIn('payment_type', $paymentTypes)
                ->where('reference_id', $referenceId)
                ->whereIn('status', ['pending', 'redirected'])
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', now())
                ->update([
                    'status' => 'expired',
                    'updated_at' => now(),
                ]);

            $existingIntent = PaymentIntent::query()
                ->whereIn('payment_type', $paymentTypes)
                ->where('reference_id', $referenceId)
                ->whereIn('status', self::ACTIVE_STATUSES)
                ->lockForUpdate()
                ->first();

            if ($existingIntent) {
                throw new RuntimeException(
                    'برای این مرجع، یک درخواست پرداخت فعال یا تعیین‌تکلیف‌شده وجود دارد.'
                );
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
        });
    }

    /**
     * قفل رکورد مرجع بر اساس نوع پرداخت.
     */
    private function lockReference(
        string $paymentType,
        int $referenceId
    ): void {
        $query = match ($paymentType) {
            'installment',
            'installment_other' => Installment::query(),

            'donation_customer',
            'donation_public' => DonationPayment::query(),

            'savings_transfer' => SavingsTransfer::query(),

            default => throw new RuntimeException(
                'نوع پرداخت برای قفل‌گذاری مرجع پشتیبانی نمی‌شود.'
            ),
        };

        $reference = $query
            ->lockForUpdate()
            ->find($referenceId);

        if (! $reference) {
            throw new RuntimeException('رکورد مرجع پرداخت پیدا نشد.');
        }
    }

    /**
     * پیدا کردن Intent بر اساس ID.
     */
    public function findById(int $id): PaymentIntent
    {
        return PaymentIntent::query()->findOrFail($id);
    }

    /**
     * پیدا کردن Intent بر اساس ID و قفل رکورد.
     * فقط داخل تراکنش دیتابیس استفاده شود.
     */
    public function findByIdForUpdate(int $id): PaymentIntent
    {
        return PaymentIntent::query()
            ->lockForUpdate()
            ->findOrFail($id);
    }

    /**
     * پیدا کردن Intent بر اساس نوع و مرجع.
     */
    public function findByReference(
        string $paymentType,
        int $referenceId
    ): PaymentIntent {
        return PaymentIntent::query()
            ->whereIn(
                'payment_type',
                $this->getReferenceTypes($paymentType)
            )
            ->where('reference_id', $referenceId)
            ->latest('id')
            ->firstOrFail();
    }

    /**
     * پیدا کردن و قفل کردن Intent بر اساس نوع و مرجع.
     * باید داخل تراکنش و پس از قفل مرجع اجرا شود.
     */
    public function findForUpdate(
        string $paymentType,
        int $referenceId
    ): PaymentIntent {
        return PaymentIntent::query()
            ->whereIn(
                'payment_type',
                $this->getReferenceTypes($paymentType)
            )
            ->where('reference_id', $referenceId)
            ->latest('id')
            ->lockForUpdate()
            ->firstOrFail();
    }

    /**
     * تغییر وضعیت به Redirected و ثبت توکن.
     */
    public function markRedirected(
        PaymentIntent $intent,
        ?string $gatewayToken = null
    ): PaymentIntent {
        return $this->withLockedIntent(
            $intent->id,
            function (PaymentIntent $lockedIntent) use ($gatewayToken) {
                $this->ensureStatus($lockedIntent, ['pending']);

                if (
                    ! is_string($gatewayToken)
                    || trim($gatewayToken) === ''
                ) {
                    throw new RuntimeException(
                        'توکن معتبر درگاه برای هدایت به صفحه پرداخت الزامی است.'
                    );
                }

                $lockedIntent->update([
                    'status' => 'redirected',
                    'gateway_token' => $gatewayToken,
                ]);

                return $lockedIntent->fresh();
            }
        );
    }

    /**
     * تغییر وضعیت به Verifying.
     *
     * این متد باید پیش از فراخوانی تأیید درگاه اجرا شود.
     */
    public function markVerifying(
        PaymentIntent $intent
    ): PaymentIntent {
        $expired = false;

        $result = $this->withLockedIntent(
            $intent->id,
            function (PaymentIntent $lockedIntent) use (&$expired) {
                $this->ensureStatus($lockedIntent, ['redirected']);

                if ($lockedIntent->isExpiredByTime()) {
                    $lockedIntent->update([
                        'status' => 'expired',
                    ]);

                    $expired = true;

                    return $lockedIntent->fresh();
                }

                $lockedIntent->update([
                    'status' => 'verifying',
                ]);

                return $lockedIntent->fresh();
            }
        );

        if ($expired) {
            throw new RuntimeException(
                'مهلت پرداخت به پایان رسیده است.'
            );
        }

        return $result;
    }

    /**
     * ثبت موفقیت نهایی پرداخت.
     */
    public function markPaid(
        PaymentIntent $intent,
        ?string $transactionId = null,
        ?string $referenceNumber = null,
    ): PaymentIntent {
        return $this->withLockedIntent(
            $intent->id,
            function (PaymentIntent $lockedIntent) use (
                $transactionId,
                $referenceNumber
            ) {
                if ($lockedIntent->isPaid()) {
                    return $lockedIntent;
                }

                $this->ensureStatus($lockedIntent, ['verifying']);

                $lockedIntent->update([
                    'status' => 'paid',
                    'gateway_transaction_id' => $transactionId,
                    'gateway_reference_number' => $referenceNumber,
                    'paid_at' => now(),
                ]);

                return $lockedIntent->fresh();
            }
        );
    }

    /**
     * انتقال پرداخت موفق یا مشکوک به بررسی مالی.
     */
    public function markReviewRequired(
        PaymentIntent $intent,
        ?string $transactionId = null,
        ?string $referenceNumber = null,
    ): PaymentIntent {
        return $this->withLockedIntent(
            $intent->id,
            function (PaymentIntent $lockedIntent) use (
                $transactionId,
                $referenceNumber
            ) {
                if ($lockedIntent->isPaid()) {
                    throw new RuntimeException(
                        'Payment Intent پرداخت‌شده را نمی‌توان به بررسی مالی منتقل کرد.'
                    );
                }

                if ($lockedIntent->status === 'review_required') {
                    return $lockedIntent;
                }

                $this->ensureStatus($lockedIntent, ['verifying']);

                $lockedIntent->update([
                    'status' => 'review_required',
                    'gateway_transaction_id' => $transactionId,
                    'gateway_reference_number' => $referenceNumber,
                ]);

                return $lockedIntent->fresh();
            }
        );
    }

    /**
     * شکست در مرحله شروع پرداخت.
     *
     * فقط pending و redirected قابل تبدیل به failed هستند.
     */
    public function markFailed(
        PaymentIntent $intent
    ): PaymentIntent {
        return $this->withLockedIntent(
            $intent->id,
            function (PaymentIntent $lockedIntent) {
                if (in_array(
                    $lockedIntent->status,
                    ['paid', 'review_required', 'expired', 'failed'],
                    true
                )) {
                    return $lockedIntent;
                }

                $this->ensureStatus(
                    $lockedIntent,
                    ['pending', 'redirected']
                );

                $lockedIntent->update([
                    'status' => 'failed',
                ]);

                return $lockedIntent->fresh();
            }
        );
    }

    /**
     * شکست قطعی تأیید درگاه.
     *
     * فقط پاسخ قطعی منفی درگاه مجاز است؛
     * نه timeout و نه خطای ارتباطی نامشخص.
     */
    public function markVerificationFailed(
        PaymentIntent $intent
    ): PaymentIntent {
        return $this->withLockedIntent(
            $intent->id,
            function (PaymentIntent $lockedIntent) {
                if ($lockedIntent->isFailed()) {
                    return $lockedIntent;
                }

                $this->ensureStatus($lockedIntent, ['verifying']);

                $lockedIntent->update([
                    'status' => 'failed',
                ]);

                return $lockedIntent->fresh();
            }
        );
    }

    /**
     * منقضی کردن Intent.
     *
     * وضعیت verifying هرگز با این متد منقضی نمی‌شود.
     */
    public function markExpired(
        PaymentIntent $intent
    ): PaymentIntent {
        return $this->withLockedIntent(
            $intent->id,
            function (PaymentIntent $lockedIntent) {
                if (in_array(
                    $lockedIntent->status,
                    ['paid', 'review_required', 'failed', 'expired'],
                    true
                )) {
                    return $lockedIntent;
                }

                $this->ensureStatus(
                    $lockedIntent,
                    ['pending', 'redirected']
                );

                $lockedIntent->update([
                    'status' => 'expired',
                ]);

                return $lockedIntent->fresh();
            }
        );
    }

    /**
     * بررسی اعتبار Intent برای شروع تأیید پرداخت.
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

        if ($intent->status !== 'redirected') {
            throw new RuntimeException(
                'وضعیت Payment Intent برای تأیید پرداخت معتبر نیست.'
            );
        }
    }

    /**
     * اجرای عملیات با قفل مرجع و سپس قفل Intent.
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
            $this->lockReference($paymentType, $referenceId);

            $intent = $this->findForUpdate(
                $paymentType,
                $referenceId
            );

            return $callback($intent);
        });
    }

    /**
     * اجرای عملیات با قفل مرجع و سپس قفل Intent مشخص.
     */
    public function transactionById(
        int $paymentIntentId,
        callable $callback
    ): mixed {
        return DB::transaction(function () use (
            $paymentIntentId,
            $callback
        ) {
            // خواندن اولیه فقط برای تشخیص نوع و شناسه مرجع است.
            $initialIntent = $this->findById($paymentIntentId);

            $paymentType = $initialIntent->payment_type;
            $referenceId = (int) $initialIntent->reference_id;

            // ابتدا مرجع قفل می‌شود.
            $this->lockReference($paymentType, $referenceId);

            // سپس Intent قفل می‌شود.
            $intent = $this->findByIdForUpdate($paymentIntentId);

            if (
                $intent->payment_type !== $paymentType
                || (int) $intent->reference_id !== $referenceId
            ) {
                throw new RuntimeException(
                    'مرجع Payment Intent در حین عملیات تغییر کرده است.'
                );
            }

            return $callback($intent);
        });
    }

    /**
     * اجرای تغییر وضعیت با ترتیب قفل ثابت:
     * مرجع ← PaymentIntent
     */
    private function withLockedIntent(
        int $intentId,
        callable $callback
    ): mixed {
        // خواندن اولیه فقط برای تشخیص مرجع است.
        $initialIntent = $this->findById($intentId);

        $paymentType = $initialIntent->payment_type;
        $referenceId = (int) $initialIntent->reference_id;

        return DB::transaction(function () use (
            $intentId,
            $paymentType,
            $referenceId,
            $callback
        ) {
            // ترتیب قفل در تمام تغییر وضعیت‌ها یکسان است.
            $this->lockReference($paymentType, $referenceId);

            $lockedIntent = $this->findByIdForUpdate($intentId);

            // بررسی عدم تغییر مرجع بین خواندن اولیه و قفل.
            if (
                $lockedIntent->payment_type !== $paymentType
                || (int) $lockedIntent->reference_id !== $referenceId
            ) {
                throw new RuntimeException(
                    'مرجع Payment Intent در حین عملیات تغییر کرده است.'
                );
            }

            return $callback($lockedIntent);
        });
    }

    /**
     * تعیین انواع پرداخت دارای مرجع مشترک.
     */
    private function getReferenceTypes(
        string $paymentType
    ): array {
        return match ($paymentType) {
            'installment',
            'installment_other' => [
                'installment',
                'installment_other',
            ],

            'donation_customer',
            'donation_public' => [
                'donation_customer',
                'donation_public',
            ],

            'savings_transfer' => [
                'savings_transfer',
            ],

            default => throw new RuntimeException(
                'نوع پرداخت پشتیبانی نمی‌شود.'
            ),
        };
    }

    /**
     * بررسی وضعیت مجاز برای تغییر.
     */
    private function ensureStatus(
        PaymentIntent $intent,
        array $allowedStatuses
    ): void {
        if (! in_array(
            $intent->status,
            $allowedStatuses,
            true
        )) {
            throw new RuntimeException(
                sprintf(
                    'تغییر وضعیت Payment Intent از "%s" مجاز نیست.',
                    $intent->status
                )
            );
        }
    }
}
