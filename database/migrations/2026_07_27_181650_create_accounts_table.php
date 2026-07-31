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
        Schema::create('accounts', function (Blueprint $table) {

            $table->id();

            // صاحب حساب
            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            // شماره حساب
            // نمونه: 6111-2535
            $table->string('account_number', 20)->unique();

            // نوع حساب
            // 1 = پس انداز
            // 2 = جاری
            $table->unsignedTinyInteger('account_type');

            // موجودی (ریال)
            $table->unsignedBigInteger('balance')
                ->default(0);

            // وضعیت حساب
            // 1 = فعال
            // 2 = مسدود
            // 0 = بسته
            $table->unsignedTinyInteger('status')
                ->default(1);

            // تاریخ افتتاح
            $table->date('opened_date');

            // تاریخ بستن
            $table->date('closed_date')
                ->nullable();

            $table->timestamps();


            $table->index('account_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
