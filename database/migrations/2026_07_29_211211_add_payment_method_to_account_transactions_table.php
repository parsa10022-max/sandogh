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
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->unsignedTinyInteger('payment_method')
                ->nullable()
                ->after('transaction_source');
        });
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        Schema::table('account_transactions', function (Blueprint $table) {

            $table->dropColumn('payment_method');

        });
    }
};
