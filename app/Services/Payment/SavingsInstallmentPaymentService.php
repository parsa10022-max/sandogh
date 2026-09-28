<?php

namespace App\Services\Payment;

use App\Enums\AccountType;
use App\Enums\InstallmentStatus;
use App\Enums\LoanStatus;
use App\Enums\PaymentMethod;
use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Installment;
use App\Models\LoanPayment;
use App\Models\Notification;
use App\Services\Account\AccountTransactionService;
use Illuminate\Support\Facades\DB;

class SavingsInstallmentPaymentService
{
    public function __construct(
        private readonly AccountTransactionService $accountTransactionService,
        private readonly TrackingCodeService $trackingCodeService,
    ) {
    }

    /**
     * پرداخت قسط از حساب پس‌انداز
     */
    public function pay(Installment $installment): LoanPayment
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

        $userId = $user->id;

        return DB::transaction(function () use (
            $installment,
            $customer,
            $userId
        ) {
            /*
            |--------------------------------------------------------------------------
            | قفل وام
            |--------------------------------------------------------------------------
            */

            $loanId = Installment::query()
                ->whereKey($installment->id)
                ->value('loan_id');

            if (! $loanId) {
                throw new \DomainException(
                    'قسط موردنظر پیدا نشد.'
                );
            }

            $loan = \App\Models\Loan::query()
                ->with('customer')
                ->lockForUpdate()
                ->find($loanId);

            if (! $loan) {
                throw new \DomainException(
                    'وام موردنظر پیدا نشد.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | قفل قسط
            |--------------------------------------------------------------------------
            */

            $installment = Installment::query()
                ->lockForUpdate()
                ->find($installment->id);

            if (! $installment) {
                throw new \DomainException(
                    'قسط موردنظر پیدا نشد.'
                );
            }

            if (
                (int) $installment->loan_id !==
                (int) $loan->id
            ) {
                throw new \DomainException(
                    'اطلاعات قسط و وام معتبر نیست.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | پرداخت تکراری
            |--------------------------------------------------------------------------
            */

            $existingPayment = LoanPayment::query()
                ->where(
                    'installment_id',
                    $installment->id
                )
                ->first();

            if ($existingPayment) {
                return $existingPayment;
            }

            /*
            |--------------------------------------------------------------------------
            | وضعیت وام
            |--------------------------------------------------------------------------
            */

            if ($loan->status !== LoanStatus::ACTIVE) {
                throw new \DomainException(
                    'این وام فعال نیست.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | وضعیت قسط
            |--------------------------------------------------------------------------
            */

            if ($installment->status === InstallmentStatus::PAID) {
                throw new \DomainException(
                    'این قسط قبلاً پرداخت شده است.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | مالکیت
            |--------------------------------------------------------------------------
            */

            if (
                (int) $loan->customer_id !==
                (int) $customer->id
            ) {
                throw new \DomainException(
                    'این قسط متعلق به شما نیست.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | ترتیب پرداخت اقساط
            |--------------------------------------------------------------------------
            |
            | PENDING و OVERDUE هر دو پرداخت‌نشده هستند.
            |--------------------------------------------------------------------------
            */

            $hasPreviousUnpaid = $loan
                ->installments()
                ->where(
                    'installment_number',
                    '<',
                    $installment->installment_number
                )
                ->whereIn(
                    'status',
                    [
                        InstallmentStatus::PENDING,
                        InstallmentStatus::OVERDUE,
                    ]
                )
                ->exists();

            if ($hasPreviousUnpaid) {
                throw new \DomainException(
                    'ابتدا باید اقساط قبلی پرداخت شوند.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | قفل حساب پس‌انداز
            |--------------------------------------------------------------------------
            */

            $account = Account::query()
                ->where(
                    'customer_id',
                    $customer->id
                )
                ->where(
                    'account_type',
                    AccountType::SAVING
                )
                ->lockForUpdate()
                ->first();

            if (! $account) {
                throw new \DomainException(
                    'حساب پس‌انداز برای شما پیدا نشد.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | بررسی موجودی
            |--------------------------------------------------------------------------
            */

            $amount = $installment->amount;

            if ($account->balance < $amount) {
                throw new \DomainException(
                    'موجودی حساب پس‌انداز برای پرداخت این قسط کافی نیست.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | تغییر موجودی
            |--------------------------------------------------------------------------
            */

            $balanceBefore = $account->balance;
            $balanceAfter = $balanceBefore - $amount;

            $account->decrement(
                'balance',
                $amount
            );

            /*
            |--------------------------------------------------------------------------
            | کد پیگیری
            |--------------------------------------------------------------------------
            */

            $trackingCode =
                $this->trackingCodeService->generate();

            /*
            |--------------------------------------------------------------------------
            | ثبت تراکنش حساب
            |--------------------------------------------------------------------------
            */

            $this->accountTransactionService->create(
                account: $account,
                type: TransactionType::INSTALLMENT_PAYMENT,
                source: TransactionSource::SYSTEM,
                paymentMethod: PaymentMethod::ACCOUNT_BALANCE,
                amount: $amount,
                balanceBefore: $balanceBefore,
                balanceAfter: $balanceAfter,
                createdBy: $userId,
                description: 'پرداخت قسط از حساب پس‌انداز',
            );

            /*
            |--------------------------------------------------------------------------
            | ثبت پرداخت قسط
            |--------------------------------------------------------------------------
            */

            $payment = LoanPayment::create([
                'loan_id' =>
                    $installment->loan_id,

                'installment_id' =>
                    $installment->id,

                'user_id' =>
                    $userId,

                'amount' =>
                    $amount,

                'tracking_code' =>
                    $trackingCode,

                'gateway' =>
                    null,

                'bank_transaction_id' =>
                    null,

                'bank_reference_number' =>
                    null,

                'paid_at' =>
                    now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | علامت‌گذاری قسط
            |--------------------------------------------------------------------------
            */

            $installment->update([
                'status' =>
                    InstallmentStatus::PAID,

                'paid_at' =>
                    now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | تسویه وام
            |--------------------------------------------------------------------------
            */

            $hasUnpaidInstallments = $loan
                ->installments()
                ->whereIn(
                    'status',
                    [
                        InstallmentStatus::PENDING,
                        InstallmentStatus::OVERDUE,
                    ]
                )
                ->exists();

            if (! $hasUnpaidInstallments) {
                $loan->update([
                    'status' =>
                        LoanStatus::FINISHED,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | اعلان صاحب وام
            |--------------------------------------------------------------------------
            */

            $loanOwner = $loan
                ->customer
                ?->user;

            if ($loanOwner) {
                Notification::create([
                    'user_id' =>
                        $loanOwner->id,

                    'type' =>
                        'installment_payment_success',

                    'title' =>
                        'پرداخت قسط با موفقیت انجام شد.',

                    'message' =>
                        'قسط شماره ' .
                        $installment->installment_number .
                        ' به مبلغ ' .
                        fa_money($payment->amount) .
                        ' ریال از حساب پس‌انداز پرداخت شد. کد پیگیری: ' .
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

                        'tracking_code' =>
                            $payment->tracking_code,

                        'payment_id' =>
                            $payment->id,

                        'paid_at' =>
                            $payment->paid_at,
                    ],

                    'read_at' =>
                        null,
                ]);
            }

            return $payment;
        });
    }
}
