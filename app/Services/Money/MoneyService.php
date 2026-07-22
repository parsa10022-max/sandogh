<?php

namespace App\Services\Money;

class MoneyService
{
    /**
     * تقسیم مبلغ بین اقساط
     */
    public function splitInstallments(
        int $loanAmount,
        int $count
    ): array {

        if ($count <= 0) {
            return [];
        }

        $installments = [];

        // مبلغ پایه هر قسط
        $baseAmount = intdiv(
            $loanAmount,
            $count
        );

        // باقیمانده
        $remainder = $loanAmount % $count;

        for ($number = 1; $number <= $count; $number++) {

            $amount = $baseAmount;

            // اختلاف فقط روی آخرین قسط
            if ($number === $count) {
                $amount += $remainder;
            }

            $installments[] = [
                'number' => $number,
                'amount' => $amount,
            ];
        }

        return $installments;
    }
}
