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
        Schema::create('donation_payments', function (Blueprint $table) {

            $table->id();

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();


            $table->foreignId('account_id')
                ->constrained()
                ->cascadeOnDelete();


            $table->unsignedBigInteger('amount');


            $table->string('tracking_code')
                ->nullable();


            $table->tinyInteger('status')
                ->default(0);
            // 0 انتظار پرداخت
            // 1 پرداخت موفق
            // 2 لغو شده


            $table->timestamps();


        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donation_payments');
    }
};
