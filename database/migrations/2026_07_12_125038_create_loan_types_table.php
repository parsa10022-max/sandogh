<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * اجرای Migration
     */
    public function up(): void
    {
        Schema::create('loan_types', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100)
                ->comment('نام نوع وام');

            $table->char('prefix', 4)
                ->unique()
                ->comment('پیش‌شماره شماره وام');

            $table->text('description')
                ->nullable()
                ->comment('توضیحات');

            $table->boolean('is_active')
                ->default(true)
                ->comment('وضعیت فعال بودن');

            $table->timestamps();
        });
    }

    /**
     * حذف Migration
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_types');
    }
};
