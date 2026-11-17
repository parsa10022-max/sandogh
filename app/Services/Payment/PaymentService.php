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
         * ایجاد Intent زیر قفل قسط انجام می‌شود
         * تا درخواست‌های هم‌زمان نتوانند چند Intent فعال بسازند.
         */
        [$installment, $paymentIntent, $trackingCode] =
            DB::transaction(function () use (
                $installment,
                $allowOthers,
                $paymentType,
                $gateway,
                $payerUserId,
            ) {
                $lockedInstallment = Installment::query()
                    ->lockForUpdate()
                    ->find($installment->id);

                if (! $lockedInstallment) {
                    throw new \DomainException(
                        'قسط موردنظر پیدا نشد.'
                    );
                }

                $lockedInstallment->load('loan');

                $this->validatePayment(
                    $lockedInstallment,
                    $allowOthers
                );

                $activeIntentExists = PaymentIntent::query()
                    ->where(
                        'reference_id',
                        $lockedInstallment->id
                    )
                    ->whereIn('payment_type', [
                        'installment',
                        'installment_other',
                    ])
                    ->whereIn('status', [
                        'pending',
                        'redirected',
                        'verifying',
                        'review_required',
                        'paid',
                    ])
                    ->exists();

                if ($activeIntentExists) {
                    throw new \DomainException(
                        'برای این قسط درخواست پرداخت قبلی وجود دارد؛ ابتدا وضعیت آن باید مشخص شود.'
                    );
                }

                $trackingCode =
                    $this->trackingCodeService->generate();

                $paymentIntent = $this->paymentIntentService->create(
                    paymentType: $paymentType,
                    referenceId: $lockedInstallment->id,
                    amount: (int) $lockedInstallment->amount,
                    trackingCode: $trackingCode,
                    gateway: $gateway,
                    payerUserId: $payerUserId,
                    gatewayToken: null,
                    expiresInMinutes: 30,
            );

                return [
                    $lockedInstallment,
                    $paymentIntent,
                    $trackingCode,
                ];
            });

        /*
         * ارتباط با درگاه خارج از تراکنش دیتابیس
         */
        try {
            $gatewayResponse = $this->gateway->request([
                'payment_intent_id' => $paymentIntent->id,
                'payment_type' => $paymentType,
                'loan_id' => $installment->loan_id,
                'installment_id' => $installment->id,
                'loan_number' => $installment->loan->loan_number,
                'installment_number' =>
                    $installment->installment_number,
                'reference_id' => $installment->id,
                'amount' => (int) $paymentIntent->amount,
                'tracking_code' => $trackingCode,
                'payer_user_id' => $payerUserId,
                'callback_url' => route('payments.callback'),
                'gateway_token' => $paymentIntent->gateway_token,
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            throw new \DomainException(
                'ارتباط با درگاه برقرار نشد؛ وضعیت درخواست پرداخت نیازمند بررسی است.'
            );
        }

        if (! ($gatewayResponse['success'] ?? false)) {
            $this->paymentIntentService->markFailed(
                $paymentIntent
            );

            throw new \DomainException(
                $gatewayResponse['message']
                ?? 'ایجاد درخواست پرداخت ناموفق بود.'
            );
        }

        $gatewayToken = $gatewayResponse['token']
            ?? $paymentIntent->gateway_token;

        if (! $gatewayToken) {
            throw new \DomainException(
                'توکن پرداخت دریافت نشد؛ وضعیت درخواست نیازمند بررسی است.'
            );
        }

        $paymentIntent = $this->paymentIntentService->markRedirected(
            $paymentIntent,
            $gatewayToken
        );

        $gatewayResponse['payment_intent_id'] =
            $paymentIntent->id;

        return $gatewayResponse;
    }

    /**
     * تأیید پرداخت
     */
    public function verifyPayment(
        array $callbackData
    ): LoanPayment {
        $paymentIntent = $this->resolvePaymentIntent(
            $callbackData
        );

        $paymentIntentId = (int) $paymentIntent->id;
        $installmentId = (int) $paymentIntent->reference_id;
        $paymentType = $paymentIntent->payment_type;
        $amount = (int) $paymentIntent->amount;
        $gateway = $paymentIntent->gateway;
        $payerUserId = $paymentIntent->payer_user_id;
        $trackingCode = $paymentIntent->tracking_code;
        $callbackToken = $callbackData['token'] ?? null;

        /*
         * Callback تکراری برای پرداخت موفق
         */
        if ($paymentIntent->isPaid()) {
            $existingPayment = LoanPayment::query()
                ->where('installment_id', $installmentId)
                ->first();

            if ($existingPayment) {
                return $existingPayment;
            }

            throw new \DomainException(
                'Payment Intent پرداخت شده است اما رکورد پرداخت پیدا نشد.'
            );
        }

        /*
         * مرحله اول:
         * قفل قسط و Intent و انتقال به verifying.
         *
         * این تراکنش قبل از ارتباط با درگاه Commit می‌شود.
         */
        $paymentIntent = DB::transaction(function () use (
            $paymentIntentId,
            $installmentId,
            $paymentType,
            $amount,
            $gateway,
            $callbackToken,

        ) {
            $installment = Installment::query()
                ->lockForUpdate()
                ->find($installmentId);

            if (! $installment) {
                throw new \DomainException(
                    'قسط موردنظر پیدا نشد.'
                );
            }

            $intent = $this->paymentIntentService
                ->findByIdForUpdate($paymentIntentId);
            if (
                ! is_string($callbackToken)
                || $callbackToken === ''
                || ! is_string($intent->gateway_token)
                || $intent->gateway_token === ''
                || ! hash_equals($intent->gateway_token, $callbackToken)
            ) {
                throw new \DomainException(
                    'توکن پرداخت نامعتبر است.'
                );
            }

            if (
                (int) $intent->reference_id !== $installmentId
                || ! in_array($intent->payment_type, [
                    'installment',
                    'installment_other',
                ], true)
            ) {
                throw new \DomainException(
                    'Payment Intent با قسط موردنظر مطابقت ندارد.'
                );
            }

            $this->paymentIntentService->validateForPayment(
                intent: $intent,
                paymentType: $paymentType,
                referenceId: $installmentId,
                amount: $amount,
                gateway: $gateway,
            );

            if ((int) $installment->amount !== $amount) {
                throw new \DomainException(
                    'مبلغ قسط با Payment Intent مطابقت ندارد.'
                );
            }

            return $this->paymentIntentService->markVerifying(
                $intent
            );
        });

        /*
         * مرحله دوم:
         * تأیید درگاه خارج از تراکنش دیتابیس.
         */
        try {
            $gatewayResponse = $this->gateway->verify(
                $callbackData
            );
        } catch (\Throwable $exception) {
            /*
             * نتیجه نامشخص است.
             * Intent در verifying باقی می‌ماند.
             */
            throw new \DomainException(
                'نتیجه پرداخت از درگاه مشخص نشد؛ پرداخت نیازمند بررسی است.'
            );
        }

        if (! ($gatewayResponse['success'] ?? false)) {
            /*
             * پاسخ قطعی ناموفق درگاه:
             * verifying به failed تبدیل می‌شود.
             *
             * این متد نباید برای timeout یا خطای ارتباطی
             * استفاده شود.
             */
            $this->paymentIntentService->markVerificationFailed(
                $paymentIntent
            );

            throw new \DomainException(
                $gatewayResponse['message']
                ?? 'تأیید پرداخت ناموفق بود.'
            );
        }

        /*
         * مرحله سوم:
         * ثبت نهایی پرداخت.
         */
        $result = DB::transaction(function () use (
            $paymentIntentId,
            $installmentId,
            $amount,
            $paymentType,
            $gateway,
            $gatewayResponse,
            $payerUserId,
            $trackingCode,
        ) {
            /*
             * ترتیب قفل:
             * Installment → PaymentIntent → Loan
             */
            $installment = Installment::query()
                ->lockForUpdate()
                ->find($installmentId);

            if (! $installment) {
                throw new \DomainException(
                    'قسط موردنظر پیدا نشد.'
                );
            }

            $intent = $this->paymentIntentService
                ->findByIdForUpdate($paymentIntentId);

            if (
                (int) $intent->reference_id !== $installmentId
                || $intent->payment_type !== $paymentType
                || (int) $intent->amount !== $amount
                || $intent->gateway !== $gateway
            ) {
                throw new \DomainException(
                    'اطلاعات Payment Intent معتبر نیست.'
                );
            }

            /*
             * Callback تکراری
             */
            if ($intent->isPaid()) {
                $existingPayment = LoanPayment::query()
                    ->where('installment_id', $installmentId)
                    ->first();

                if ($existingPayment) {
                    return $existingPayment;
                }

                throw new \DomainException(
                    'رکورد پرداخت قبلی پیدا نشد.'
                );
            }

            if ($intent->status !== 'verifying') {
                throw new \DomainException(
                    'وضعیت Payment Intent برای ثبت پرداخت معتبر نیست.'
                );
            }

            if ((int) $installment->amount !== $amount) {
                $this->paymentIntentService->markReviewRequired(
                    $intent,
                    $gatewayResponse['transaction_id'] ?? null,
                    $gatewayResponse['reference_number'] ?? null,
                );

                return [
                    'review_required' => true,
                ];
            }

            $loan = Loan::query()
                ->with('customer.user')
                ->lockForUpdate()
                ->find($installment->loan_id);

            if (! $loan) {
                $this->paymentIntentService->markReviewRequired(
                    $intent,
                    $gatewayResponse['transaction_id'] ?? null,
                    $gatewayResponse['reference_number'] ?? null,
                );

                return [
                    'review_required' => true,
                ];
            }

            /*
             * جلوگیری از ثبت پرداخت تکراری
             */
            $existingPayment = LoanPayment::query()
                ->where('installment_id', $installmentId)
                ->first();

            if ($existingPayment) {
                $this->paymentIntentService->markReviewRequired(
                    $intent,
                    $gatewayResponse['transaction_id'] ?? null,
                    $gatewayResponse['reference_number'] ?? null,
                );

                return [
                    'review_required' => true,
                ];
            }

            if (
                $loan->status !== LoanStatus::ACTIVE
                || $installment->status === InstallmentStatus::PAID
            ) {
                $this->paymentIntentService->markReviewRequired(
                    $intent,
                    $gatewayResponse['transaction_id'] ?? null,
                    $gatewayResponse['reference_number'] ?? null,
                );

                return [
                    'review_required' => true,
                ];
            }

            /*
             * بررسی اقساط قبلی
             */
            $previousUnpaidExists = Installment::query()
                ->where('loan_id', $loan->id)
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
                $this->paymentIntentService->markReviewRequired(
                    $intent,
                    $gatewayResponse['transaction_id'] ?? null,
                    $gatewayResponse['reference_number'] ?? null,
                );

                return [
                    'review_required' => true,
                ];
            }

            /*
             * ثبت پرداخت
             */
            $payment = $this->createLoanPayment(
                installment: $installment,
                paymentIntent: $intent,
                gatewayResponse: $gatewayResponse,
                trackingCode: $trackingCode,
                payerUserId: $payerUserId,
            );

            $this->markInstallmentAsPaid($installment);

            $this->paymentIntentService->markPaid(
                $intent,
                $gatewayResponse['transaction_id'] ?? null,
                $gatewayResponse['reference_number'] ?? null,
            );

            $this->finishLoanIfNeeded($loan);

            $this->sendPaymentNotifications(
                $loan,
                $installment,
                $payment,
                $payerUserId
            );

            return $payment;
        });

        if (
            is_array($result)
            && ($result['review_required'] ?? false) === true
        ) {
            throw new \DomainException(
                'پرداخت در درگاه موفق بوده، اما ثبت آن نیازمند بررسی مالی است.'
            );
        }

        if (! $result instanceof LoanPayment) {
            throw new \DomainException(
                'نتیجه تأیید پرداخت معتبر نیست.'
            );
        }

        return $result;
    }

    /**
     * پیدا کردن Payment Intent از روی Callback
     */
    private function resolvePaymentIntent(
        array $callbackData
    ): PaymentIntent {
        $paymentIntentId =
            $callbackData['payment_intent_id'] ?? null;

        if (! $paymentIntentId) {
            throw new \DomainException(
                'شناسه Payment Intent ارسال نشده است.'
            );
        }

        if (! ctype_digit((string) $paymentIntentId)) {
            throw new \DomainException(
                'شناسه Payment Intent نامعتبر است.'
            );
        }

        $paymentIntent = PaymentIntent::query()
            ->find((int) $paymentIntentId);

        if (! $paymentIntent) {
            throw new \DomainException(
                'Payment Intent پیدا نشد.'
            );
        }

        $callbackToken = $callbackData['token'] ?? null;

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
            'loan_id' => $installment->loan_id,
            'installment_id' => $installment->id,
            'user_id' => $payerUserId,
            'amount' => $installment->amount,
            'tracking_code' => $trackingCode,
            'gateway' => $paymentIntent->gateway,
            'bank_transaction_id' =>
                $gatewayResponse['transaction_id'] ?? null,
            'bank_reference_number' =>
                $gatewayResponse['reference_number'] ?? null,
            'paid_at' => now(),
        ]);
    }

    /**
     * پرداخت شدن قسط
     */
    private function markInstallmentAsPaid(
        Installment $installment
    ): void {
        $installment->update([
            'status' => InstallmentStatus::PAID->value,
            'paid_at' => now(),
        ]);
    }

    /**
     * پایان وام در صورت پرداخت تمام اقساط
     */
    private function finishLoanIfNeeded(
        Loan $loan
    ): void {
        $hasUnpaidInstallments = Installment::query()
            ->where('loan_id', $loan->id)
            ->whereIn('status', [
                InstallmentStatus::PENDING->value,
                InstallmentStatus::OVERDUE->value,
            ])
            ->exists();

        if (! $hasUnpaidInstallments) {
            $loan->update([
                'status' => LoanStatus::FINISHED->value,
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
        $loanOwnerUserId = $loan->customer?->user?->id;

        if ($loanOwnerUserId) {
            Notification::create([
                'user_id' => $loanOwnerUserId,
                'type' => 'loan_payment',
                'title' => 'پرداخت قسط',
                'message' =>
                    'قسط شماره '
                    . $installment->installment_number
                    . ' با موفقیت پرداخت شد.',
                'data' => [
                    'loan_payment_id' => $payment->id,
                    'installment_id' => $installment->id,
                ],
            ]);
        }

        if (
            $payerUserId
            && $payerUserId !== $loanOwnerUserId
        ) {
            Notification::create([
                'user_id' => $payerUserId,
                'type' => 'loan_payment',
                'title' => 'پرداخت قسط',
                'message' => 'پرداخت قسط با موفقیت انجام شد.',
                'data' => [
                    'loan_payment_id' => $payment->id,
                    'installment_id' => $installment->id,
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
            $loanCustomerId = $installment->loan?->customer_id;

            if (
                (int) $loanCustomerId
                !== (int) $user->customer_id
            ) {
                throw new \DomainException(
                    'شما مجاز به پرداخت این قسط نیستید.'
                );
            }
        }

        $previousUnpaidExists = Installment::query()
            ->where('loan_id', $installment->loan_id)
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
            ->where('loan_number', $loanNumber)
            ->first();

        if (! $loan) {
            throw new \DomainException(
                'وامی با این شماره پیدا نشد.'
            );
        }

        if ($loan->status !== LoanStatus::ACTIVE) {
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
