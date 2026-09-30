<?php

namespace Tests\Feature\Payment;

use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;
use Tests\TestCase;

class TrackingCodeConcurrencyTest extends TestCase
{
    /**
     * تست Lock روی اتصال واقعی MySQL.
     */
    public function test_mysql_lock_prevents_duplicate_sequence_numbers(): void
    {
        $mysql = DB::connection('mysql');

        $this->assertSame(
            'mysql',
            $mysql->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME)
        );

        $today = Jalalian::now()->format('Ymd');

        $mysql->table('payment_tracking_sequences')
            ->where('jalali_date', $today)
            ->delete();

        $mysql->table('payment_tracking_sequences')->insert([
            'jalali_date' => $today,
            'last_sequence' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $connection1 = DB::connection('mysql');
        $connection2 = DB::connection('mysql');

        $connection1->beginTransaction();

        try {
            $sequence1 = $connection1->table('payment_tracking_sequences')
                ->where('jalali_date', $today)
                ->lockForUpdate()
                ->first();

            $this->assertNotNull($sequence1);

            $nextSequence1 = (int) $sequence1->last_sequence + 1;

            $connection1->table('payment_tracking_sequences')
                ->where('id', $sequence1->id)
                ->update([
                    'last_sequence' => $nextSequence1,
                    'updated_at' => now(),
                ]);

            $connection1->commit();

            $connection2->beginTransaction();

            try {
                $sequence2 = $connection2->table('payment_tracking_sequences')
                    ->where('jalali_date', $today)
                    ->lockForUpdate()
                    ->first();

                $this->assertNotNull($sequence2);

                $nextSequence2 = (int) $sequence2->last_sequence + 1;

                $connection2->table('payment_tracking_sequences')
                    ->where('id', $sequence2->id)
                    ->update([
                        'last_sequence' => $nextSequence2,
                        'updated_at' => now(),
                    ]);

                $connection2->commit();
            } catch (\Throwable $e) {
                if ($connection2->transactionLevel() > 0) {
                    $connection2->rollBack();
                }

                throw $e;
            }

            $this->assertSame(1, $nextSequence1);
            $this->assertSame(2, $nextSequence2);

            $finalSequence = $mysql->table('payment_tracking_sequences')
                ->where('jalali_date', $today)
                ->first();

            $this->assertNotNull($finalSequence);
            $this->assertSame(
                2,
                (int) $finalSequence->last_sequence
            );
        } catch (\Throwable $e) {
            if ($connection1->transactionLevel() > 0) {
                $connection1->rollBack();
            }

            throw $e;
        }
    }
}
