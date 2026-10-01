<?php

namespace Tests\Feature\Savings;

use App\Services\Savings\SavingsTransferTrackingCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;
use Tests\TestCase;

class SavingsTransferTrackingCodeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_tracking_code_has_correct_format(): void
    {
        $service = app(SavingsTransferTrackingCodeService::class);

        $trackingCode = $service->generate();

        $today = Jalalian::now()->format('Ymd');

        $this->assertMatchesRegularExpression(
            '/^ST' . $today . '\d{6}$/',
            $trackingCode
        );
    }

    public function test_first_tracking_code_starts_with_sequence_one(): void
    {
        $service = app(SavingsTransferTrackingCodeService::class);

        $trackingCode = $service->generate();

        $sequence = (int) substr($trackingCode, -6);

        $this->assertSame(1, $sequence);
    }

    public function test_tracking_codes_are_sequential(): void
    {
        $service = app(SavingsTransferTrackingCodeService::class);

        $first = $service->generate();
        $second = $service->generate();
        $third = $service->generate();

        $this->assertSame(
            1,
            (int) substr($first, -6)
        );

        $this->assertSame(
            2,
            (int) substr($second, -6)
        );

        $this->assertSame(
            3,
            (int) substr($third, -6)
        );

        $this->assertNotSame($first, $second);
        $this->assertNotSame($second, $third);
        $this->assertNotSame($first, $third);
    }

    public function test_sequence_is_stored_in_database(): void
    {
        $service = app(SavingsTransferTrackingCodeService::class);

        $trackingCode = $service->generate();

        $today = Jalalian::now()->format('Ymd');

        $sequence = DB::table('savings_transfer_tracking_sequences')
            ->where('jalali_date', $today)
            ->first();

        $this->assertNotNull($sequence);

        $this->assertSame(
            1,
            (int) $sequence->last_sequence
        );

        $this->assertSame(
            'ST' . $today . '000001',
            $trackingCode
        );
    }

    public function test_existing_sequence_continues_from_last_number(): void
    {
        $today = Jalalian::now()->format('Ymd');

        DB::table('savings_transfer_tracking_sequences')->insert([
            'jalali_date' => $today,
            'last_sequence' => 25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $service = app(SavingsTransferTrackingCodeService::class);

        $trackingCode = $service->generate();

        $this->assertSame(
            'ST' . $today . '000026',
            $trackingCode
        );

        $sequence = DB::table('savings_transfer_tracking_sequences')
            ->where('jalali_date', $today)
            ->first();

        $this->assertSame(
            26,
            (int) $sequence->last_sequence
        );
    }
}
