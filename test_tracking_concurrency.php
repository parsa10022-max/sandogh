<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$today = Jalalian::now()->format('Ymd');

$connection = DB::connection('mysql');

echo "Database: " . $connection->getDatabaseName() . PHP_EOL;
echo "Driver: " . $connection->getPdo()->getAttribute(PDO::ATTR_DRIVER_NAME) . PHP_EOL;
echo "Date: " . $today . PHP_EOL;
echo PHP_EOL;

$worker = $argv[1] ?? null;

/*
|--------------------------------------------------------------------------
| Worker 1
|--------------------------------------------------------------------------
*/

if ($worker === 'worker1') {
    $connection->beginTransaction();

    try {
        echo "[Worker 1] BEGIN" . PHP_EOL;

        $sequence = $connection->table('payment_tracking_sequences')
            ->where('jalali_date', $today)
            ->lockForUpdate()
            ->first();

        if (! $sequence) {
            throw new RuntimeException(
                'Sequence row not found.'
            );
        }

        echo "[Worker 1] LOCK ACQUIRED" . PHP_EOL;

        $nextSequence = (int) $sequence->last_sequence + 1;

        echo "[Worker 1] Current: {$sequence->last_sequence}" . PHP_EOL;
        echo "[Worker 1] Next: {$nextSequence}" . PHP_EOL;

        $connection->table('payment_tracking_sequences')
            ->where('id', $sequence->id)
            ->update([
                'last_sequence' => $nextSequence,
                'updated_at' => now(),
            ]);

        echo "[Worker 1] Sequence updated to {$nextSequence}" . PHP_EOL;

        echo "[Worker 1] Holding lock for 5 seconds..." . PHP_EOL;

        sleep(5);

        $connection->commit();

        echo "[Worker 1] COMMIT" . PHP_EOL;

        exit(0);
    } catch (\Throwable $e) {
        if ($connection->transactionLevel() > 0) {
            $connection->rollBack();
        }

        echo "[Worker 1] ERROR: {$e->getMessage()}" . PHP_EOL;

        exit(1);
    }
}

/*
|--------------------------------------------------------------------------
| Worker 2
|--------------------------------------------------------------------------
*/

if ($worker === 'worker2') {
    sleep(1);

    $start = microtime(true);

    $connection->beginTransaction();

    try {
        echo "[Worker 2] BEGIN" . PHP_EOL;
        echo "[Worker 2] Waiting for lock..." . PHP_EOL;

        $sequence = $connection->table('payment_tracking_sequences')
            ->where('jalali_date', $today)
            ->lockForUpdate()
            ->first();

        if (! $sequence) {
            throw new RuntimeException(
                'Sequence row not found.'
            );
        }

        $waited = round(
            microtime(true) - $start,
            2
        );

        echo "[Worker 2] LOCK ACQUIRED after {$waited}s" . PHP_EOL;

        $nextSequence = (int) $sequence->last_sequence + 1;

        echo "[Worker 2] Current: {$sequence->last_sequence}" . PHP_EOL;
        echo "[Worker 2] Next: {$nextSequence}" . PHP_EOL;

        $connection->table('payment_tracking_sequences')
            ->where('id', $sequence->id)
            ->update([
                'last_sequence' => $nextSequence,
                'updated_at' => now(),
            ]);

        $connection->commit();

        echo "[Worker 2] COMMIT" . PHP_EOL;

        exit(0);
    } catch (\Throwable $e) {
        if ($connection->transactionLevel() > 0) {
            $connection->rollBack();
        }

        echo "[Worker 2] ERROR: {$e->getMessage()}" . PHP_EOL;

        exit(1);
    }
}

/*
|--------------------------------------------------------------------------
| Parent Process
|--------------------------------------------------------------------------
|
| فقط Parent رکورد اولیه را ایجاد می‌کند.
| Workerها دیگر نباید این بخش را اجرا کنند.
|
*/

$connection->table('payment_tracking_sequences')
    ->where('jalali_date', $today)
    ->delete();

$connection->table('payment_tracking_sequences')->insert([
    'jalali_date' => $today,
    'last_sequence' => 0,
    'created_at' => now(),
    'updated_at' => now(),
]);

echo "Initial sequence created with value 0." . PHP_EOL;
echo PHP_EOL;

$php = PHP_BINARY;

echo "Starting Worker 1..." . PHP_EOL;

$worker1 = proc_open(
    '"' . $php . '" "' . __FILE__ . '" worker1',
    [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ],
    $pipes1
);

if (! is_resource($worker1)) {
    throw new RuntimeException(
        'Worker 1 could not be started.'
    );
}

echo "Starting Worker 2..." . PHP_EOL;

$worker2 = proc_open(
    '"' . $php . '" "' . __FILE__ . '" worker2',
    [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ],
    $pipes2
);

if (! is_resource($worker2)) {
    proc_terminate($worker1);

    throw new RuntimeException(
        'Worker 2 could not be started.'
    );
}

$output1 = stream_get_contents($pipes1[1]);
$error1 = stream_get_contents($pipes1[2]);

$output2 = stream_get_contents($pipes2[1]);
$error2 = stream_get_contents($pipes2[2]);

fclose($pipes1[0]);
fclose($pipes1[1]);
fclose($pipes1[2]);

fclose($pipes2[0]);
fclose($pipes2[1]);
fclose($pipes2[2]);

$exit1 = proc_close($worker1);
$exit2 = proc_close($worker2);

echo PHP_EOL;
echo "========== Worker 1 ==========" . PHP_EOL;
echo $output1;

if ($error1 !== '') {
    echo "ERROR:" . PHP_EOL;
    echo $error1;
}

echo PHP_EOL;
echo "========== Worker 2 ==========" . PHP_EOL;
echo $output2;

if ($error2 !== '') {
    echo "ERROR:" . PHP_EOL;
    echo $error2;
}

$final = $connection->table('payment_tracking_sequences')
    ->where('jalali_date', $today)
    ->first();

echo PHP_EOL;
echo "========== Result ==========" . PHP_EOL;
echo "Worker 1 exit code: {$exit1}" . PHP_EOL;
echo "Worker 2 exit code: {$exit2}" . PHP_EOL;
echo "Final sequence: " . ($final?->last_sequence ?? 'NULL') . PHP_EOL;

if (
    $exit1 === 0 &&
    $exit2 === 0 &&
    $final !== null &&
    (int) $final->last_sequence === 2
) {
    echo PHP_EOL;
    echo "SUCCESS: MySQL row lock prevented duplicate sequence numbers." . PHP_EOL;

    exit(0);
}

echo PHP_EOL;
echo "FAILED: Concurrency test did not produce the expected result." . PHP_EOL;

exit(1);
