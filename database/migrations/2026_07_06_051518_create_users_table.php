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
        Schema::create('users', function (Blueprint $table) {

            $table->id();

            // ارتباط با مشتری (در صورت مشتری بودن کاربر)
            $table->foreignId('customer_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete()
                ->unique();

            // اطلاعات ورود
            $table->string('username', 100)->unique();
            $table->string('mobile', 11)->unique();
            $table->string('email')->nullable()->unique();

            // نقش کاربر
            $table->string('role',20)->index();

            // احراز هویت
            $table->string('password');

            // وضعیت
            $table->string('status',20)
                ->default('active')
                ->index();
            // تأییدها
            $table->timestamp('mobile_verified_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();

            // آخرین ورود
            $table->timestamp('last_login_at')->nullable();

            $table->rememberToken();

            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
