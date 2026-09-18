<?php

namespace App\Services\FundStatistic;

use App\Models\Customer;
use App\Models\FundStatistic;

class FundStatisticService
{
    public function get(): array
    {
        $statistic = FundStatistic::query()->first();

        return [
            'members' => Customer::query()->count(),
            'paid_loans' => $statistic?->paid_loans_count ?? 0,
            'paid_loan_amount' => $statistic?->paid_loans_amount ?? 0,
            'donations' => $statistic?->donations_count ?? 0,
            'statistics_date' => $statistic?->statistics_date,
        ];
    }

    public function update(array $data): FundStatistic
    {
        $statistic = FundStatistic::query()->first();

        if (!$statistic) {
            return FundStatistic::query()->create([
                'paid_loans_count' => $data['paid_loans_count'],
                'paid_loans_amount' => $data['paid_loans_amount'],
                'donations_count' => $data['donations_count'],
                'statistics_date' => $data['statistics_date'] ?? null,
            ]);
        }

        $statistic->update([
            'paid_loans_count' =>
                $statistic->paid_loans_count + $data['paid_loans_count'],

            'paid_loans_amount' =>
                $statistic->paid_loans_amount + $data['paid_loans_amount'],

            'donations_count' =>
                $statistic->donations_count + $data['donations_count'],

            'statistics_date' =>
                $data['statistics_date'] ?? $statistic->statistics_date,
        ]);

        return $statistic->fresh();
    }
}
