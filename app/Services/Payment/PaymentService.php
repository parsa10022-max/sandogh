<?php

namespace App\Services\Payment;

use App\Enums\InstallmentStatus;
use App\Enums\LoanStatus;
use App\Models\Installment;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Notification;
use App\Services\Payment\Gateways\GatewayInterface;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        private readonly GatewayInterface $gateway,
        private readonly TrackingCodeService $trackingCodeService,
    ) {
    }

    /**
     * شروع فرآیند پرداخت
     */
    public function startPayment(
        Installment $installment
    ): array {

        $this->validatePayment($installment);

        $trackingCode = $this->trackingCodeService->generate();

        return $this->gateway->request([

            'payment_type' => 'installment',

            'loan_id' => $installment->loan_id,

            'installment_id' => $installment->id,

            'reference_id' => $installment->id,

            'amount' => $installment->amount,

            'tracking_code' => $trackingCode,

            'callback_url' => route('payments.callback'),

        ]);
    }

    /**
     * تایید پرداخت
     */
    public function verifyPayment(
        array $callbackData
    ): LoanPayment {

        $gatewayResponse = $this->gateway->verify(
            $callbackData
        );

        if (! $gatewayResponse['success']) {

            throw new \DomainException(
                $gatewayResponse['message']
                ?? 'پرداخت ناموفق بود.'
            );

        }

        $installment = Installment::query()
            ->with('loan.customer.user')
            ->findOrFail(
                $callbackData['installment_id']
            );

        return DB::transaction(function () use (
            $installment,
            $gatewayResponse,
            $callbackData
        ) {

            /*
            |--------------------------------------------------------------------------
            | ثبت پرداخت
            |--------------------------------------------------------------------------
            */

            $payment = $this->createLoanPayment(
                $installment,
                $gatewayResponse,
                $callbackData['tracking_code']
            );

            /*
            |--------------------------------------------------------------------------
            | پرداخت شدن قسط
            |--------------------------------------------------------------------------
            */

            $this->markInstallmentAsPaid(
                $installment
            );

            /*
            |--------------------------------------------------------------------------
            | تسویه وام در صورت پرداخت تمام اقساط
            |--------------------------------------------------------------------------
            */

            $this->finishLoanIfNeeded(
                $installment->loan
            );

            /*
            |--------------------------------------------------------------------------
            | اطلاعات صاحب وام
            |--------------------------------------------------------------------------
            */

            $loanOwner = $installment->loan
                ->customer
                ?->user;

            /*
            |--------------------------------------------------------------------------
            | اعلان برای صاحب وام
            |--------------------------------------------------------------------------
            */

            if ($loanOwner) {

                Notification::create([

                    'user_id' => $loanOwner->id,

                    'type' => 'installment_payment_success',

                    'title' => 'پرداخت قسط با موفقیت انجام شد.',

                    'message' =>
                        'قسط شماره ' .
                        $installment->installment_number .
                        ' به مبلغ ' .
                        number_format($payment->amount) .
                        ' ریال با موفقیت پرداخت شد. کد پیگیری: ' .
                        $payment->tracking_code,

                    'data' => [

                        'amount' =>
                            $payment->amount,

                        'loan_id' =>
                            $payment->loan_id,

                        'installment_id' =>
                            $payment->installment_id,

                        'installment_number' =>
                            $installment->installment_number,

                        'installment_count' =>
                            $installment->loan
                                ->installments()
                                ->count(),

                        'tracking_code' =>
                            $payment->tracking_code,

                        'payment_id' =>
                            $payment->id,

                        'paid_at' =>
                            $payment->paid_at,

                    ],

                    'read_at' => null,

                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | اعلان پرداخت قسط دیگران برای پرداخت‌کننده
            |--------------------------------------------------------------------------
            */

            $payerUser = auth()->user();

            if (
                $payerUser &&
                $loanOwner &&
                $payerUser->id !== $loanOwner->id
            ) {

                $loanOwnerCustomer =
                    $installment->loan->customer;

                Notification::create([

                    'user_id' => $payerUser->id,

                    'type' => 'other_installment_payment_success',

                    'title' => 'پرداخت قسط دیگران با موفقیت انجام شد.',

                    'message' =>
                        'قسط شماره ' .
                        $installment->installment_number .
                        ' وام ' .
                        $installment->loan->loan_number .
                        ' به مبلغ ' .
                        number_format($payment->amount) .
                        ' ریال برای ' .
                        ($loanOwnerCustomer?->full_name ?? 'عضو صندوق') .
                        ' با موفقیت پرداخت شد. کد پیگیری: ' .
                        $payment->tracking_code,

                    'data' => [

                        'amount' =>
                            $payment->amount,

                        'loan_id' =>
                            $payment->loan_id,

                        'loan_number' =>
                            $installment->loan->loan_number,

                        'installment_id' =>
                            $payment->installment_id,

                        'installment_number' =>
                            $installment->installment_number,

                        'installment_count' =>
                            $installment->loan
                                ->installments()
                                ->count(),

                        'customer_id' =>
                            $loanOwnerCustomer?->id,

                        'customer_name' =>
                            $loanOwnerCustomer?->full_name,

                        'tracking_code' =>
                            $payment->tracking_code,

                        'payment_id' =>
                            $payment->id,

                        'paid_at' =>
                            $payment->paid_at,

                    ],

                    'read_at' => null,

                ]);
            }

            return $payment;
        });
    }

    /**
     * اعتبارسنجی پرداخت
     */
    private function validatePayment(
        Installment $installment
    ): void {

        // وام باید فعال باشد
        if ($installment->loan->status !== LoanStatus::ACTIVE) {

            throw new \DomainException(
                'این وام فعال نیست.'
            );
        }

        // قسط نباید قبلاً پرداخت شده باشد
        if ($installment->status === InstallmentStatus::PAID) {

            throw new \DomainException(
                'این قسط قبلاً پرداخت شده است.'
            );
        }

        // همه اقساط قبلی باید پرداخت شده باشند
        $hasPreviousUnpaid = $installment->loan
            ->installments()
            ->where(
                'installment_number',
                '<',
                $installment->installment_number
            )
            ->where(
                'status',
                InstallmentStatus::PENDING
            )
            ->exists();

        if ($hasPreviousUnpaid) {

            throw new \DomainException(
                'ابتدا باید اقساط قبلی پرداخت شوند.'
            );
        }
    }

    /**
     * ثبت پرداخت
     */
    private function createLoanPayment(
        Installment $installment,
        array $gatewayResponse,
        string $trackingCode
    ): LoanPayment {

        return LoanPayment::create([

            'loan_id' =>
                $installment->loan_id,

            'installment_id' =>
                $installment->id,

            'user_id' =>
                auth()->id(),

            'amount' =>
                $installment->amount,

            'tracking_code' =>
                $trackingCode,

            'gateway' =>
                config('payment.gateway'),

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
     * علامت‌گذاری قسط به عنوان پرداخت‌شده
     */
    private function markInstallmentAsPaid(
        Installment $installment
    ): void {

        $installment->update([

            'status' =>
                InstallmentStatus::PAID,

            'paid_at' =>
                now(),

        ]);
    }

    /**
     * در صورت پرداخت تمام اقساط، وام را تسویه می‌کند.
     */
    private function finishLoanIfNeeded(
        Loan $loan
    ): void {

        $hasUnpaidInstallments = $loan
            ->installments()
            ->where(
                'status',
                InstallmentStatus::PENDING
            )
            ->exists();

        if (! $hasUnpaidInstallments) {

            $loan->update([

                'status' =>
                    LoanStatus::FINISHED,

            ]);
        }
    }

    /**
     * پیدا کردن اولین قسط قابل پرداخت با شماره وام
     */
    public function findPayableInstallmentByLoanNumber(
        string $loanNumber
    ): ?Installment {

        $loan = Loan::query()
            ->with([
                'customer',
                'loanType',
                'installments'
            ])
            ->where(
                'loan_number',
                $loanNumber
            )
            ->first();

        if (! $loan) {

            return null;
        }

        if ($loan->status !== LoanStatus::ACTIVE) {

            throw new \DomainException(
                'این وام فعال نیست.'
            );
        }

        return $loan->installments()
            ->where(
                'status',
                InstallmentStatus::PENDING
            )
            ->orderBy(
                'installment_number'
            )
            ->first();
    }
}

