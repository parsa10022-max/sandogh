<?php

namespace Tests\Feature\Account;

use App\Services\Account\AccountTransactionNoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccountTransactionNoServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generates_transaction_number_with_correct_format(): void
    {
        $service = app(AccountTransactionNoService::class);

        $transactionNo = $service->generate();

        $this->assertMatchesRegularExpression(
            '/^AT\d{8}\d{6}$/',
            $transactionNo
        );

        $this->assertDatabaseCount('account_transaction_sequences', 1);
    }

    public function test_first_transaction_number_of_the_day_ends_with_000001(): void
    {
        $service = app(AccountTransactionNoService::class);

        $transactionNo = $service->generate();

        $this->assertStringEndsWith('000001', $transactionNo);
    }

    public function test_sequence_increases_for_each_generated_number(): void
    {
        $service = app(AccountTransactionNoService::class);

        $first = $service->generate();
        $second = $service->generate();
        $third = $service->generate();

        $this->assertNotSame($first, $second);
        $this->assertNotSame($second, $third);
        $this->assertNotSame($first, $third);

        $this->assertStringEndsWith('000001', $first);
        $this->assertStringEndsWith('000002', $second);
        $this->assertStringEndsWith('000003', $third);
    }

    public function test_sequence_record_contains_correct_final_value(): void
    {
        $service = app(AccountTransactionNoService::class);

        $service->generate();
        $service->generate();
        $service->generate();

        $record = DB::table('account_transaction_sequences')->first();

        $this->assertNotNull($record);
        $this->assertSame(3, (int) $record->sequence);
        $this->assertSame(8, strlen($record->jalali_date));
    }
}
