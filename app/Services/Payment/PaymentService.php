<?php

namespace App\Services\Payment;

use App\Enums\InstallmentStatus;
use App\Enums\LoanStatus;
use App\Enums\PaymentGateway;
use App\Models\Installment;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Notification;
use App\Models\PaymentIntent;
use App\Services\Payment\Gateways\GatewayInterface;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private readonly GatewayInterface $gateway,
        private readonly TrackingCodeService $trackingCodeService,
        private readonly PaymentIntentService $paymentIntentService,
    ) {}

    /**
     * شروع پرداخت قسط
     */
    public function startPayment(
        Installment $installment,
        bool $allowOthers = false
    ): array {
        $installment->loadMissing('loan');

        $this->validatePayment(
            $installment,
            $allowOthers
        );

        $trackingCode =
            $this->trackingCodeService->generate();

        $payerUserId = auth()->id();

        if (! $payerUserId) {
            throw new \DomainException(
                'کاربر پرداخت‌کننده مشخص نیست.'
            );
        }

        $paymentType = $allowOthers
            ? 'installment_other'
            : 'installment';

        $gateway = PaymentGateway::from(
            config('payment.gateway')
        );

        /*
         * ایجاد Payment Intent
         *
         * اطلاعات اصلی پرداخت در اینجا ثبت می‌شود.
         */
        $paymentIntent = $this->paymentIntentService->create(
            paymentType: $paymentType,
            referenceId: $installment->id,
            amount: (int) $installment->amount,
            trackingCode: $trackingCode,
            gateway: $gateway,
            payerUserId: $payerUserId,
            gatewayToken: null,
            expiresInMinutes: 30,

);

        /*
         * ارسال درخواست به Gateway
         */
        $gatewayResponse = $this->gateway->request([
            'payment_intent_id' => $paymentIntent->id,

            'payment_type' => $paymentType,

            'loan_id' => $installment->loan_id,

            'installment_id' => $installment->id,

            'loan_number' =>
                $installment->loan->loan_number,

            'installment_number' =>
                $installment->installment_number,

            'reference_id' => $installment->id,

            'amount' => (int) $installment->amount,

            'tracking_code' => $trackingCode,

            /*
             * فقط جهت ارسال به Gateway.
             * مرجع اصلی payer همان PaymentIntent است.
             */
            'payer_user_id' => $payerUserId,

            'callback_url' =>
                route('payments.callback'),

            'gateway_token' =>
                $paymentIntent->gateway_token,
        ]);

        if (! ($gatewayResponse['success'] ?? false)) {
            $this->paymentIntentService->markFailed(
                $paymentIntent
            );

            throw new \DomainException(
                $gatewayResponse['message']
                ?? 'ایجاد درخواست پرداخت ناموفق بود.'
            );
        }

        /*
         * Gateway ممکن است توکن خودش را ایجاد کند.
         */
        $gatewayToken =
            $gatewayResponse['token']
            ?? $paymentIntent->gateway_token;

        if (! $gatewayToken) {
            $this->paymentIntentService->markFailed(
                $paymentIntent
            );

            throw new \DomainException(
                'توکن درگاه ایجاد نشد.'
            );
        }

        /*
         * ذخیره توکن Gateway در PaymentIntent
         */
        $paymentIntent->update([
            'gateway_token' => $gatewayToken,
        ]);

        /*
         * انتقال PaymentIntent به وضعیت redirected
         */
        $paymentIntent =
            $this->paymentIntentService->markRedirected(
                $paymentIntent->refresh(),
                $gatewayToken
            );

        /*
         * شناسه PaymentIntent را در پاسخ نگه می‌داریم.
         */
        $gatewayResponse['payment_intent_id'] =
            $paymentIntent->id;

        return $gatewayResponse;
    }

    /**
     * تایید پرداخت
     */
    public function verifyPayment(
        array $callbackData
    ): LoanPayment {
        /*
         * PaymentIntent اولین مرجع Callback است.
         */
        $paymentIntent =
            $this->resolvePaymentIntent($callbackData);

        $paymentType =
            $paymentIntent->payment_type;

        $installmentId =
            (int) $paymentIntent->reference_id;

        $amount =
            (int) $paymentIntent->amount;

        $gateway =
            $paymentIntent->gateway;

        /*
         * اعتبارسنجی PaymentIntent
         */
        $this->paymentIntentService->validateForPayment(
            intent: $paymentIntent,
            paymentType: $paymentType,
            referenceId: $installmentId,
            amount: $amount,
            gateway: $gateway,
        );

        /*
         * PaymentIntent وارد مرحله verifying می‌شود.
         */
        $paymentIntent =
            $this->paymentIntentService->markVerifying(
                $paymentIntent
            );

        /*
         * اکنون Gateway را Verify می‌کنیم.
         */
        $gatewayResponse =
            $this->gateway->verify($callbackData);

        if (! ($gatewayResponse['success'] ?? false)) {
            $this->paymentIntentService->markFailed(
                $paymentIntent
            );

            throw new \DomainException(
                $gatewayResponse['message']
                ?? 'تأیید پرداخت ناموفق بود.'
            );
        }

        /*
         * payer فقط از PaymentIntent
         */
        $payerUserId =
            $paymentIntent->payer_user_id;

        $trackingCode =
            $paymentIntent->tracking_code;

        return DB::transaction(
            function () use (
                $paymentIntent,
                $installmentId,
                $amount,
                $gatewayResponse,
                $payerUserId,
                $trackingCode,
            ) {
                /*
                 * پیدا کردن loan_id
                 */
                $loanId = Installment::query()
                    ->whereKey($installmentId)
                    ->value('loan_id');

                if (! $loanId) {
                    throw new \DomainException(
                        'قسط موردنظر پیدا نشد.'
                    );
                }

                /*
                 * قفل کردن وام
                 */
                $loan = Loan::query()
                    ->with('customer.user')
                    ->lockForUpdate()
                    ->find($loanId);

                if (! $loan) {
                    throw new \DomainException(
                        'وام مربوط به قسط پیدا نشد.'
                    );
                }

                /*
                 * قفل کردن قسط
                 */
                $installment = Installment::query()
                    ->lockForUpdate()
                    ->find($installmentId);

                if (! $installment) {
                    throw new \DomainException(
                        'قسط موردنظر پیدا نشد.'
                    );
                }

                /*
                 * کنترل ارتباط قسط و وام
                 */
                if (
                    (int) $installment->loan_id
                    !== (int) $loan->id
                ) {
                    throw new \DomainException(
                        'قسط با وام مربوطه مطابقت ندارد.'
                    );
                }

                /*
                 * مبلغ واقعی قسط باید با PaymentIntent
                 * یکسان باشد.
                 */
                if (
                    (int) $installment->amount
                    !== $amount
                ) {
                    throw new \DomainException(
                        'مبلغ قسط با Payment Intent مطابقت ندارد.'
                    );
                }

                /*
                 * پرداخت تکراری
                 */
                $existingPayment =
                    LoanPayment::query()
                        ->where(
                            'installment_id',
                            $installment->id
                        )
                        ->first();

                if ($existingPayment) {
                    /*
                     * اگر LoanPayment قبلاً ثبت شده،
                     * دیگر نباید عملیات مالی دوباره انجام شود.
                     */
                    if (
                        ! $paymentIntent->isPaid()
                    ) {
                        $this->paymentIntentService->markPaid(
                            $paymentIntent,
                            $gatewayResponse['transaction_id']
                            ?? null,
                            $gatewayResponse['reference_number']
                            ?? null,
                        );
                    }

                    return $existingPayment;
                }

                /*
                 * وام باید فعال باشد.
                 */
                if (
                    $loan->status
                    !== LoanStatus::ACTIVE
                ) {
                    throw new \DomainException(
                        'این وام فعال نیست.'
                    );
                }

                /*
                 * قسط نباید قبلاً پرداخت شده باشد.
                 */
                if (
                    $installment->status
                    === InstallmentStatus::PAID
                ) {
                    throw new \DomainException(
                        'این قسط قبلاً پرداخت شده است.'
                    );
                }

                /*
                 * بررسی ترتیب اقساط
                 */
                $previousUnpaidExists =
                    Installment::query()
                        ->where(
                            'loan_id',
                            $loan->id
                        )
                        ->where(
                            'installment_number',
                            '<',
                            $installment->installment_number
                        )
                        ->whereIn('status', [
                            InstallmentStatus::PENDING->value,
                            InstallmentStatus::OVERDUE->value,
                        ])
                        ->exists();

                if ($previousUnpaidExists) {
                    throw new \DomainException(
                        'ابتدا باید اقساط قبلی پرداخت شوند.'
                    );
                }

                /*
                 * ثبت LoanPayment
                 */
                $payment =
                    $this->createLoanPayment(
                        installment: $installment,
                        paymentIntent: $paymentIntent,
                        gatewayResponse: $gatewayResponse,
                        trackingCode: $trackingCode,
                        payerUserId: $payerUserId,
                    );

                /*
                 * پرداخت شدن قسط
                 */
                $this->markInstallmentAsPaid(
                    $installment
                );

                /*
                 * موفق شدن PaymentIntent
                 */
                $this->paymentIntentService->markPaid(
                    $paymentIntent,
                    $gatewayResponse['transaction_id']
                    ?? null,
                    $gatewayResponse['reference_number']
                    ?? null,
                );

                /*
                 * بررسی پایان وام
                 */
                $this->finishLoanIfNeeded(
                    $loan
                );

                /*
                 * ارسال اعلان
                 */
                $this->sendPaymentNotifications(
                    $loan,
                    $installment,
                    $payment,
                    $payerUserId
                );

                return $payment;
            }
        );
    }

    /**
     * پیدا کردن Payment Intent از روی Callback
     */
    private function resolvePaymentIntent(
        array $callbackData
    ): PaymentIntent {
        $paymentIntentId =
            $callbackData['payment_intent_id']
            ?? null;

        if (! $paymentIntentId) {
            throw new \DomainException(
                'شناسه Payment Intent ارسال نشده است.'
            );
        }

        if (
            ! ctype_digit(
                (string) $paymentIntentId
            )
        ) {
            throw new \DomainException(
                'شناسه Payment Intent نامعتبر است.'
            );
        }

        $paymentIntent =
            PaymentIntent::query()
                ->find((int) $paymentIntentId);

        if (! $paymentIntent) {
            throw new \DomainException(
                'Payment Intent پیدا نشد.'
            );
        }

        $callbackToken =
            $callbackData['token']
            ?? null;

        if (
            ! $callbackToken
            || ! $paymentIntent->gateway_token
            || ! hash_equals(
                $paymentIntent->gateway_token,
                $callbackToken
            )
        ) {
            throw new \DomainException(
                'توکن پرداخت نامعتبر است.'
            );
        }

        return $paymentIntent;
    }

    /**
     * ساخت LoanPayment
     */
    private function createLoanPayment(
        Installment $installment,
        PaymentIntent $paymentIntent,
        array $gatewayResponse,
        string $trackingCode,
        ?int $payerUserId
    ): LoanPayment {
        return LoanPayment::create([
            'loan_id' =>
                $installment->loan_id,

            'installment_id' =>
                $installment->id,

            'user_id' =>
                $payerUserId,

            'amount' =>
                $installment->amount,

            'tracking_code' =>
                $trackingCode,

            /*
             * درگاه واقعی از PaymentIntent
             */
            'gateway' =>
                $paymentIntent->gateway,

            'bank_transaction_id' =>
                $gatewayResponse['transaction_id']
                ?? null,

            'bank_reference_number' =>
                $gatewayResponse['reference_number']
                ?? null,

            'paid_at' =>
                now(),
        ]);
    }

    /**
     * پرداخت شدن قسط
     */
    private function markInstallmentAsPaid(
        Installment $installment
    ): void {
        $installment->update([
            'status' =>
                InstallmentStatus::PAID->value,

            'paid_at' =>
                now(),
        ]);
    }

    /**
     * پایان وام در صورت پرداخت تمام اقساط
     */
    private function finishLoanIfNeeded(
        Loan $loan
    ): void {
        $hasUnpaidInstallments =
            Installment::query()
                ->where(
                    'loan_id',
                    $loan->id
                )
                ->whereIn('status', [
                    InstallmentStatus::PENDING->value,
                    InstallmentStatus::OVERDUE->value,
                ])
                ->exists();

        if (! $hasUnpaidInstallments) {
            $loan->update([
                'status' =>
                    LoanStatus::FINISHED->value,
            ]);
        }
    }

    /**
     * ارسال اعلان‌های پرداخت
     */
    private function sendPaymentNotifications(
        Loan $loan,
        Installment $installment,
        LoanPayment $payment,
        ?int $payerUserId
    ): void {
        $loanOwnerUserId =
            $loan->customer?->user?->id;

        if ($loanOwnerUserId) {
            Notification::create([
                'user_id' =>
                    $loanOwnerUserId,

                'type' =>
                    'loan_payment',

                'title' =>
                    'پرداخت قسط',

                'message' =>
                    'قسط شماره '
                    . $installment->installment_number
                    . ' با موفقیت پرداخت شد.',

                'data' => [
                    'loan_payment_id' =>
                        $payment->id,

                    'installment_id' =>
                        $installment->id,
                ],
            ]);
        }

        if (
            $payerUserId
            && $payerUserId !== $loanOwnerUserId
        ) {
            Notification::create([
                'user_id' =>
                    $payerUserId,

                'type' =>
                    'loan_payment',

                'title' =>
                    'پرداخت قسط',

                'message' =>
                    'پرداخت قسط با موفقیت انجام شد.',

                'data' => [
                    'loan_payment_id' =>
                        $payment->id,

                    'installment_id' =>
                        $installment->id,
                ],
            ]);
        }
    }

    /**
     * اعتبارسنجی اولیه پرداخت
     */
    private function validatePayment(
        Installment $installment,
        bool $allowOthers = false
    ): void {
        if (
            $installment->loan?->status
            !== LoanStatus::ACTIVE
        ) {
            throw new \DomainException(
                'این وام فعال نیست.'
            );
        }

        if (
            $installment->status
            === InstallmentStatus::PAID
        ) {
            throw new \DomainException(
                'این قسط قبلاً پرداخت شده است.'
            );
        }

        $user = auth()->user();

        if (! $user) {
            throw new \DomainException(
                'کاربر وارد نشده است.'
            );
        }

        if (! $user->customer_id) {
            throw new \DomainException(
                'حساب مشتری برای کاربر پیدا نشد.'
            );
        }

        if (! $allowOthers) {
            $loanCustomerId =
                $installment->loan?->customer_id;

            if (
                (int) $loanCustomerId
                !== (int) $user->customer_id
            ) {
                throw new \DomainException(
                    'شما مجاز به پرداخت این قسط نیستید.'
                );
            }
        }

        $previousUnpaidExists =
            Installment::query()
                ->where(
                    'loan_id',
                    $installment->loan_id
                )
                ->where(
                    'installment_number',
                    '<',
                    $installment->installment_number
                )
                ->whereIn('status', [
                    InstallmentStatus::PENDING->value,
                    InstallmentStatus::OVERDUE->value,
                ])
                ->exists();

        if ($previousUnpaidExists) {
            throw new \DomainException(
                'ابتدا باید اقساط قبلی پرداخت شوند.'
            );
        }
    }

    /**
     * پیدا کردن اولین قسط قابل پرداخت بر اساس شماره وام
     */
    public function findPayableInstallmentByLoanNumber(
        string $loanNumber
    ): Installment {
        $loan = Loan::query()
            ->with([
                'customer',
                'loanType',
                'installments',
            ])
            ->where(
                'loan_number',
                $loanNumber
            )
            ->first();

        if (! $loan) {
            throw new \DomainException(
                'وامی با این شماره پیدا نشد.'
            );
        }

        if (
            $loan->status
            !== LoanStatus::ACTIVE
        ) {
            throw new \DomainException(
                'این وام فعال نیست.'
            );
        }

        $installment = $loan->installments
            ->whereIn('status', [
                InstallmentStatus::PENDING->value,
                InstallmentStatus::OVERDUE->value,
            ])
            ->sortBy('installment_number')
            ->first();

        if (! $installment) {
            throw new \DomainException(
                'قسط قابل پرداختی برای این وام وجود ندارد.'
            );
        }

        return $installment;
    }
}
