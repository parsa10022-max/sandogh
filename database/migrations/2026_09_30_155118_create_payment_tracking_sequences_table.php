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
        Schema::create('payment_tracking_sequences', function (Blueprint $table) {
            $table->id();

            // تاریخ جلالی، مانند 14050930
            $table->string('jalali_date', 8)->unique();

            // آخرین شماره استفاده‌شده در آن روز
            $table->unsignedInteger('last_sequence')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_tracking_sequences');
    }
};
