<?php

namespace App\Services\Savings;

use App\Models\SavingsTransfer;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class SavingsTransferTrackingCodeService
{
    private const PREFIX = 'ST';

    private const SEQUENCE_LENGTH = 6;

    private const MAX_SEQUENCE = 999999;

    public function generate(): string
    {
        return DB::transaction(function (): string {
            $today = Jalalian::now()->format('Ymd');

            DB::table('savings_transfer_tracking_sequences')
                ->insertOrIgnore([
                    'jalali_date' => $today,
                    'last_sequence' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            $sequenceRow = DB::table('savings_transfer_tracking_sequences')
                ->where('jalali_date', $today)
                ->lockForUpdate()
                ->first();

            if (!$sequenceRow) {
                throw new \RuntimeException(
                    'رکورد Sequence کد واریز پس‌انداز ایجاد نشد.'
                );
            }

            $nextSequence = ((int) $sequenceRow->last_sequence) + 1;

            if ($nextSequence > self::MAX_SEQUENCE) {
                throw new \RuntimeException(
                    'ظرفیت کد رهگیری واریز پس‌انداز برای امروز تکمیل شده است.'
                );
            }

            DB::table('savings_transfer_tracking_sequences')
                ->where('id', $sequenceRow->id)
                ->update([
                    'last_sequence' => $nextSequence,
                    'updated_at' => now(),
                ]);

            return sprintf(
                '%s%s%0' . self::SEQUENCE_LENGTH . 'd',
                self::PREFIX,
                $today,
                $nextSequence
            );
        });
    }
}
