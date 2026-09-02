<?php

namespace App\Services\Savings;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\PaymentMethod;
use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Customer;
use App\Models\Notification;
use App\Models\SavingsTransfer;
use App\Services\Account\AccountService;
use App\Services\Account\AccountTransactionService;
use App\Services\Payment\Gateways\GatewayInterface;
use Illuminate\Support\Facades\DB;

class SavingsTransferService
{
    public function __construct(

        private readonly GatewayInterface $gateway,

        private readonly SavingsTransferTrackingCodeService $trackingService,

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

        if ($amount <= 0) {

            throw new \DomainException(
                'مبلغ واریز باید بیشتر از صفر باشد.'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | پیدا کردن حساب پس‌انداز مقصد
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | تولید کد پیگیری
        |--------------------------------------------------------------------------
        */

        $trackingCode =
            $this->trackingService->generate();


        /*
        |--------------------------------------------------------------------------
        | ایجاد انتقال
        |--------------------------------------------------------------------------
        */

        $transfer = DB::transaction(function () use (

            $receiver,

            $account,

            $amount,

            $trackingCode

        ) {

            return SavingsTransfer::create([

                'sender_user_id' =>
                    auth()->id(),

                'receiver_customer_id' =>
                    $receiver->id,

                'account_id' =>
                    $account->id,

                'amount' =>
                    $amount,

                'tracking_code' =>
                    $trackingCode,

                'gateway' =>
                    config('payment.gateway'),

                'status' =>
                    'pending',

            ]);

        });


        /*
        |--------------------------------------------------------------------------
        | ارسال به درگاه
        |--------------------------------------------------------------------------
        */

        $gatewayResponse =
            $this->gateway->request([

                'payment_type' =>
                    'savings_transfer',

                'reference_id' =>
                    $transfer->id,

                'amount' =>
                    $amount,

                'tracking_code' =>
                    $trackingCode,

                'callback_url' =>
                    route('payments.callback'),

            ]);


        if (! $gatewayResponse['success']) {

            $transfer->update([

                'status' =>
                    'failed',

            ]);

            throw new \DomainException(

                $gatewayResponse['message']
                ??
                'خطا در اتصال به درگاه پرداخت.'

            );

        }


        return [

            'transfer' =>
                $transfer,

            'gateway' =>
                $gatewayResponse,

        ];
    }


    /**
     * تایید پرداخت و افزایش موجودی حساب مقصد
     */
    public function verifyPayment(
        array $callbackData
    ): SavingsTransfer {

        /*
        |--------------------------------------------------------------------------
        | تایید پرداخت توسط درگاه
        |--------------------------------------------------------------------------
        */

        $gatewayResponse =
            $this->gateway->verify(
                $callbackData
            );


        if (! $gatewayResponse['success']) {

            throw new \DomainException(
                $gatewayResponse['message']
                ?? 'پرداخت ناموفق بود.'
            );

        }


        return DB::transaction(function () use (

            $callbackData,

            $gatewayResponse

        ) {

            /*
            |--------------------------------------------------------------------------
            | دریافت انتقال
            |--------------------------------------------------------------------------
            */

            $transfer = SavingsTransfer::query()
                ->lockForUpdate()
                ->findOrFail(
                    $callbackData['reference_id']
                );


            /*
            |--------------------------------------------------------------------------
            | بررسی مبلغ
            |--------------------------------------------------------------------------
            */

            if (
                isset($callbackData['amount'])
                &&
                (int) $callbackData['amount']
                !==
                (int) $transfer->amount
            ) {

                throw new \DomainException(
                    'مبلغ پرداخت نامعتبر است.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | جلوگیری از Callback تکراری
            |--------------------------------------------------------------------------
            */

            if ($transfer->status === 'paid') {

                return $transfer;

            }


            /*
            |--------------------------------------------------------------------------
            | حساب مقصد
            |--------------------------------------------------------------------------
            */

            $account = Account::query()
                ->with('customer.user')
                ->lockForUpdate()
                ->findOrFail(
                    $transfer->account_id
                );


            /*
            |--------------------------------------------------------------------------
            | موجودی قبل و بعد
            |--------------------------------------------------------------------------
            */

            $balanceBefore =
                $account->balance;

            $balanceAfter =
                $balanceBefore
                +
                $transfer->amount;


            /*
            |--------------------------------------------------------------------------
            | افزایش موجودی
            |--------------------------------------------------------------------------
            */

            $this->accountService->depositBalance(
                $account,
                $transfer->amount
            );


            /*
            |--------------------------------------------------------------------------
            | ثبت تراکنش حساب
            |--------------------------------------------------------------------------
            */

            $this->accountTransactionService->create(

                account: $account,

                type:
                TransactionType::DEPOSIT,

                source:
                TransactionSource::ONLINE,

                paymentMethod:
                PaymentMethod::GATEWAY,

                amount:
                $transfer->amount,

                balanceBefore:
                $balanceBefore,

                balanceAfter:
                $balanceAfter,

                createdBy:
                null,

                description:
                'واریز آنلاین به حساب پس‌انداز'

            );


            /*
            |--------------------------------------------------------------------------
            | تکمیل انتقال
            |--------------------------------------------------------------------------
            */

            $transfer->update([

                'status' =>
                    'paid',

                'bank_transaction_id' =>
                    $gatewayResponse['transaction_id']
                    ??
                    null,

                'bank_reference_number' =>
                    $gatewayResponse['reference_number']
                    ??
                    null,

                'paid_at' =>
                    now(),

            ]);


            /*
            |--------------------------------------------------------------------------
            | اطلاعات گیرنده
            |--------------------------------------------------------------------------
            */

            $receiverCustomer =
                $account->customer;

            $receiverUser =
                $receiverCustomer?->user;


            /*
            |--------------------------------------------------------------------------
            | اطلاعات پرداخت‌کننده
            |--------------------------------------------------------------------------
            */

            $senderUser =
                $transfer->sender_user_id
                    ? \App\Models\User::find(
                    $transfer->sender_user_id
                )
                    : null;


            /*
            |--------------------------------------------------------------------------
            | اعلان برای گیرنده
            |--------------------------------------------------------------------------
            |
            | شخصی که پول به حساب او واریز شده است.
            |
            */

            if ($receiverUser) {

                Notification::create([

                    'user_id' =>
                        $receiverUser->id,

                    'type' =>
                        'savings_deposit_other',

                    'title' =>
                        'واریز به حساب پس‌انداز شما',

                    'message' =>
                        'مبلغ ' .
                        number_format($transfer->amount) .
                        ' ریال توسط یکی از اعضای صندوق به حساب پس‌انداز شما واریز شد. کد پیگیری: ' .
                        $transfer->tracking_code,

                    'data' => [

                        'amount' =>
                            $transfer->amount,

                        'account_number' =>
                            $account->account_number,

                        'tracking_code' =>
                            $transfer->tracking_code,

                        'transfer_id' =>
                            $transfer->id,

                        'sender_user_id' =>
                            $transfer->sender_user_id,

                        'paid_at' =>
                            $transfer->paid_at,

                    ],

                    'read_at' =>
                        null,

                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | اعلان برای پرداخت‌کننده
            |--------------------------------------------------------------------------
            |
            | شخصی که پول را پرداخت کرده است.
            |
            */

            if (
                $senderUser
                &&
                $senderUser->id !== $receiverUser?->id
            ) {

                $receiverName =
                    $receiverCustomer?->full_name
                    ??
                    'عضو صندوق';


                Notification::create([

                    'user_id' =>
                        $senderUser->id,

                    'type' =>
                        'savings_deposit_success',

                    'title' =>
                        'واریز به حساب پس‌انداز با موفقیت انجام شد',

                    'message' =>
                        'مبلغ ' .
                        number_format($transfer->amount) .
                        ' ریال به حساب پس‌انداز ' .
                        $receiverName .
                        ' واریز شد. کد پیگیری: ' .
                        $transfer->tracking_code,

                    'data' => [

                        'amount' =>
                            $transfer->amount,

                        'receiver_customer_id' =>
                            $transfer->receiver_customer_id,

                        'receiver_name' =>
                            $receiverName,

                        'account_number' =>
                            $account->account_number,

                        'tracking_code' =>
                            $transfer->tracking_code,

                        'transfer_id' =>
                            $transfer->id,

                        'paid_at' =>
                            $transfer->paid_at,

                    ],

                    'read_at' =>
                        null,

                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | بازگرداندن انتقال کامل
            |--------------------------------------------------------------------------
            */

            return $transfer->fresh();

        });
    }


    /**
     * نمایش رسید موفقیت انتقال
     */
    public function success(
        SavingsTransfer $transfer
    ) {

        /*
        |--------------------------------------------------------------------------
        | فقط پرداخت‌کننده اجازه مشاهده رسید را دارد
        |--------------------------------------------------------------------------
        */

        abort_if(

            $transfer->sender_user_id
            !==
            auth()->id(),

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
    public function failed()
    {

        return view(
            'customer.savings-transfer.failed'
        );

    }


    /**
     * تراکنش‌های حساب پس‌انداز
     */
    public function transactions()
    {
        $customer =
            auth()->user()->customer;


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
            ->firstOrFail();


        $transactions = $account
            ->transactions()
            ->latest('transaction_date')
            ->paginate(20);


        return view(
            'customer.savings.transactions',
            compact(
                'account',
                'transactions'
            )
        );
    }
}

