<?php

namespace App\Services\Donation;

use App\Enums\AccountStatus;
use App\Enums\PaymentGateway;
use App\Enums\PaymentMethod;
use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Customer;
use App\Models\DonationPayment;
use App\Models\PaymentIntent;
use App\Services\Account\AccountService;
use App\Services\Account\AccountTransactionService;
use App\Services\Payment\Gateways\GatewayInterface;
use App\Services\Payment\PaymentIntentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DonationPaymentService
{
    public function __construct(
        private readonly GatewayInterface $gateway,
        private readonly AccountTransactionService $accountTransactionService,
        private readonly AccountService $accountService,
        private readonly PaymentIntentService $paymentIntentService,
    ) {
    }

    /**
     * شروع پرداخت کمک
     */
    public function startPayment(
        ?Customer $customer,
        Account $account,
        int $amount,
        ?string $donorName = null,
        ?string $donorMobile = null,
        string $paymentType = 'donation_customer'
    ): array {
        $this->validateDonationInput(
            $account,
            $amount,
            $paymentType
        );

        $gateway = PaymentGateway::from(
            config('payment.gateway')
        );

        $trackingCode = $this->generateTrackingCode();

        /*
         * ساخت پرداخت و PaymentIntent در یک تراکنش.
         * قفل DonationPayment قبل از قفل Intent گرفته می‌شود.
         */
        [$payment, $paymentIntent] = DB::transaction(
            function () use (
                $customer,
                $account,
                $amount,
                $donorName,
                $donorMobile,
                $trackingCode,
                $gateway,
                $paymentType
            ) {
                $payment = DonationPayment::create([
                    'customer_id' => $customer?->id,
                    'donor_name' => $donorName,
                    'donor_mobile' => $donorMobile,
                    'account_id' => $account->id,
                    'amount' => $amount,
                    'tracking_code' => $trackingCode,
                    'gateway' => $gateway,
                    'status' => 0,
                ]);

                $paymentIntent = $this->paymentIntentService->create(
                    paymentType: $paymentType,
                    referenceId: $payment->id,
                    amount: $amount,
                    trackingCode: $trackingCode,
                    gateway: $gateway,
                    payerUserId: auth()->id(),
                );

                return [$payment, $paymentIntent];
            }
        );

        return $this->requestGateway(
            $payment,
            $paymentIntent,
            $paymentType
        );
    }

    /**
     * تأیید پرداخت کمک
     */
    public function verifyPayment(
        array $callbackData
    ): DonationPayment {
        $paymentIntentId = $callbackData['payment_intent_id'] ?? null;
        $callbackToken = $callbackData['token'] ?? null;

        if (
            ! $paymentIntentId
            || ! ctype_digit((string) $paymentIntentId)
        ) {
            throw new \DomainException(
                'شناسه Payment Intent نامعتبر است.'
            );
        }

        if (
            ! is_string($callbackToken)
            || $callbackToken === ''
        ) {
            throw new \DomainException(
                'توکن پرداخت ارسال نشده است.'
            );
        }

        $initialIntent = $this->paymentIntentService->findById(
            (int) $paymentIntentId
        );

        if (
            ! $initialIntent->gateway_token
            || ! hash_equals(
                $initialIntent->gateway_token,
                $callbackToken
            )
        ) {
            throw new \DomainException(
                'توکن پرداخت نامعتبر است.'
            );
        }

        if (! in_array(
            $initialIntent->payment_type,
            ['donation_customer', 'donation_public'],
            true
        )) {
            throw new \DomainException(
                'نوع Payment Intent برای کمک نامعتبر است.'
            );
        }

        $paymentId = (int) $initialIntent->reference_id;
        $paymentIntentId = (int) $initialIntent->id;
        $paymentType = $initialIntent->payment_type;
        $amount = (int) $initialIntent->amount;
        $gateway = $initialIntent->gateway;

        if (
            isset($callbackData['reference_id'])
            && (int) $callbackData['reference_id'] !== $paymentId
        ) {
            throw new \DomainException(
                'مرجع پرداخت با Payment Intent مطابقت ندارد.'
            );
        }

        /*
         * Callback تکراری برای پرداخت موفق
         */
        if ($initialIntent->isPaid()) {
            $payment = DonationPayment::query()
                ->findOrFail($paymentId);

            if ($payment->status === 1) {
                return $payment;
            }

            throw new \DomainException(
                'Payment Intent پرداخت شده است اما وضعیت پرداخت کمک نیازمند بررسی است.'
            );
        }

        /*
         * مرحله اول:
         * قفل DonationPayment و سپس PaymentIntent.
         * تغییر وضعیت به verifying قبل از تماس با درگاه انجام می‌شود.
         */
        $paymentIntent = DB::transaction(function () use (
            $paymentId,
            $paymentIntentId,
            $paymentType,
            $amount,
            $gateway
        ) {
            $payment = DonationPayment::query()
                ->lockForUpdate()
                ->findOrFail($paymentId);

            $intent = $this->paymentIntentService
                ->findByIdForUpdate($paymentIntentId);

            $this->validateIntentReference(
                $intent,
                $payment,
                $paymentType,
                $amount,
                $gateway
            );

            if ($payment->status !== 0) {
                throw new \DomainException(
                    'وضعیت پرداخت کمک برای تأیید مجدد معتبر نیست.'
                );
            }

            $this->paymentIntentService->validateForPayment(
                intent: $intent,
                paymentType: $paymentType,
                referenceId: $payment->id,
                amount: (int) $payment->amount,
                gateway: $gateway,
            );

            return $this->paymentIntentService->markVerifying(
                $intent
            );
        });

        /*
         * مرحله دوم:
         * تأیید درگاه خارج از تراکنش دیتابیس.
         */
        try {
            $gatewayResponse = $this->gateway->verify([
                ...$callbackData,
                'payment_intent_id' => $paymentIntent->id,
                'payment_type' => $paymentType,
                'reference_id' => $paymentId,
                'amount' => $amount,
                'tracking_code' => $paymentIntent->tracking_code,
                'token' => $callbackToken,
            ]);
        } catch (\Throwable $exception) {
            /*
             * نتیجه نامشخص است.
             * وضعیت verifying حفظ می‌شود تا بررسی مالی انجام شود.
             */
            throw new \DomainException(
                'نتیجه پرداخت از درگاه مشخص نشد؛ پرداخت نیازمند بررسی است.'
            );
        }

        if (! ($gatewayResponse['success'] ?? false)) {
            /*
             * فقط پاسخ قطعی ناموفق باید به این مسیر برسد.
             * خطای شبکه و timeout نباید این متد را فراخوانی کنند.
             */
            $this->paymentIntentService->markVerificationFailed(
                $paymentIntent
            );

            throw new \DomainException(
                $gatewayResponse['message']
                ?? 'تأیید پرداخت توسط درگاه ناموفق بود.'
            );
        }

        /*
         * مرحله سوم:
         * ثبت نهایی پرداخت.
         *
         * ترتیب قفل:
         * DonationPayment → PaymentIntent → Account
         */
        $result = DB::transaction(function () use (
            $paymentId,
            $paymentIntentId,
            $paymentType,
            $amount,
            $gateway,
            $gatewayResponse
        ) {
            $payment = DonationPayment::query()
                ->lockForUpdate()
                ->findOrFail($paymentId);

            $intent = $this->paymentIntentService
                ->findByIdForUpdate($paymentIntentId);

            $this->validateIntentReference(
                $intent,
                $payment,
                $paymentType,
                $amount,
                $gateway
            );

            /*
             * Callback تکراری
             */
            if ($intent->isPaid()) {
                if ($payment->status === 1) {
                    return $payment->fresh();
                }

                throw new \DomainException(
                    'Payment Intent پرداخت شده اما وضعیت کمک پرداخت‌شده نیست.'
                );
            }

            if ($intent->status !== 'verifying') {
                throw new \DomainException(
                    'وضعیت Payment Intent برای ثبت پرداخت معتبر نیست.'
                );
            }

            /*
             * اگر وضعیت کمک قبلاً ناموفق یا غیرقابل تکمیل شده باشد،
             * دریافت پول از درگاه باید به بررسی مالی منتقل شود.
             */
            if ($payment->status !== 0) {
                $this->paymentIntentService->markReviewRequired(
                    $intent,
                    $gatewayResponse['transaction_id'] ?? null,
                    $gatewayResponse['reference_number'] ?? null
                );

                return [
                    'review_required' => true,
                ];
            }

            /*
             * قفل حساب مقصد
             */
            $account = Account::query()
                ->lockForUpdate()
                ->find($payment->account_id);

            if (! $account) {
                $this->paymentIntentService->markReviewRequired(
                    $intent,
                    $gatewayResponse['transaction_id'] ?? null,
                    $gatewayResponse['reference_number'] ?? null
                );

                return [
                    'review_required' => true,
                ];
            }

            if ($account->status !== AccountStatus::ACTIVE) {
                $this->paymentIntentService->markReviewRequired(
                    $intent,
                    $gatewayResponse['transaction_id'] ?? null,
                    $gatewayResponse['reference_number'] ?? null
                );

                return [
                    'review_required' => true,
                ];
            }

            $description = $payment->customer_id
                ? 'کمک آنلاین عضو صندوق'
                : 'کمک آنلاین از طرف '
                . ($payment->donor_name ?: 'فرد خارج از صندوق');

            $balanceBefore = (int) $account->balance;
            $balanceAfter = $balanceBefore + (int) $payment->amount;

            /*
             * افزایش موجودی حساب
             */
            $this->accountService->depositBalance(
                account: $account,
                amount: (int) $payment->amount,
            );

            /*
             * ثبت گردش حساب
             */
            $this->accountTransactionService->create(
                account: $account,
                type: TransactionType::DEPOSIT,
                source: TransactionSource::ONLINE,
                paymentMethod: PaymentMethod::GATEWAY,
                amount: (int) $payment->amount,
                balanceBefore: $balanceBefore,
                balanceAfter: $balanceAfter,
                createdBy: auth()->id(),
                description: $description,
            );

            /*
             * ثبت پرداخت کمک
             */
            $payment->update([
                'status' => 1,
                'bank_transaction_id' =>
                    $gatewayResponse['transaction_id'] ?? null,
                'bank_reference_number' =>
                    $gatewayResponse['reference_number'] ?? null,
                'paid_at' => now(),
            ]);

            /*
             * ثبت موفقیت PaymentIntent
             */
            $this->paymentIntentService->markPaid(
                $intent,
                $gatewayResponse['transaction_id'] ?? null,
                $gatewayResponse['reference_number'] ?? null
            );

            return $payment->fresh();
        });

        if (
            is_array($result)
            && ($result['review_required'] ?? false) === true
        ) {
            throw new \DomainException(
                'پرداخت در درگاه موفق بوده، اما ثبت آن نیازمند بررسی مالی است.'
            );
        }

        if (! $result instanceof DonationPayment) {
            throw new \DomainException(
                'نتیجه تأیید پرداخت کمک معتبر نیست.'
            );
        }

        return $result;
    }

    /**
     * ارسال پرداخت موجود به درگاه
     */
    public function sendToGateway(
        DonationPayment $payment,
        string $paymentType = 'donation_customer'
    ): array {
        if (! in_array($paymentType, [
            'donation_customer',
            'donation_public',
        ], true)) {
            throw new \DomainException(
                'نوع پرداخت کمک نامعتبر است.'
            );
        }

        $gateway = PaymentGateway::from(
            config('payment.gateway')
        );

        /*
         * قفل پرداخت و ایجاد Intent در یک تراکنش.
         */
        $paymentIntent = DB::transaction(function () use (
            $payment,
            $paymentType,
            $gateway
        ) {
            $lockedPayment = DonationPayment::query()
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if ($lockedPayment->status !== 0) {
                throw new \DomainException(
                    'این پرداخت دیگر قابل ارسال به درگاه نیست.'
                );
            }

            return $this->paymentIntentService->create(
                paymentType: $paymentType,
                referenceId: $lockedPayment->id,
                amount: (int) $lockedPayment->amount,
                trackingCode: $lockedPayment->tracking_code,
                gateway: $gateway,
                payerUserId: auth()->id(),
            );
        });

        return $this->requestGateway(
            $payment,
            $paymentIntent,
            $paymentType
        );
    }

    /**
     * ارسال درخواست به درگاه و ثبت توکن.
     */
    private function requestGateway(
        DonationPayment $payment,
        PaymentIntent $paymentIntent,
        string $paymentType
    ): array {
        try {
            $gatewayResponse = $this->gateway->request([
                'payment_intent_id' => $paymentIntent->id,
                'payment_type' => $paymentType,
                'reference_id' => $payment->id,
                'amount' => (int) $payment->amount,
                'tracking_code' => $payment->tracking_code,
                'payer_user_id' => auth()->id(),
                'callback_url' => route('payments.callback'),
            ]);
        } catch (\Throwable $exception) {
            /*
             * ممکن است درخواست در درگاه ثبت شده باشد
             * ولی پاسخ به برنامه نرسیده باشد.
             * بنابراین وضعیت را failed نمی‌کنیم.
             */
            throw new \DomainException(
                'ارتباط با درگاه نامشخص شد؛ درخواست پرداخت نیازمند بررسی است.'
            );
        }

        if (! ($gatewayResponse['success'] ?? false)) {
            /*
             * این شاخه باید فقط برای رد قطعی درخواست توسط درگاه باشد.
             */
            $this->paymentIntentService->markFailed(
                $paymentIntent
            );

            $this->markDonationPaymentFailed(
                $payment->id
            );

            throw new \DomainException(
                $gatewayResponse['message']
                ?? 'ایجاد درخواست پرداخت ناموفق بود.'
            );
        }


        $gatewayToken = $gatewayResponse['token'] ?? null;

        if (! is_string($gatewayToken) || trim($gatewayToken) === '') {
            throw new \DomainException(
                'درگاه پاسخ موفق داده اما توکن پرداخت دریافت نشد؛ درخواست نیازمند بررسی است.'
            );
        }

        $redirectUrl = $gatewayResponse['redirect_url'] ?? null;

        if (
            ! is_string($redirectUrl)
            || trim($redirectUrl) === ''
            || filter_var($redirectUrl, FILTER_VALIDATE_URL) === false
        ) {
            throw new \DomainException(
                'آدرس هدایت درگاه معتبر نیست؛ درخواست پرداخت نیازمند بررسی است.'
            );
        }


        $urlParts = parse_url($redirectUrl);

        $redirectHost = is_array($urlParts)
            ? strtolower(rtrim($urlParts['host'] ?? '', '.'))
            : '';

        $allowedHosts = array_map(
            static fn ($host) => strtolower(
                rtrim(trim((string) $host), '.')
            ),
            config('payment.redirect_allowed_hosts', [])
        );

        $allowedSchemes = app()->environment(['local', 'testing'])
            ? ['http', 'https']
            : ['https'];

        $validRedirect = is_array($urlParts)
            && isset($urlParts['scheme'], $urlParts['host'])
            && in_array(
                strtolower($urlParts['scheme']),
                $allowedSchemes,
                true
            )
            && in_array($redirectHost, $allowedHosts, true)
            && ! isset($urlParts['user'])
            && ! isset($urlParts['pass']);

        if (! $validRedirect) {
            throw new \DomainException(
                'دامنه یا ساختار آدرس هدایت درگاه مجاز نیست.'
            );
        }


        $paymentIntent = $this->paymentIntentService->markRedirected(
            $paymentIntent,
            $gatewayToken
        );

        return [
            'payment' => $payment->fresh(),
            'payment_intent' => $paymentIntent,
            'gateway' => $gatewayResponse,
        ];
    }

    /**
     * اعتبارسنجی ورودی شروع پرداخت کمک
     */
    private function validateDonationInput(
        Account $account,
        int $amount,
        string $paymentType
    ): void {
        if ($amount <= 0) {
            throw new \DomainException(
                'مبلغ کمک باید بیشتر از صفر باشد.'
            );
        }

        if ($account->status !== AccountStatus::ACTIVE) {
            throw new \DomainException(
                'حساب مقصد فعال نیست.'
            );
        }

        if (! in_array($paymentType, [
            'donation_customer',
            'donation_public',
        ], true)) {
            throw new \DomainException(
                'نوع پرداخت کمک نامعتبر است.'
            );
        }
    }

    /**
     * اعتبارسنجی ارتباط Intent و DonationPayment
     */
    private function validateIntentReference(
        PaymentIntent $intent,
        DonationPayment $payment,
        string $paymentType,
        int $amount,
        PaymentGateway $gateway
    ): void {
        if (
            (int) $intent->reference_id
            !== (int) $payment->id
        ) {
            throw new \DomainException(
                'Payment Intent با پرداخت کمک مطابقت ندارد.'
            );
        }

        if ($intent->payment_type !== $paymentType) {
            throw new \DomainException(
                'نوع Payment Intent با نوع پرداخت کمک مطابقت ندارد.'
            );
        }

        if (
            (int) $intent->amount !== (int) $payment->amount
            || (int) $intent->amount !== $amount
        ) {
            throw new \DomainException(
                'مبلغ Payment Intent با مبلغ کمک مطابقت ندارد.'
            );
        }

        if ($intent->gateway !== $gateway) {
            throw new \DomainException(
                'درگاه Payment Intent با درگاه مورد انتظار مطابقت ندارد.'
            );
        }
    }

    /**
     * تولید کد پیگیری یکتا
     */
    private function generateTrackingCode(): string
    {
        do {
            $trackingCode = 'DON-' . strtoupper(
                    (string) Str::ulid()
                );
        } while (
            PaymentIntent::query()
                ->where('tracking_code', $trackingCode)
                ->exists()
            || DonationPayment::query()
                ->where('tracking_code', $trackingCode)
                ->exists()
        );

        return $trackingCode;
    }

    /**
     * ناموفق کردن پرداخت کمک فقط در صورت Pending بودن
     */
    private function markDonationPaymentFailed(
        int $paymentId
    ): void {
        DB::transaction(function () use ($paymentId) {
            $payment = DonationPayment::query()
                ->lockForUpdate()
                ->find($paymentId);

            if (! $payment || $payment->status !== 0) {
                return;
            }

            $payment->update([
                'status' => 2,
            ]);
        });
    }
}
