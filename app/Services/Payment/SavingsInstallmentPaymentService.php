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

    public function pay(Installment $installment): LoanPayment
    {
        $customer = auth()->user()?->customer;

        if (! $customer) {
            throw new \DomainException('عضو صندوق یافت نشد.');
        }

        return DB::transaction(function () use ($installment, $customer) {

            $installment = Installment::query()
                ->with(['loan.customer'])
                ->lockForUpdate()
                ->findOrFail($installment->id);

            if ($installment->loan->status !== LoanStatus::ACTIVE) {
                throw new \DomainException('این وام فعال نیست.');
            }

            if ($installment->status === InstallmentStatus::PAID) {
                throw new \DomainException('این قسط قبلاً پرداخت شده است.');
            }

            if ($installment->loan->customer_id !== $customer->id) {
                throw new \DomainException('این قسط متعلق به شما نیست.');
            }

            $hasPreviousUnpaid = $installment->loan
                ->installments()
                ->where(
                    'installment_number',
                    '<',
                    $installment->installment_number
                )
                ->where('status', InstallmentStatus::PENDING)
                ->exists();

            if ($hasPreviousUnpaid) {
                throw new \DomainException(
                    'ابتدا باید اقساط قبلی پرداخت شوند.'
                );
            }

            $account = Account::query()
                ->where('customer_id', $customer->id)
                ->where('account_type', AccountType::SAVING)
                ->lockForUpdate()
                ->first();

            if (! $account) {
                throw new \DomainException(
                    'حساب پس‌انداز برای شما پیدا نشد.'
                );
            }

            $amount = $installment->amount;

            if ($account->balance < $amount) {
                throw new \DomainException(
                    'موجودی حساب پس‌انداز برای پرداخت این قسط کافی نیست.'
                );
            }

            $balanceBefore = $account->balance;
            $balanceAfter = $balanceBefore - $amount;

            $account->decrement('balance', $amount);

            $trackingCode = $this->trackingCodeService->generate();

            $this->accountTransactionService->create(
                account: $account,
                type: TransactionType::INSTALLMENT_PAYMENT,
                source: TransactionSource::SYSTEM,
                paymentMethod: PaymentMethod::ACCOUNT_BALANCE,
                amount: $amount,
                balanceBefore: $balanceBefore,
                balanceAfter: $balanceAfter,
                createdBy: auth()->id(),
                description: 'پرداخت قسط از حساب پس‌انداز',
            );

            $payment = LoanPayment::create([
                'loan_id' => $installment->loan_id,
                'installment_id' => $installment->id,
                'user_id' => auth()->id(),
                'amount' => $amount,
                'tracking_code' => $trackingCode,
                'gateway' => null,
                'bank_transaction_id' => null,
                'bank_reference_number' => null,
                'paid_at' => now(),
            ]);

            $installment->update([
                'status' => InstallmentStatus::PAID,
                'paid_at' => now(),
            ]);

            $hasUnpaidInstallments = $installment->loan
                ->installments()
                ->where('status', InstallmentStatus::PENDING)
                ->exists();

            if (! $hasUnpaidInstallments) {
                $installment->loan->update([
                    'status' => LoanStatus::FINISHED,
                ]);
            }

            $loanOwner = $installment->loan->customer?->user;

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
                        ' ریال از حساب پس‌انداز پرداخت شد. کد پیگیری: ' .
                        $payment->tracking_code,
                    'data' => [
                        'amount' => $payment->amount,
                        'loan_id' => $payment->loan_id,
                        'installment_id' => $payment->installment_id,
                        'installment_number' =>
                            $installment->installment_number,
                        'tracking_code' => $payment->tracking_code,
                        'payment_id' => $payment->id,
                        'paid_at' => $payment->paid_at,
                    ],
                    'read_at' => null,
                ]);
            }

            return $payment;
        });
    }
}
