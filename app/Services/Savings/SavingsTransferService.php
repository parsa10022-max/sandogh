<?php

namespace App\Services\Savings;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\PaymentGateway;
use App\Enums\PaymentMethod;
use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Notification;
use App\Models\SavingsTransfer;
use App\Models\User;
use App\Services\Account\AccountService;
use App\Services\Account\AccountTransactionService;
use App\Services\Payment\Gateways\GatewayInterface;
use App\Services\Payment\PaymentIntentService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SavingsTransferService
{
    public function __construct(
        private readonly GatewayInterface $gateway,
        private readonly SavingsTransferTrackingCodeService $trackingService,
        private readonly PaymentIntentService $paymentIntentService,
        private readonly AccountTransactionService $accountTransactionService,
        private readonly AccountService $accountService,
    ) {
    }

    /**
     * شروع فرآیند پرداخت
     */
    public function startPayment(
        Customer $receiver,
        int $amount
    ): array {
        $senderUser = auth()->user();

        if (! $senderUser) {
            throw new \DomainException(
                'کاربر وارد سیستم نشده است.'
            );
        }

        if ($amount <= 0) {
            throw new \DomainException(
                'مبلغ واریز باید بیشتر از صفر باشد.'
            );
        }

        $account = $receiver
            ->accounts()
            ->where(
                'account_type',
                AccountType::SAVING->value
            )
            ->where(
                'status',
                AccountStatus::ACTIVE->value
            )
            ->first();

        if (! $account) {
            throw new \DomainException(
                'حساب پس‌انداز فعال برای این عضو پیدا نشد.'
            );
        }

        $gateway = PaymentGateway::from(
            config('payment.gateway')
        );

        $trackingCode = $this->trackingService->generate();

        /*
         * ایجاد انتقال و PaymentIntent در یک تراکنش
         */
        [$transfer, $paymentIntent] = DB::transaction(
            function () use (
                $senderUser,
                $receiver,
                $account,
                $amount,
                $trackingCode,
                $gateway
            ) {
                $lockedAccount = Account::query()
                    ->lockForUpdate()
                    ->whereKey($account->id)
                    ->where('customer_id', $receiver->id)
                    ->where('account_type', AccountType::SAVING->value)
                    ->where('status', AccountStatus::ACTIVE->value)
                    ->first();

                if (! $lockedAccount) {
                    throw new \DomainException(
                        'حساب پس‌انداز فعال برای این عضو پیدا نشد.'
                    );
                }

                $transfer = SavingsTransfer::create([
                    'sender_user_id' => $senderUser->id,
                    'receiver_customer_id' => $receiver->id,
                    'account_id' => $lockedAccount->id,
                    'amount' => $amount,
                    'tracking_code' => $trackingCode,
                    'gateway' => $gateway,
                    'status' => 'pending',
                ]);

                $paymentIntent = $this->paymentIntentService->create(
                    paymentType: 'savings_transfer',
                    referenceId: $transfer->id,
                    amount: $amount,
                    trackingCode: $trackingCode,
                    gateway: $gateway,
                    payerUserId: $senderUser->id,
        );

                return [$transfer, $paymentIntent];
            }
        );

        /*
         * درخواست به درگاه خارج از تراکنش دیتابیس
         *
         * Exception مبهم نباید باعث Failed شدن Intent شود.
         */
        try {
            $gatewayResponse = $this->gateway->request([
                'payment_type' => 'savings_transfer',
                'payment_intent_id' => $paymentIntent->id,
                'reference_id' => $transfer->id,
                'amount' => $amount,
                'tracking_code' => $trackingCode,
                'payer_user_id' => $senderUser->id,
                'callback_url' => route('payments.callback'),
            ]);
        } catch (\Throwable $e) {
            report($e);

            throw new \DomainException(
                'ارتباط با درگاه پرداخت با خطا مواجه شد. وضعیت درخواست پرداخت نیازمند بررسی است.'
            );
        }

        /*
         * پاسخ قطعی منفی درگاه
         */
        if (! ($gatewayResponse['success'] ?? false)) {
            $this->markTransferFailedSafely(
                $transfer->id,
                $paymentIntent->id
            );

            throw new \DomainException(
                $gatewayResponse['message']
                ?? 'درگاه درخواست پرداخت را نپذیرفت.'
            );
        }

        $gatewayToken = $gatewayResponse['token'] ?? null;

        /*
         * نبود توکن، وضعیت مبهم است.
         */
        if (
            ! is_string($gatewayToken)
            || $gatewayToken === ''
        ) {
            throw new \DomainException(
                'توکن پرداخت از درگاه دریافت نشد. وضعیت درخواست نیازمند بررسی است.'
            );
        }

        /*
         * ثبت توکن و تغییر وضعیت فقط از طریق PaymentIntentService
         */
        $paymentIntent = $this->paymentIntentService->markRedirected(
            $paymentIntent->refresh(),
            $gatewayToken
        );

        return [
            'transfer' => $transfer->fresh(),
            'payment_intent' => $paymentIntent,
            'gateway' => $gatewayResponse,
        ];
    }

    /**
     * تأیید پرداخت و افزایش موجودی حساب مقصد
     */
    public function verifyPayment(
        array $callbackData
    ): SavingsTransfer {

        $paymentIntentId =
            $callbackData['payment_intent_id'] ?? null;

        if (
            ! $paymentIntentId
            || ! ctype_digit((string) $paymentIntentId)
        ) {
            throw new \DomainException(
                'شناسه Payment Intent نامعتبر است.'
            );
        }

        $paymentIntentId = (int) $paymentIntentId;

        $callbackToken = $callbackData['token'] ?? null;

        if (
            ! is_string($callbackToken)
            || $callbackToken === ''
        ) {
            throw new \DomainException(
                'توکن پرداخت نامعتبر است.'
            );
        }

        /*
         * دریافت اولیه Intent
         */
        $paymentIntent = $this->paymentIntentService->findById(
            $paymentIntentId
        );

        if (
            ! $paymentIntent->gateway_token
            || ! hash_equals(
                (string) $paymentIntent->gateway_token,
                $callbackToken
            )
        ) {
            throw new \DomainException(
                'توکن پرداخت نامعتبر است.'
            );
        }

        if (
            $paymentIntent->payment_type !== 'savings_transfer'
        ) {
            throw new \DomainException(
                'نوع Payment Intent نامعتبر است.'
            );
        }

        $transferId = (int) $paymentIntent->reference_id;

        if (
            isset($callbackData['reference_id'])
            && (int) $callbackData['reference_id'] !== $transferId
        ) {
            throw new \DomainException(
                'مرجع پرداخت با Payment Intent مطابقت ندارد.'
            );
        }

        $gateway = PaymentGateway::from(
            config('payment.gateway')
        );

        if ($paymentIntent->gateway !== $gateway) {
            throw new \DomainException(
                'درگاه پرداخت با Payment Intent مطابقت ندارد.'
            );
        }

        /*
         * تطبیق اولیه انتقال
         */
        $transfer = SavingsTransfer::query()
            ->find($transferId);

        if (! $transfer) {
            throw new \DomainException(
                'تراکنش واریز موردنظر پیدا نشد.'
            );
        }

        if (
            (int) $paymentIntent->amount
            !== (int) $transfer->amount
        ) {
            throw new \DomainException(
                'مبلغ Payment Intent با انتقال مطابقت ندارد.'
            );
        }

        /*
         * Callback تکراری برای پرداخت موفق
         */
        if ($paymentIntent->isPaid()) {
            if ($transfer->status === 'paid') {
                return $transfer;
            }

            throw new \DomainException(
                'Payment Intent پرداخت شده است اما وضعیت انتقال ناسازگار است؛ بررسی مالی لازم است.'
            );
        }

        /*
         * مرحله اول:
         * قفل انتقال و Intent و ثبت Verifying
         *
         * این تراکنش پیش از تماس با درگاه Commit می‌شود.
         */
        $verificationData = DB::transaction(
            function () use (
                $transferId,
                $paymentIntentId,
                $gateway,
                $callbackToken
            ) {
                $transfer = SavingsTransfer::query()
                    ->lockForUpdate()
                    ->find($transferId);

                if (! $transfer) {
                    throw new \DomainException(
                        'تراکنش واریز موردنظر پیدا نشد.'
                    );
                }

                $paymentIntent = $this->paymentIntentService
                    ->findByIdForUpdate($paymentIntentId);

                /*
                 * اعتبارسنجی مجدد توکن پس از قفل‌کردن Intent
                 */
                if (
                    ! $paymentIntent->gateway_token
                    || ! hash_equals(
                        (string) $paymentIntent->gateway_token,
                        $callbackToken
                    )
                ) {
                    throw new \DomainException(
                        'توکن پرداخت نامعتبر است.'
                    );
                }

                /*
                 * تطبیق مجدد اطلاعات
                 */
                if (
                    $paymentIntent->payment_type !== 'savings_transfer'
                    || (int) $paymentIntent->reference_id !== (int) $transfer->id
                    || (int) $paymentIntent->amount !== (int) $transfer->amount
                    || $paymentIntent->gateway !== $gateway
                ) {
                    throw new \DomainException(
                        'اطلاعات Payment Intent با انتقال مطابقت ندارد.'
                    );
                }

                /*
                 * Callback تکراری
                 */
                if ($paymentIntent->isPaid()) {
                    if ($transfer->status === 'paid') {
                        return [
                            'already_paid' => true,
                            'transfer' => $transfer,
                        ];
                    }

                    throw new \DomainException(
                        'Payment Intent پرداخت شده اما انتقال تکمیل نشده است؛ بررسی مالی لازم است.'
                    );
                }

                /*
                 * بررسی وضعیت قابل پرداخت
                 */
                $this->paymentIntentService->validateForPayment(
                    intent: $paymentIntent,
                    paymentType: 'savings_transfer',
                    referenceId: $transferId,
                    amount: (int) $transfer->amount,
                    gateway: $gateway,
                );

                if ($transfer->status !== 'pending') {
                    throw new \DomainException(
                        'انتقال در وضعیت قابل پرداخت نیست.'
                    );
                }

                /*
                 * ثبت Verifying قبل از تماس با درگاه
                 */
                $paymentIntent = $this->paymentIntentService->markVerifying(
                    $paymentIntent
                );

                return [
                    'already_paid' => false,
                    'transfer' => $transfer,
                    'payment_intent' => $paymentIntent,
                ];
            }
        );

        if ($verificationData['already_paid']) {
            return $verificationData['transfer'];
        }

        /*
         * تأیید درگاه خارج از تراکنش دیتابیس
         *
         * در صورت خطای شبکه یا Exception مبهم،
         * Intent در وضعیت Verifying باقی می‌ماند.
         */
        try {
            $gatewayResponse = $this->gateway->verify([
                ...$callbackData,
                'payment_intent_id' => (string) $paymentIntentId,
                'payment_type' => 'savings_transfer',
                'reference_id' => $transferId,
                'amount' => $verificationData['payment_intent']->amount,
                'tracking_code' => $verificationData['payment_intent']->tracking_code,
                'token' => $callbackToken,
            ]);
        } catch (\Throwable $e) {
            report($e);

            throw new \DomainException(
                'پاسخ قطعی از درگاه دریافت نشد. وضعیت پرداخت برای بررسی حفظ شد.'
            );
        }

        /*
         * پاسخ قطعی منفی درگاه
         */
        if (! ($gatewayResponse['success'] ?? false)) {
            $this->markVerificationFailedSafely(
                $transferId,
                $paymentIntentId
            );

            throw new \DomainException(
                $gatewayResponse['message']
                ?? 'پرداخت توسط درگاه تأیید نشد.'
            );
        }

        /*
         * مرحله نهایی:
         * ثبت اتمیک موجودی، گردش حساب، انتقال و Intent
         *
         * ترتیب قفل‌ها:
         * 1. SavingsTransfer
         * 2. PaymentIntent
         * 3. Account
         */

        $result = DB::transaction(function () use (
            $paymentIntentId,
            $transferId,
            $gatewayResponse,
            $gateway,
            $callbackToken
        ) {
            /*
             * قفل انتقال
             */
            $transfer = SavingsTransfer::query()
                ->lockForUpdate()
                ->find($transferId);

            if (! $transfer) {
                throw new \DomainException(
                    'تراکنش واریز موردنظر پیدا نشد.'
                );
            }

            /*
             * قفل PaymentIntent
             */
            $paymentIntent = $this->paymentIntentService
                ->findByIdForUpdate($paymentIntentId);
            /*
 * اعتبارسنجی مجدد توکن پس از قفل‌کردن Intent
 */
            if (
                ! $paymentIntent->gateway_token
                || ! hash_equals(
                    (string) $paymentIntent->gateway_token,
                    $callbackToken
                )
            ) {
                throw new \DomainException(
                    'توکن پرداخت نامعتبر است.'
                );
            }

            /*
             * تطبیق مجدد اطلاعات
             */
            if (
                $paymentIntent->payment_type !== 'savings_transfer'
                || (int) $paymentIntent->reference_id !== (int) $transfer->id
                || (int) $paymentIntent->amount !== (int) $transfer->amount
                || $paymentIntent->gateway !== $gateway
            ) {
                throw new \DomainException(
                    'اطلاعات Payment Intent با انتقال مطابقت ندارد.'
                );
            }

            /*
             * Callback تکراری
             */
            if ($paymentIntent->isPaid()) {
                if ($transfer->status === 'paid') {
                    return [
                        'transfer' => $transfer,
                        'should_notify' => false,
                    ];
                }

                throw new \DomainException(
                    'Payment Intent پرداخت شده اما انتقال تکمیل نشده است؛ بررسی مالی لازم است.'
                );
            }

            /*
             * Intent باید Verifying باشد.
             */
            if ($paymentIntent->status !== 'verifying') {
                throw new \DomainException(
                    'وضعیت Payment Intent برای تکمیل پرداخت معتبر نیست.'
                );
            }

            /*
             * ناسازگاری وضعیت انتقال
             */
            if ($transfer->status !== 'pending') {
                $this->paymentIntentService->markReviewRequired(
                    $paymentIntent,
                    $gatewayResponse['transaction_id'] ?? null,
                    $gatewayResponse['reference_number'] ?? null,
        );

                return [
                    'transfer' => $transfer,
                    'should_notify' => false,
                ];
            }

            /*
             * قفل حساب مقصد
             */
            $account = Account::query()
                ->lockForUpdate()
                ->find($transfer->account_id);

            if (! $account) {
                $this->paymentIntentService->markReviewRequired(
                    $paymentIntent,
                    $gatewayResponse['transaction_id'] ?? null,
                    $gatewayResponse['reference_number'] ?? null,
        );

                return [
                    'transfer' => $transfer,
                    'should_notify' => false,
                ];
            }

            /*
             * تطبیق حساب و عضو دریافت‌کننده
             */
            if (
                (int) $account->customer_id
                !== (int) $transfer->receiver_customer_id
            ) {
                $this->paymentIntentService->markReviewRequired(
                    $paymentIntent,
                    $gatewayResponse['transaction_id'] ?? null,
                    $gatewayResponse['reference_number'] ?? null,
        );

                return [
                    'transfer' => $transfer,
                    'should_notify' => false,
                ];
            }

            /*
             * کنترل نوع حساب
             */
            if (
                $account->account_type
                !== AccountType::SAVING
            ) {
                $this->paymentIntentService->markReviewRequired(
                    $paymentIntent,
                    $gatewayResponse['transaction_id'] ?? null,
                    $gatewayResponse['reference_number'] ?? null,
        );

                return [
                    'transfer' => $transfer,
                    'should_notify' => false,
                ];
            }

            /*
             * کنترل فعال بودن حساب
             */
            if (
                $account->status
                !== AccountStatus::ACTIVE
            ) {
                $this->paymentIntentService->markReviewRequired(
                    $paymentIntent,
                    $gatewayResponse['transaction_id'] ?? null,
                    $gatewayResponse['reference_number'] ?? null,
        );

                return [
                    'transfer' => $transfer,
                    'should_notify' => false,
                ];
            }

            /*
             * محاسبه موجودی
             */
            $balanceBefore = (int) $account->balance;

            $balanceAfter = $balanceBefore
                + (int) $transfer->amount;

            /*
             * افزایش موجودی
             */
            $this->accountService->depositBalance(
                account: $account,
                amount: (int) $transfer->amount
            );

            /*
             * ثبت گردش حساب
             */
            $this->accountTransactionService->create(
                account: $account,
                type: TransactionType::DEPOSIT,
                source: TransactionSource::ONLINE,
                paymentMethod: PaymentMethod::GATEWAY,
                amount: (int) $transfer->amount,
                balanceBefore: $balanceBefore,
                balanceAfter: $balanceAfter,
                createdBy: null,
                description: 'واریز آنلاین به حساب پس‌انداز',
    );

            /*
             * تکمیل انتقال
             */
            $transfer->update([
                'status' => 'paid',
                'bank_transaction_id' =>
                    $gatewayResponse['transaction_id'] ?? null,
                'bank_reference_number' =>
                    $gatewayResponse['reference_number'] ?? null,
                'paid_at' => now(),
            ]);

            /*
             * تکمیل PaymentIntent
             */
            $this->paymentIntentService->markPaid(
                $paymentIntent,
                $gatewayResponse['transaction_id'] ?? null,
                $gatewayResponse['reference_number'] ?? null,
    );

            return [
                'transfer' => $transfer->fresh(),
                'should_notify' => true,
            ];
        });

        /*
         * ثبت اعلان‌ها بعد از Commit موفق عملیات مالی
         */
        if ($result['should_notify']) {
            try {
                $transfer = $result['transfer'];

                $account = Account::query()
                    ->find($transfer->account_id);

                $receiverCustomer = Customer::query()
                    ->with('user')
                    ->find($transfer->receiver_customer_id);

                $receiverUser = $receiverCustomer?->user;

                $senderUser = $transfer->sender_user_id
                    ? User::query()->find($transfer->sender_user_id)
                    : null;

                /*
                 * اعلان گیرنده
                 */
                if ($receiverUser) {
                    Notification::create([
                        'user_id' => $receiverUser->id,
                        'type' => 'savings_deposit_other',
                        'title' => 'واریز به حساب پس‌انداز شما',
                        'message' =>
                            'مبلغ '
                            . fa_money($transfer->amount)
                            . ' ریال توسط یکی از اعضای صندوق به حساب پس‌انداز شما واریز شد. کد پیگیری: '
                            . $transfer->tracking_code,
                        'data' => [
                            'amount' => $transfer->amount,
                            'account_number' => $account?->account_number,
                            'tracking_code' => $transfer->tracking_code,
                            'transfer_id' => $transfer->id,
                            'sender_user_id' => $transfer->sender_user_id,
                            'paid_at' => $transfer->paid_at,
                        ],
                        'read_at' => null,
                    ]);
                }

                /*
                 * اعلان پرداخت‌کننده
                 */
                if (
                    $senderUser
                    && $senderUser->id !== $receiverUser?->id
                ) {
                    $receiverName = $receiverCustomer?->full_name
                        ?? 'عضو صندوق';

                    Notification::create([
                        'user_id' => $senderUser->id,
                        'type' => 'savings_deposit_success',
                        'title' => 'واریز به حساب پس‌انداز با موفقیت انجام شد',
                        'message' =>
                            'مبلغ '
                            . fa_money($transfer->amount)
                            . ' ریال به حساب پس‌انداز '
                            . $receiverName
                            . ' واریز شد. کد پیگیری: '
                            . $transfer->tracking_code,
                        'data' => [
                            'amount' => $transfer->amount,
                            'receiver_customer_id' =>
                                $transfer->receiver_customer_id,
                            'receiver_name' => $receiverName,
                            'account_number' => $account?->account_number,
                            'tracking_code' => $transfer->tracking_code,
                            'transfer_id' => $transfer->id,
                            'paid_at' => $transfer->paid_at,
                        ],
                        'read_at' => null,
                    ]);
                }
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return $result['transfer'];

    }

    /**
     * Failed کردن انتقال در صورت رد قطعی درخواست اولیه درگاه
     */
    private function markTransferFailedSafely(
        int $transferId,
        int $paymentIntentId
    ): void {
        DB::transaction(function () use (
            $transferId,
            $paymentIntentId
        ) {
            $transfer = SavingsTransfer::query()
                ->lockForUpdate()
                ->find($transferId);

            if (! $transfer) {
                return;
            }

            $paymentIntent = $this->paymentIntentService
                ->findByIdForUpdate($paymentIntentId);

            if (
                $transfer->status === 'paid'
                || $paymentIntent->isPaid()
            ) {
                return;
            }

            $this->paymentIntentService->markFailed(
                $paymentIntent
            );

            $paymentIntent->refresh();

            if (
                $paymentIntent->isFailed()
                && $transfer->status === 'pending'
            ) {
                $transfer->update([
                    'status' => 'failed',
                ]);
            }
        });
    }

    /**
     * Failed کردن انتقال پس از پاسخ قطعی منفی تأیید درگاه
     */
    private function markVerificationFailedSafely(
        int $transferId,
        int $paymentIntentId
    ): void {
        DB::transaction(function () use (
            $transferId,
            $paymentIntentId
        ) {
            $transfer = SavingsTransfer::query()
                ->lockForUpdate()
                ->find($transferId);

            if (! $transfer) {
                return;
            }

            $paymentIntent = $this->paymentIntentService
                ->findByIdForUpdate($paymentIntentId);

            if (
                $transfer->status === 'paid'
                || $paymentIntent->isPaid()
            ) {
                return;
            }

            if ($paymentIntent->status !== 'verifying') {
                return;
            }

            $this->paymentIntentService->markVerificationFailed(
                $paymentIntent
            );

            $paymentIntent->refresh();

            if (
                $paymentIntent->isFailed()
                && $transfer->status === 'pending'
            ) {
                $transfer->update([
                    'status' => 'failed',
                ]);
            }
        });
    }

    /**
     * نمایش رسید موفقیت انتقال
     */
    public function success(
        SavingsTransfer $transfer
    ): View {
        abort_if(
            (int) $transfer->sender_user_id
            !== (int) auth()->id(),
            403
        );

        return view(
            'customer.savings-transfer.success',
            compact('transfer')
        );
    }

    /**
     * نمایش صفحه خطای انتقال
     */
    public function failed(): View
    {
        return view(
            'customer.savings-transfer.failed'
        );
    }

    /**
     * تراکنش‌های حساب پس‌انداز
     */
    public function transactions(): LengthAwarePaginator
    {
        $user = auth()->user();

        if (! $user) {
            throw new \DomainException(
                'کاربر وارد سیستم نشده است.'
            );
        }

        $customer = $user->customer;

        if (! $customer) {
            throw new \DomainException(
                'عضو صندوق یافت نشد.'
            );
        }

        $account = $customer
            ->accounts()
            ->where(
                'account_type',
                AccountType::SAVING->value
            )
            ->where(
                'status',
                AccountStatus::ACTIVE->value
            )
            ->first();

        if (! $account) {
            throw new \DomainException(
                'حساب پس‌انداز فعال برای شما پیدا نشد.'
            );
        }

        return $account
            ->transactions()
            ->latest('transaction_date')
            ->paginate(20);
    }
}
