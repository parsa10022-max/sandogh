<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_guarantors', function (Blueprint $table) {
            $table->unique(
                ['loan_id', 'guarantor_order'],
                'loan_guarantors_loan_order_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('loan_guarantors', function (Blueprint $table) {
            $table->dropUnique('loan_guarantors_loan_order_unique');
        });
    }
};
