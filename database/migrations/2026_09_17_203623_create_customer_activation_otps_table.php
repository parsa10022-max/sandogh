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
        Schema::create('customer_activation_otps', function (Blueprint $table) {

            $table->id();

            // عضو صندوق
            $table->foreignId('customer_id')
                ->constrained()
                ->cascadeOnDelete();

            // شماره موبایلی که OTP برای آن درخواست شده
            $table->string('mobile', 11);

            // کد OTP
            $table->string('code', 255);

            // وضعیت OTP
            $table->string('status', 20);

            // تعداد تلاش
            $table->unsignedTinyInteger('attempts')
                ->default(0);

            // زمان انقضا
            $table->timestamp('expires_at');

            // زمان تأیید
            $table->timestamp('verified_at')
                ->nullable();

            // اطلاعات امنیتی
            $table->string('ip_address', 45)
                ->nullable();

            $table->string('user_agent', 500)
                ->nullable();

            // زمان لغو
            $table->timestamp('cancelled_at')
                ->nullable();

            $table->timestamps();

            // Indexes
            $table->index('customer_id');
            $table->index('mobile');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_activation_otps');
    }
};
