<?php

use App\Enums\LoanStatus;
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
        Schema::create('loans', function (Blueprint $table) {

            $table->id();

            // مشتری
            $table->foreignId('customer_id')
                ->constrained()
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            // نوع وام
            $table->foreignId('loan_type_id')
                ->constrained()
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            // شماره وام (بدون پیش شماره)
            $table->unsignedInteger('loan_number');

            // مبلغ اصل وام
            $table->unsignedBigInteger('loan_amount');

            // مبلغ هر قسط
            $table->unsignedBigInteger('installment_amount');

            // تعداد اقساط
            $table->unsignedSmallInteger('installment_count');

            // فاصله بین اقساط (بر حسب ماه)
            // 1 = ماهانه
            // 3 = سه ماهه
            // 6 = شش ماهه
            $table->unsignedTinyInteger('installment_interval')
                ->default(1);

            // تاریخ پرداخت وام
            $table->date('start_date');

            // تاریخ اولین قسط
            $table->date('first_due_date');

            // تاریخ آخرین قسط
            $table->date('last_due_date');

            // وضعیت وام
            $table->tinyInteger('status')
                ->default(LoanStatus::ACTIVE->value)
                ->index();

            // توضیحات
            $table->text('description')
                ->nullable();

            // ثبت کننده
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            // آخرین ویرایش کننده
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();

            $table->softDeletes();

            // شماره وام در هر نوع وام یکتا باشد
            $table->unique([
                'loan_type_id',
                'loan_number',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
