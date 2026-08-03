<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_transfers', function (Blueprint $table) {

            $table->id();


            // پرداخت کننده
            $table->foreignId('sender_user_id')
                ->constrained('users')
                ->restrictOnDelete();


            // عضو دریافت کننده
            $table->foreignId('receiver_customer_id')
                ->constrained('customers')
                ->restrictOnDelete();


            // حساب پس انداز مقصد
            $table->foreignId('account_id')
                ->constrained()
                ->restrictOnDelete();


            // مبلغ (ریال)
            $table->unsignedBigInteger('amount');


            // کد رهگیری داخلی صندوق
            $table->string('tracking_code', 30)
                ->unique();


            // درگاه
            $table->string('gateway', 30);


            // اطلاعات بانک
            $table->string('bank_transaction_id')
                ->nullable();


            $table->string('bank_reference_number')
                ->nullable();


            // وضعیت
            // pending = در انتظار پرداخت
            // paid = پرداخت شده
            // failed = ناموفق
            $table->string('status', 20)
                ->default('pending');


            // زمان پرداخت موفق
            $table->timestamp('paid_at')
                ->nullable();


            $table->timestamps();


            $table->index('sender_user_id');
            $table->index('receiver_customer_id');
            $table->index('tracking_code');

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('savings_transfers');
    }
};
