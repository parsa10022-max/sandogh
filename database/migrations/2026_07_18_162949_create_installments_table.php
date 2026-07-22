<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installments', function (Blueprint $table) {

            $table->id();

            // وام
            $table->foreignId('loan_id')
                ->constrained()
                ->cascadeOnDelete();


            // شماره قسط
            $table->unsignedSmallInteger(
                'installment_number'
            );


            // مبلغ قسط (ریال)
            $table->unsignedBigInteger(
                'amount'
            );


            // تاریخ سررسید (میلادی)
            $table->date(
                'due_date'
            );


            // وضعیت قسط
            $table->unsignedTinyInteger(
                'status'
            )->default(0);


            // زمان پرداخت
            $table->dateTime(
                'paid_at'
            )->nullable();


            // توضیحات
            $table->text(
                'description'
            )->nullable();


            // ثبت کننده
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


            // ویرایش کننده
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();


            $table->timestamps();



            // جلوگیری از شماره قسط تکراری برای یک وام
            $table->unique([
                'loan_id',
                'installment_number'
            ]);

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};
