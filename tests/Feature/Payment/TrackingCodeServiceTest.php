<?php

namespace Tests\Feature\Payment;

use App\Models\PaymentTrackingSequence;
use App\Services\Payment\TrackingCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Morilog\Jalali\Jalalian;
use Tests\TestCase;

class TrackingCodeServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * تولید کدهای متوالی باید شماره‌های یکتا و پشت‌سرهم تولید کند.
     */
    public function test_tracking_codes_are_sequential_and_unique(): void
    {
        $service = app(TrackingCodeService::class);

        $code1 = $service->generate();
        $code2 = $service->generate();
        $code3 = $service->generate();

        $this->assertNotSame($code1, $code2);
        $this->assertNotSame($code2, $code3);
        $this->assertNotSame($code1, $code3);

        $this->assertSame('000001', substr($code1, -6));
        $this->assertSame('000002', substr($code2, -6));
        $this->assertSame('000003', substr($code3, -6));
    }

    /**
     * شماره روز جاری باید در جدول Sequence ذخیره شود.
     */
    public function test_tracking_sequence_is_stored_for_today(): void
    {
        $today = Jalalian::now()->format('Ymd');

        $service = app(TrackingCodeService::class);

        $code = $service->generate();

        $this->assertSame(
            'LP' . $today . '000001',
            $code
        );

        $sequence = PaymentTrackingSequence::query()
            ->where('jalali_date', $today)
            ->first();

        $this->assertNotNull($sequence);

        $this->assertSame(
            1,
            $sequence->last_sequence
        );
    }

    /**
     * چند تولید متوالی باید Sequence را بدون پرش افزایش دهد.
     *
     * توجه:
     * این تست concurrency واقعی نیست.
     * تست واقعی همزمانی در مرحله بعد با MySQL انجام می‌شود.
     */
    public function test_multiple_generations_increment_sequence_correctly(): void
    {
        $today = Jalalian::now()->format('Ymd');

        PaymentTrackingSequence::query()->create([
            'jalali_date' => $today,
            'last_sequence' => 0,
        ]);

        $service = app(TrackingCodeService::class);

        $codes = [
            $service->generate(),
            $service->generate(),
            $service->generate(),
        ];

        $this->assertSame(
            '000001',
            substr($codes[0], -6)
        );

        $this->assertSame(
            '000002',
            substr($codes[1], -6)
        );

        $this->assertSame(
            '000003',
            substr($codes[2], -6)
        );

        $this->assertCount(3, array_unique($codes));

        $sequence = PaymentTrackingSequence::query()
            ->where('jalali_date', $today)
            ->firstOrFail();

        $this->assertSame(3, $sequence->last_sequence);
    }
}
