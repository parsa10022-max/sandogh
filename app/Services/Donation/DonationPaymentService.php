<?php

namespace App\Services\Donation;

use App\Enums\AccountStatus;
use App\Enums\PaymentMethod;
use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Customer;
use App\Models\DonationPayment;
use App\Services\Account\AccountService;
use App\Services\Account\AccountTransactionService;
use App\Services\Payment\Gateways\GatewayInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class DonationPaymentService
{

    public function __construct(

        private readonly GatewayInterface $gateway,

        private readonly AccountTransactionService $accountTransactionService,

        private readonly AccountService $accountService,

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


        if ($amount <= 0) {

            throw new \DomainException(
                'مبلغ کمک باید بیشتر از صفر باشد.'
            );

        }



        if (
            $account->status !== AccountStatus::ACTIVE
        ) {

            throw new \DomainException(
                'حساب مقصد فعال نیست.'
            );

        }



        $trackingCode =
            'DON-' . strtoupper(
                Str::random(10)
            );




        $payment = DB::transaction(function () use (

            $customer,

            $account,

            $amount,

            $trackingCode,

            $donorName,

            $donorMobile

        ) {


            return DonationPayment::create([

                'customer_id' =>
                    $customer?->id,


                'donor_name' =>
                    $donorName,


                'donor_mobile' =>
                    $donorMobile,


                'account_id' =>
                    $account->id,


                'amount' =>
                    $amount,


                'tracking_code' =>
                    $trackingCode,


                'gateway' =>
                    config('payment.gateway'),


                'status' => 0,


            ]);


        });





        $gatewayResponse =
            $this->gateway->request([

                'payment_type' =>
                    $paymentType,


                'reference_id' =>
                    $payment->id,


                'amount' =>
                    $amount,


                'tracking_code' =>
                    $trackingCode,


                'callback_url' =>
                    route('payments.callback'),


            ]);





        if (! $gatewayResponse['success']) {


            $payment->update([

                'status' => 2,

            ]);


            throw new \DomainException(

                $gatewayResponse['message']
                ??
                'خطا در اتصال به درگاه'

            );

        }





        return [

            'payment' =>
                $payment,


            'gateway' =>
                $gatewayResponse,


        ];

    }





    /**
     * تایید پرداخت
     */
    public function verifyPayment(
        array $callbackData
    ): DonationPayment {

        $gatewayResponse =
            $this->gateway->verify(
                $callbackData
            );


        if (! $gatewayResponse['success']) {

            throw new \DomainException(
                $gatewayResponse['message']
                ??
                'پرداخت ناموفق بود.'
            );

        }



        return DB::transaction(function () use (

            $callbackData,

            $gatewayResponse

        ) {


            $payment =
                DonationPayment::query()

                    ->lockForUpdate()

                    ->findOrFail(
                        $callbackData['reference_id']
                    );



            if ($payment->status === 1) {

                return $payment;

            }



            $account =
                Account::query()

                    ->lockForUpdate()

                    ->findOrFail(
                        $payment->account_id
                    );



            /*
            |--------------------------------------------------------------------------
            | توضیح تراکنش
            |--------------------------------------------------------------------------
            */

            if ($payment->customer_id) {

                $description =
                    'کمک آنلاین عضو صندوق';

            } else {

                $description =
                    'کمک آنلاین از طرف '
                    .
                    ($payment->donor_name ?: 'فرد خارج از صندوق');

            }



            $balanceBefore =
                $account->balance;



            $balanceAfter =
                $balanceBefore
                +
                $payment->amount;



            $this->accountService->depositBalance(

                $account,

                $payment->amount

            );



            $this->accountTransactionService->create(

                account: $account,

                type: TransactionType::DEPOSIT,

                source: TransactionSource::ONLINE,

                paymentMethod: PaymentMethod::GATEWAY,

                amount: $payment->amount,

                balanceBefore: $balanceBefore,

                balanceAfter: $balanceAfter,

                createdBy: null,

                description: $description

            );



            $payment->update([

                'status' => 1,

                'bank_transaction_id' =>
                    $gatewayResponse['transaction_id']
                    ?? null,

                'bank_reference_number' =>
                    $gatewayResponse['reference_number']
                    ?? null,

                'paid_at' =>
                    now(),

            ]);



            return $payment->fresh();


        });

    }
    /**
     * ارسال پرداخت موجود به درگاه
     */
    public function sendToGateway(
        DonationPayment $payment,
        string $paymentType = 'donation_customer'
    ): array {

        return $this->gateway->request([

            'payment_type' =>
                $paymentType,

            'reference_id' =>
                $payment->id,

            'amount' =>
                $payment->amount,

            'tracking_code' =>
                $payment->tracking_code,

            'callback_url' =>
                route('payments.callback'),

        ]);
    }

}
