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
        Schema::create('payment_intents', function (Blueprint $table) {
            $table->id();

            // نوع پرداخت:
            // installment
            // installment_customer
            // installment_other
            // savings_transfer
            // donation_customer
            // donation_public
            $table->string('payment_type', 40);

            // شناسه رکورد مرتبط با نوع پرداخت
            $table->unsignedBigInteger('reference_id');

            // مبلغ پرداخت به ریال
            $table->unsignedBigInteger('amount');

            // کد پیگیری داخلی و قابل نمایش
            $table->string('tracking_code', 30);

            // درگاه پرداخت
            $table->string('gateway', 30);

            // توکن/شناسه فنی پرداخت درگاه
            $table->string('gateway_token', 255)->nullable();

            // وضعیت Intent
            // pending / redirected / verifying / paid / failed / expired
            $table->string('status', 20)->default('pending');

            // اطلاعات برگشتی درگاه
            $table->string('gateway_transaction_id', 255)->nullable();
            $table->string('gateway_reference_number', 255)->nullable();

            // زمان انقضای Intent
            $table->timestamp('expires_at')->nullable();

            // زمان پرداخت موفق
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            // جستجوی رکورد مرتبط
            $table->index(
                ['payment_type', 'reference_id'],
                'payment_intents_type_reference_index'
            );

            // جلوگیری از تکرار کد پیگیری
            $table->unique(
                'tracking_code',
                'payment_intents_tracking_code_unique'
            );

            // Token باید یکتا باشد
            $table->unique(
                'gateway_token',
                'payment_intents_gateway_token_unique'
            );

            // برای بررسی وضعیت‌های پرداخت
            $table->index(
                'status',
                'payment_intents_status_index'
            );

            // برای پاکسازی/بررسی Intentهای منقضی‌شده
            $table->index(
                'expires_at',
                'payment_intents_expires_at_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_intents');
    }
};
