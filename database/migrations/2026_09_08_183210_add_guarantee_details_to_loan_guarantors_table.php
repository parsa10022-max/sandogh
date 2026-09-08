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
        Schema::table('loan_guarantors', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | مبلغ مدرک ضمانت
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('guarantee_amount')
                ->nullable()
                ->after('guarantee_type');

            /*
            |--------------------------------------------------------------------------
            | شماره / سریال چک یا سفته
            |--------------------------------------------------------------------------
            */

            $table->string('guarantee_number', 100)
                ->nullable()
                ->after('guarantee_amount');

            /*
            |--------------------------------------------------------------------------
            | شماره حساب چک
            |--------------------------------------------------------------------------
            */

            $table->string('guarantee_account_number', 100)
                ->nullable()
                ->after('guarantee_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_guarantors', function (Blueprint $table) {

            $table->dropColumn([
                'guarantee_amount',
                'guarantee_number',
                'guarantee_account_number',
            ]);
        });
    }
};
