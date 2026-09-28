<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_transaction_sequences', function (Blueprint $table) {
            $table->id();

            $table->string('jalali_date', 8)->unique();

            $table->unsignedInteger('sequence')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transaction_sequences');
    }
};
