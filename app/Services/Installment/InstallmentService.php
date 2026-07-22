<?php

namespace App\Services\Installment;

use App\Enums\InstallmentStatus;
use App\Models\Installment;
use App\Models\Loan;
use Illuminate\Support\Facades\DB;

class InstallmentService
{
    /**
     * ساخت اقساط وام
     */
    public function createForLoan(
        Loan $loan,
        array $schedule
    ): void {

        DB::transaction(function () use ($loan, $schedule) {


            foreach ($schedule as $item) {

                Installment::create([

                    'loan_id' => $loan->id,

                    'installment_number' => $item['number'],

                    'amount' => (int) $item['amount'],

                    'due_date' => $item['gregorian_date'],

                    'status' => InstallmentStatus::UNPAID,

                    'created_by' => auth()->id(),

                ]);

            }

        });

    }

    /**
     * دریافت اقساط
     */
    public function getByLoan(
        Loan $loan
    ) {

        return $loan->installments()
            ->orderBy('installment_number')
            ->get();

    }

    /**
     * پرداخت قسط
     */
    public function pay(
        Installment $installment
    ): Installment {

        $installment->update([

            'status' => InstallmentStatus::PAID,

            'paid_at' => now(),

            'updated_by' => auth()->id(),

        ]);

        return $installment->fresh();

    }

    /**
     * لغو پرداخت
     */
    public function cancelPayment(
        Installment $installment
    ): Installment {

        $installment->update([

            'status' => InstallmentStatus::UNPAID,

            'paid_at' => null,

            'updated_by' => auth()->id(),

        ]);

        return $installment->fresh();

    }

    /**
     * تعداد اقساط پرداخت نشده
     */
    public function unpaidCount(
        Loan $loan
    ): int {

        return $loan->installments()
            ->where('status', InstallmentStatus::UNPAID)
            ->count();

    }

    /**
     * مبلغ پرداخت شده
     */
    public function paidAmount(
        Loan $loan
    ): int {

        return (int) $loan->installments()
            ->where('status', InstallmentStatus::PAID)
            ->sum('amount');

    }
}
