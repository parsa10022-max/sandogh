<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {

            $table->id();

            // حساب مقصد
            $table->foreignId('account_id')
                ->constrained()
                ->cascadeOnDelete();

            // اگر عضو صندوق باشد
            $table->foreignId('customer_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // مبلغ
            $table->unsignedBigInteger('amount');

            // روش پرداخت
            $table->unsignedTinyInteger('payment_method');

            // وضعیت
            // 0 = ناموفق
            // 1 = موفق
            // 2 = در انتظار پرداخت
            $table->unsignedTinyInteger('status')
                ->default(2);

            // شماره پیگیری بانک
            $table->string('tracking_code')
                ->nullable();

            // شماره مرجع بانک
            $table->string('reference_number')
                ->nullable();

            // تاریخ پرداخت
            $table->dateTime('paid_at')
                ->nullable();

            // توضیحات
            $table->text('description')
                ->nullable();

            // ثبت کننده
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index('status');
            $table->index('payment_method');
            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
