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

    public function startPayment(
        ?Customer $customer,
        Account $account,
        int $amount,
        ?string $donorName = null,
        ?string $donorMobile = null,
        string $paymentType = 'donation_customer'
    ): array {
        if ($amount <= 0) {
            throw new \DomainException('مبلغ کمک باید بیشتر از صفر باشد.');
        }

        if ($account->status !== AccountStatus::ACTIVE) {
            throw new \DomainException('حساب مقصد فعال نیست.');
        }

        if (! in_array($paymentType, ['donation_customer', 'donation_public'], true)) {
            throw new \DomainException('نوع پرداخت کمک نامعتبر است.');
        }

        $gateway = PaymentGateway::from(config('payment.gateway'));

        $trackingCode = 'DON-' . strtoupper(Str::random(10));

        [$payment, $paymentIntent] = DB::transaction(function () use (
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
        });

        try {
            $gatewayResponse = $this->gateway->request([
                'payment_intent_id' => $paymentIntent->id,
                'payment_type' => $paymentType,
                'reference_id' => $payment->id,
                'amount' => $amount,
                'tracking_code' => $trackingCode,
                'payer_user_id' => auth()->id(),
                'callback_url' => route('payments.callback'),
            ]);

            if (! ($gatewayResponse['success'] ?? false)) {
                $this->paymentIntentService->markFailed($paymentIntent);
                $payment->update(['status' => 2]);

                throw new \DomainException(
                    $gatewayResponse['message'] ?? 'خطا در اتصال به درگاه.'
                );
            }

            $gatewayToken = $gatewayResponse['token'] ?? null;

            if (! $gatewayToken) {
                $this->paymentIntentService->markFailed($paymentIntent);
                $payment->update(['status' => 2]);

                throw new \DomainException('توکن پرداخت از درگاه دریافت نشد.');
            }

            $paymentIntent->update([
                'gateway_token' => $gatewayToken,
            ]);

            $paymentIntent = $this->paymentIntentService->markRedirected(
                $paymentIntent,
                $gatewayToken
            );

            return [
                'payment' => $payment->fresh(),
                'payment_intent' => $paymentIntent,
                'gateway' => $gatewayResponse,
            ];
        } catch (\Throwable $e) {
            if (! $paymentIntent->isFailed()) {
                try {
                    $this->paymentIntentService->markFailed($paymentIntent);
                } catch (\Throwable) {
                    // وضعیت اصلی خطا حفظ می‌شود.
                }
            }

            $payment->update(['status' => 2]);

            throw $e;
        }
    }

    public function verifyPayment(array $callbackData): DonationPayment
    {
        $paymentIntentId = $callbackData['payment_intent_id'] ?? null;
        $callbackToken = $callbackData['token'] ?? null;

        if (! $paymentIntentId || ! ctype_digit((string) $paymentIntentId)) {
            throw new \DomainException('شناسه Payment Intent نامعتبر است.');
        }

        if (! is_string($callbackToken) || $callbackToken === '') {
            throw new \DomainException('توکن پرداخت ارسال نشده است.');
        }

        $paymentIntent = $this->paymentIntentService->findById(
            (int) $paymentIntentId
        );

        if (
            ! $paymentIntent->gateway_token ||
            ! hash_equals($paymentIntent->gateway_token, $callbackToken)
        ) {
            throw new \DomainException('توکن پرداخت نامعتبر است.');
        }

        if (
            ! in_array(
                $paymentIntent->payment_type,
                ['donation_customer', 'donation_public'],
                true
            )
        ) {
            throw new \DomainException('نوع Payment Intent برای کمک نامعتبر است.');
        }

        $paymentId = (int) $paymentIntent->reference_id;

        if (
            isset($callbackData['reference_id']) &&
            (int) $callbackData['reference_id'] !== $paymentId
        ) {
            throw new \DomainException(
                'مرجع پرداخت با Payment Intent مطابقت ندارد.'
            );
        }

        $payment = DonationPayment::query()->findOrFail($paymentId);

        if ((int) $paymentIntent->amount !== (int) $payment->amount) {
            throw new \DomainException(
                'مبلغ پرداخت با Payment Intent مطابقت ندارد.'
            );
        }

        $gateway = PaymentGateway::from(config('payment.gateway'));

        if ($paymentIntent->gateway !== $gateway) {
            throw new \DomainException(
                'درگاه پرداخت با Payment Intent مطابقت ندارد.'
            );
        }

        if ($paymentIntent->isPaid()) {
            return $payment->fresh();
        }

        $this->paymentIntentService->validateForPayment(
            intent: $paymentIntent,
            paymentType: $paymentIntent->payment_type,
            referenceId: $paymentId,
            amount: (int) $payment->amount,
            gateway: $gateway,
        );

        $gatewayResponse = $this->gateway->verify([
            ...$callbackData,
            'payment_intent_id' => $paymentIntent->id,
            'payment_type' => $paymentIntent->payment_type,
            'reference_id' => $paymentId,
            'amount' => $paymentIntent->amount,
            'tracking_code' => $paymentIntent->tracking_code,
            'token' => $callbackToken,
        ]);

        if (! ($gatewayResponse['success'] ?? false)) {
            $this->paymentIntentService->markFailed($paymentIntent);

            throw new \DomainException(
                $gatewayResponse['message'] ?? 'تأیید پرداخت توسط درگاه ناموفق بود.'
            );
        }

        return DB::transaction(function () use (
            $paymentIntent,
            $paymentId,
            $gatewayResponse,
            $gateway,
        ) {
            $intent = $this->paymentIntentService->findByIdForUpdate(
                $paymentIntent->id
            );

            if ($intent->isPaid()) {
                return DonationPayment::query()
                    ->findOrFail($paymentId)
                    ->fresh();
            }

            $payment = DonationPayment::query()
                ->lockForUpdate()
                ->findOrFail($paymentId);

            $this->paymentIntentService->validateForPayment(
                intent: $intent,
                paymentType: $intent->payment_type,
                referenceId: $payment->id,
                amount: (int) $payment->amount,
                gateway: $gateway,
            );

            $this->paymentIntentService->markVerifying($intent);

            if ($payment->status === 1) {
                $this->paymentIntentService->markPaid(
                    $intent,
                    $gatewayResponse['transaction_id'] ?? null,
                    $gatewayResponse['reference_number'] ?? null
                );

                return $payment->fresh();
            }

            if ($payment->status !== 0) {
                throw new \DomainException(
                    'این پرداخت دیگر قابل تکمیل نیست.'
                );
            }

            $account = Account::query()
                ->lockForUpdate()
                ->findOrFail($payment->account_id);

            if ($account->status !== AccountStatus::ACTIVE) {
                throw new \DomainException('حساب مقصد فعال نیست.');
            }

            $description = $payment->customer_id
                ? 'کمک آنلاین عضو صندوق'
                : 'کمک آنلاین از طرف ' .
                ($payment->donor_name ?: 'فرد خارج از صندوق');

            $balanceBefore = (int) $account->balance;
            $balanceAfter = $balanceBefore + (int) $payment->amount;

            $this->accountService->depositBalance(
                account: $account,
                amount: (int) $payment->amount,
            );

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

            $payment->update([
                'status' => 1,
                'bank_transaction_id' =>
                    $gatewayResponse['transaction_id'] ?? null,
                'bank_reference_number' =>
                    $gatewayResponse['reference_number'] ?? null,
                'paid_at' => now(),
            ]);

            $this->paymentIntentService->markPaid(
                $intent,
                $gatewayResponse['transaction_id'] ?? null,
                $gatewayResponse['reference_number'] ?? null
            );

            return $payment->fresh();
        });
    }

    public function sendToGateway(
        DonationPayment $payment,
        string $paymentType = 'donation_customer'
    ): array {
        if ($payment->status !== 0) {
            throw new \DomainException(
                'این پرداخت دیگر قابل ارسال به درگاه نیست.'
            );
        }

        if (! in_array($paymentType, ['donation_customer', 'donation_public'], true)) {
            throw new \DomainException('نوع پرداخت کمک نامعتبر است.');
        }

        $gateway = PaymentGateway::from(config('payment.gateway'));

        $paymentIntent = $this->paymentIntentService->create(
            paymentType: $paymentType,
            referenceId: $payment->id,
            amount: (int) $payment->amount,
            trackingCode: $payment->tracking_code,
            gateway: $gateway,
            payerUserId: auth()->id(),
        );

        try {
            $gatewayResponse = $this->gateway->request([
                'payment_intent_id' => $paymentIntent->id,
                'payment_type' => $paymentType,
                'reference_id' => $payment->id,
                'amount' => $payment->amount,
                'tracking_code' => $payment->tracking_code,
                'payer_user_id' => auth()->id(),
                'callback_url' => route('payments.callback'),
            ]);

            if (! ($gatewayResponse['success'] ?? false)) {
                $this->paymentIntentService->markFailed($paymentIntent);

                throw new \DomainException(
                    $gatewayResponse['message'] ?? 'خطا در اتصال به درگاه.'
                );
            }

            $gatewayToken = $gatewayResponse['token'] ?? null;

            if (! $gatewayToken) {
                $this->paymentIntentService->markFailed($paymentIntent);

                throw new \DomainException(
                    'توکن پرداخت از درگاه دریافت نشد.'
                );
            }

            $paymentIntent->update([
                'gateway_token' => $gatewayToken,
            ]);

            $paymentIntent = $this->paymentIntentService->markRedirected(
                $paymentIntent,
                $gatewayToken
            );

            return [
                'payment' => $payment->fresh(),
                'payment_intent' => $paymentIntent,
                'gateway' => $gatewayResponse,
            ];
        } catch (\Throwable $e) {
            if (! $paymentIntent->isFailed()) {
                try {
                    $this->paymentIntentService->markFailed($paymentIntent);
                } catch (\Throwable) {
                    // وضعیت اصلی خطا حفظ می‌شود.
                }
            }

            throw $e;
        }
    }
}
