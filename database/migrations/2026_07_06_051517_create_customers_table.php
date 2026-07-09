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
        Schema::create('customers', function (Blueprint $table) {

            $table->id();

            // کد مشتری (از سیستم قبلی)
            $table->unsignedInteger('customer_code')->unique();

            // اطلاعات هویتی
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('father_name', 50)->nullable();
            $table->string('national_code', 10)->unique();

            // اطلاعات تماس
            $table->string('mobile', 11)->unique();
            $table->string('mobile_second', 11)->nullable();

            // وضعیت
            $table->string('status', 20)->default('active')->index();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['first_name', 'last_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
