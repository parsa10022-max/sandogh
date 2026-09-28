<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_requests', function (Blueprint $table) {

            $table->foreignId('loan_id')
                ->nullable()
                ->after('id')
                ->constrained('loans')
                ->nullOnDelete();

            $table->unique('loan_id');
        });
    }

    public function down(): void
    {
        Schema::table('loan_requests', function (Blueprint $table) {

            $table->dropUnique(['loan_id']);

            $table->dropForeign(['loan_id']);

            $table->dropColumn('loan_id');
        });
    }
};
