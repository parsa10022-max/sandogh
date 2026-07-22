<?php

namespace App\Services\Loan;

use InvalidArgumentException;
use App\Services\Date\JalaliDateService;
use App\Services\Money\MoneyService;

class LoanCalculationService
{
    public function __construct(
        private readonly JalaliDateService $jalaliDateService,
        private readonly MoneyService $moneyService,
    ) {
    }

    public function generate(
        int $loanAmount,
        int $installmentCount,
        string $startDate,
        int $interval = 1
    ): array {

        if ($installmentCount <= 0) {

            throw new InvalidArgumentException(
                'تعداد اقساط باید بیشتر از صفر باشد.'
            );

        }

        /*
        |--------------------------------------------------------------------------
        | برنامه اقساط
        |--------------------------------------------------------------------------
        */

        $dates = $this->jalaliDateService->generateSchedule(
            registerDate: $startDate,
            count: $installmentCount,
            interval: $interval
        );

        /*
        |--------------------------------------------------------------------------
        | مبلغ اقساط
        |--------------------------------------------------------------------------
        */

        $amounts = $this->moneyService->splitInstallments(
            $loanAmount,
            $installmentCount
        );

        $schedule = [];

        foreach ($dates as $index => $date) {

            $schedule[] = [

                'number' => $date['number'],

                'amount' => $amounts[$index]['amount'],

                // نمایش
                'jalali_date' => $date['jalali_date'],

                // ذخیره
                'gregorian_date' => $date['gregorian_date'],

            ];

        }

        $firstInstallment = $schedule[0];

        $lastInstallment = $schedule[count($schedule) - 1];

        return [

            'loan_amount' => $loanAmount,

            'installment_count' => $installmentCount,

            'base_installment_amount' => $amounts[0]['amount'],

            /*
            |--------------------------------------------------------------------------
            | نمایش
            |--------------------------------------------------------------------------
            */

            'start_date_jalali' => $startDate,

            'first_due_date_jalali'
            => $firstInstallment['jalali_date'],

            'last_due_date_jalali'
            => $lastInstallment['jalali_date'],

            /*
            |--------------------------------------------------------------------------
            | ذخیره
            |--------------------------------------------------------------------------
            */

            'first_due_date'
            => $firstInstallment['gregorian_date'],

            'last_due_date'
            => $lastInstallment['gregorian_date'],

            /*
            |--------------------------------------------------------------------------
            | برنامه اقساط
            |--------------------------------------------------------------------------
            */

            'schedule' => $schedule,

        ];
    }
}
