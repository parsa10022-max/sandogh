<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('donation_payments', function (Blueprint $table) {

            $table->string('gateway')
                ->nullable()
                ->after('status');

            $table->string('bank_transaction_id')
                ->nullable()
                ->after('gateway');

            $table->string('bank_reference_number')
                ->nullable()
                ->after('bank_transaction_id');

            $table->timestamp('paid_at')
                ->nullable()
                ->after('bank_reference_number');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donation_payments', function (Blueprint $table) {

            $table->dropColumn([
                'gateway',
                'bank_transaction_id',
                'bank_reference_number',
                'paid_at',
            ]);

        });
    }
};
