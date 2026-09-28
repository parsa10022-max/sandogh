<?php

namespace App\Services\Account;

use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class AccountTransactionNoService
{
    private const PREFIX = 'AT';

    private const SEQUENCE_LENGTH = 6;

    public function generate(): string
    {
        $today = Jalalian::now()->format('Ymd');

        $sequence = DB::transaction(function () use ($today): int {

            /*
            |--------------------------------------------------------------------------
            | ایجاد رکورد روز در صورت نبودن
            |--------------------------------------------------------------------------
            |
            | insertOrIgnore باعث می‌شود اگر درخواست دیگری همزمان
            | همین تاریخ را ایجاد کرده باشد، خطای Duplicate نگیریم.
            |
            */

            DB::table('account_transaction_sequences')->insertOrIgnore([
                'jalali_date' => $today,
                'sequence' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | قفل رکورد روز
            |--------------------------------------------------------------------------
            */

            $record = DB::table('account_transaction_sequences')
                ->where('jalali_date', $today)
                ->lockForUpdate()
                ->first();

            if (! $record) {
                throw new \RuntimeException(
                    'رکورد شمارنده تراکنش پیدا نشد.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | افزایش شماره
            |--------------------------------------------------------------------------
            */

            $nextSequence = $record->sequence + 1;

            DB::table('account_transaction_sequences')
                ->where('id', $record->id)
                ->update([
                    'sequence' => $nextSequence,
                    'updated_at' => now(),
                ]);

            return $nextSequence;
        });

        return sprintf(
            '%s%s%0' . self::SEQUENCE_LENGTH . 'd',
            self::PREFIX,
            $today,
            $sequence
        );
    }
}
