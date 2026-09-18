<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_statistics', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('paid_loans_count')->default(0);
            $table->unsignedBigInteger('paid_loans_amount')->default(0);
            $table->unsignedInteger('donations_count')->default(0);
            $table->date('statistics_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_statistics');
    }
};
