<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_requests', function (Blueprint $table) {

            $table->foreignId('loan_type_id')
                ->nullable()
                ->after('approved_amount')
                ->constrained('loan_types')
                ->nullOnDelete();

            $table->unsignedSmallInteger('approved_installment_count')
                ->nullable()
                ->after('loan_type_id');

            $table->unsignedTinyInteger('approved_installment_interval')
                ->nullable()
                ->after('approved_installment_count');

        });
    }

    public function down(): void
    {
        Schema::table('loan_requests', function (Blueprint $table) {

            $table->dropConstrainedForeignId('loan_type_id');

            $table->dropColumn([
                'approved_installment_count',
                'approved_installment_interval',
            ]);

        });
    }
};
