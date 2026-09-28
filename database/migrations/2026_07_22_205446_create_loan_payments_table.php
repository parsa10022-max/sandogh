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
        Schema::create('loan_payments', function (Blueprint $table) {

            $table->id();

            // وام
            $table->foreignId('loan_id')
                ->constrained()
                ->restrictOnDelete();

            // قسط
            $table->foreignId('installment_id')
                ->constrained()
                ->restrictOnDelete();

            // کاربری که پرداخت را انجام داده است
            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();

            // مبلغ پرداخت (ریال)
            $table->unsignedBigInteger('amount');

            // کد رهگیری داخلی صندوق
            $table->string('tracking_code', 30)->unique();

            // درگاه پرداخت
            $table->string('gateway', 30);

            // شناسه تراکنش بانک
            $table->string('bank_transaction_id')->nullable();

            // شماره مرجع بانک
            $table->string('bank_reference_number')->nullable();

            // زمان پرداخت
            $table->timestamp('paid_at');

            $table->timestamps();

            // Indexes
            $table->index('loan_id');
            $table->index('installment_id');
            $table->index('user_id');
            $table->index('paid_at');
            $table->index('bank_reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_payments');
    }
};
