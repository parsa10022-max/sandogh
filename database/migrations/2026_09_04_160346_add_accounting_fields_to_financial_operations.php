<?php

use App\Enums\AccountingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Savings Transfers
        |--------------------------------------------------------------------------
        */

        Schema::table('savings_transfers', function (Blueprint $table) {

            $table->unsignedTinyInteger('accounting_status')
                ->default(AccountingStatus::PENDING->value)
                ->after('status');

            $table->foreignId('accounting_confirmed_by')
                ->nullable()
                ->after('accounting_status')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('accounting_confirmed_at')
                ->nullable()
                ->after('accounting_confirmed_by');

            $table->index('accounting_status');
        });

        /*
        |--------------------------------------------------------------------------
        | Withdrawals
        |--------------------------------------------------------------------------
        */

        Schema::table('withdrawals', function (Blueprint $table) {

            $table->unsignedTinyInteger('accounting_status')
                ->default(AccountingStatus::PENDING->value)
                ->after('status');

            $table->foreignId('accounting_confirmed_by')
                ->nullable()
                ->after('accounting_status')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('accounting_confirmed_at')
                ->nullable()
                ->after('accounting_confirmed_by');

            $table->index('accounting_status');
        });

        /*
        |--------------------------------------------------------------------------
        | Loan Payments
        |--------------------------------------------------------------------------
        */

        Schema::table('loan_payments', function (Blueprint $table) {

            $table->unsignedTinyInteger('accounting_status')
                ->default(AccountingStatus::PENDING->value)
                ->after('paid_at');

            $table->foreignId('accounting_confirmed_by')
                ->nullable()
                ->after('accounting_status')
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('accounting_confirmed_at')
                ->nullable()
                ->after('accounting_confirmed_by');

            $table->index('accounting_status');
        });
    }

    public function down(): void
    {
        Schema::table('savings_transfers', function (Blueprint $table) {
            $table->dropForeign(['accounting_confirmed_by']);
            $table->dropIndex(['savings_transfers_accounting_status_index']);
            $table->dropColumn([
                'accounting_status',
                'accounting_confirmed_by',
                'accounting_confirmed_at',
            ]);
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropForeign(['accounting_confirmed_by']);
            $table->dropIndex(['withdrawals_accounting_status_index']);
            $table->dropColumn([
                'accounting_status',
                'accounting_confirmed_by',
                'accounting_confirmed_at',
            ]);
        });

        Schema::table('loan_payments', function (Blueprint $table) {
            $table->dropForeign(['accounting_confirmed_by']);
            $table->dropIndex(['loan_payments_accounting_status_index']);
            $table->dropColumn([
                'accounting_status',
                'accounting_confirmed_by',
                'accounting_confirmed_at',
            ]);
        });
    }
};
