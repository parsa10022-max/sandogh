<?php

namespace App\Services\Payment;

use App\Models\PaymentTrackingSequence;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;
use RuntimeException;

class TrackingCodeService
{
    /**
     * پیشوند کد رهگیری پرداخت وام
     */
    private const PREFIX = 'LP';

    /**
     * طول شماره ترتیبی
     */
    private const SEQUENCE_LENGTH = 6;

    /**
     * تولید کد رهگیری
     */
    public function generate(): string
    {
        return $this->generateLoanPaymentCode();
    }

    /**
     * تولید کد رهگیری پرداخت وام
     *
     * شماره‌گذاری به صورت اتمیک و با Row Lock انجام می‌شود.
     */
    public function generateLoanPaymentCode(): string
    {
        return DB::transaction(function () {
            $today = Jalalian::now()->format('Ymd');

            $sequence = PaymentTrackingSequence::query()
                ->where('jalali_date', $today)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                try {
                    $sequence = PaymentTrackingSequence::query()->create([
                        'jalali_date' => $today,
                        'last_sequence' => 0,
                    ]);
                } catch (\Throwable $e) {
                    /*
                     * ممکن است درخواست همزمان دیگری
                     * رکورد امروز را ساخته باشد.
                     */
                    $sequence = PaymentTrackingSequence::query()
                        ->where('jalali_date', $today)
                        ->lockForUpdate()
                        ->first();

                    if (! $sequence) {
                        throw $e;
                    }
                }
            }

            $sequence->last_sequence++;

            if ($sequence->last_sequence > 999999) {
                throw new RuntimeException(
                    'ظرفیت شماره‌گذاری کد رهگیری امروز به پایان رسیده است.'
                );
            }

            $sequence->save();

            return sprintf(
                '%s%s%0' . self::SEQUENCE_LENGTH . 'd',
                self::PREFIX,
                $today,
                $sequence->last_sequence
            );
        });
    }
}
