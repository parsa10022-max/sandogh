<?php

namespace App\Services\Savings;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\PaymentMethod;
use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Models\AccountTransaction;
use App\Models\Customer;
use App\Models\SavingsTransfer;
use App\Services\Payment\Gateways\GatewayInterface;
use Illuminate\Support\Facades\DB;
use App\Models\Account;



class SavingsTransferService
{
    public function __construct(

        private readonly GatewayInterface $gateway,

        private readonly SavingsTransferTrackingCodeService $trackingService,

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



        $trackingCode =
            $this->trackingService->generate();



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
                        route('payments.callback'
                    ),

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
     * تایید پرداخت و افزایش موجودی
     */
    /**
     * تایید پرداخت و افزایش موجودی حساب مقصد
     */
    public function verifyPayment(array $callbackData): SavingsTransfer
    {
        $gatewayResponse = $this->gateway->verify(
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


            $transfer = SavingsTransfer::query()
                ->lockForUpdate()
                ->findOrFail(
                    $callbackData['reference_id']
                );
            if (
                isset($callbackData['amount'])
                &&
                (int)$callbackData['amount'] !== $transfer->amount
            ) {

                throw new \DomainException(
                    'مبلغ پرداخت نامعتبر است.'
                );

            }

            // جلوگیری از ثبت دوباره Callback
            if ($transfer->status === 'paid') {

                return $transfer;

            }


            $account = Account::query()
                ->lockForUpdate()
                ->findOrFail(
                    $transfer->account_id
                );


            $balanceBefore = $account->balance;


            $balanceAfter =
                $balanceBefore + $transfer->amount;



            // افزایش موجودی
            $account->update([

                'balance' => $balanceAfter,

            ]);



            // ثبت تراکنش حساب
            AccountTransaction::create([

                'account_id' =>
                    $account->id,


                'transaction_no' =>
                    'AT' . now()->format('YmdHis') . rand(1000,9999),


                'transaction_type' =>
                    TransactionType::DEPOSIT,


                'transaction_source' =>
                    TransactionSource::ONLINE,


                'amount' =>
                    $transfer->amount,


                'balance_before' =>
                    $balanceBefore,


                'balance_after' =>
                    $balanceAfter,


                'payment_method' =>
                    PaymentMethod::GATEWAY,


                'transaction_date' =>
                    today(),


                'created_by' =>
                    null,


                'description' =>
                    'واریز آنلاین به حساب پس‌انداز',

            ]);



            // تکمیل انتقال
            $transfer->update([

                'status' =>
                    'paid',


                'bank_transaction_id' =>
                    $gatewayResponse['transaction_id']
                    ?? null,


                'bank_reference_number' =>
                    $gatewayResponse['reference_number']
                    ?? null,


                'paid_at' =>
                    now(),

            ]);



            return $transfer->fresh();

        });
    }

    public function success(
        SavingsTransfer $transfer
    )
    {

        // فقط اجازه مشاهده پرداخت‌کننده
        abort_if(

            $transfer->sender_user_id
            !== auth()->id(),

            403

        );


        return view(
            'customer.savings-transfer.success',
            compact('transfer')
        );

    }



    public function failed()
    {

        return view(
            'customer.savings-transfer.failed'
        );

    }


}
