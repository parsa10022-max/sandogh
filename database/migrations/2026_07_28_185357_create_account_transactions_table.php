<?php

use App\Enums\PaymentMethod;
use App\Enums\TransactionSource;
use App\Enums\TransactionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_transactions', function (Blueprint $table) {

            $table->id();

            // حساب مربوط به تراکنش
            $table->foreignId('account_id')
                ->constrained()
                ->restrictOnDelete();

            // شماره رهگیری یکتا
            $table->string('transaction_no', 50)
                ->unique();

            // نوع تراکنش
            $table->unsignedTinyInteger('transaction_type');

            // منبع تراکنش
            $table->unsignedTinyInteger('transaction_source');

            // مبلغ به ریال
            $table->unsignedBigInteger('amount');

            // موجودی قبل از عملیات
            $table->unsignedBigInteger('balance_before');

            // موجودی بعد از عملیات
            $table->unsignedBigInteger('balance_after');

            // تاریخ عملیات
            $table->date('transaction_date');

            // کاربر ثبت کننده
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // روش پرداخت
            $table->unsignedTinyInteger('payment_method')
                ->nullable();

            // توضیحات
            $table->text('description')
                ->nullable();

            $table->timestamps();

            $table->index('transaction_type');
            $table->index('transaction_source');
            $table->index('transaction_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }
};
